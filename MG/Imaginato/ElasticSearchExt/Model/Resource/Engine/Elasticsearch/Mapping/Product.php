<?php
class Imaginato_ElasticSearchExt_Model_Resource_Engine_Elasticsearch_Mapping_Product
    extends Smile_ElasticSearch_Model_Resource_Engine_Elasticsearch_Mapping_Product{

    protected function _getAttributeMapping($attribute)
    {
        $mapping = array();

        if ($this->_canIndexAttribute($attribute)) {
            $attributeCode = $attribute->getAttributeCode();
            $type = $this->_getAttributeType($attribute);

            $isSearchable = (bool) $attribute->getIsSearchable() && $attribute->getSearchWeight() > 0;
            $isFilterable = $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch();
            $isFacet = (bool) ($isFilterable || $attribute->getIsUsedForPromoRules());
            $isFuzzy = (bool) $attribute->getIsFuzzinessEnabled() && $isSearchable;
            $usedForSortBy = (bool) $attribute->getUsedForSortBy();
            $isAutocomplete = (bool) ($attribute->getIsUsedInAutocomplete() || $attribute->getIsDisplayedInAutocomplete());

            if ($type === 'string' && !$attribute->getBackendModel() && $attribute->getFrontendInput() != 'media_image') {
                foreach ($this->_stores as $store) {
                    $languageCode = $this->_helper->getLanguageCodeByStore($store);
                    $fieldName = $attributeCode . '_' . $languageCode;
                    $mapping[$fieldName] = array('type' => $type, 'analyzer' => 'analyzer_' . $languageCode, 'store' => false);

                    $multiTypeField = $attribute->getBackendType() == 'varchar' || $attribute->getBackendType() == 'text';
                    $multiTypeField = $multiTypeField && !($attribute->usesSource());

                    if ($multiTypeField) {
                        $fieldMapping = $this->_getStringMapping(
                            $fieldName, $languageCode, $type, $usedForSortBy, $isFuzzy, $isFacet, $isAutocomplete, $isSearchable
                        );
                        $mapping = array_merge($mapping, $fieldMapping);
                    }
                }
            } else if ($type === 'date') {
                $mapping[$attributeCode] = array(
                    'store' => false,
                    'type' => $type,
                    'format' => implode('||', array(Varien_Date::DATETIME_INTERNAL_FORMAT, Varien_Date::DATE_INTERNAL_FORMAT))
                );
            } else {
                $mapping[$attributeCode] = array(
                    'type' => $type, 'store' => false, 'fielddata' => array('format' => $type == 'string' ? 'disabled' :'doc_values')
                );
                if($attributeCode == 'sku'){
                    foreach ($this->_stores as $store) {
                        $languageCode = $this->_helper->getLanguageCodeByStore($store);
                        $fieldName = $attributeCode . '_' . $languageCode;
                        $isFuzzy = false;
                        $isFacet = false;
                        $isAutocomplete = false;
                        $fieldMapping = $this->_getStringMapping(
                            $fieldName, $languageCode, $type, $usedForSortBy, $isFuzzy, $isFacet, $isAutocomplete, $isSearchable
                        );
                        $mapping = array_merge($mapping, $fieldMapping);
                    }
                }
            }

            if ($attribute->usesSource()) {
                foreach ($this->_stores as $store) {
                    $languageCode = $this->_helper->getLanguageCodeByStore($store);
                    $fieldName = 'options' . '_' .  $attributeCode . '_' . $languageCode;
                    $fieldMapping = $this->_getStringMapping(
                        $fieldName, $languageCode, 'string', $usedForSortBy, $isFuzzy, $isFacet, $isAutocomplete, $isSearchable
                    );
                    $mapping = array_merge($mapping, $fieldMapping);
                }
            }
        }

        return $mapping;
    }

    protected function _rebuildStoreIndex($storeId, $entityIds = null)
    {
        if (is_array($entityIds)) {
            $entityIds = array_map('intval', $entityIds);
        }

        $store = Mage::app()->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        $languageCode = $this->_helper->getLanguageCodeByStore($store);

        $dynamicFields = array();
        $attributesById = $this->_getAttributesById();

        foreach ($attributesById as $attribute) {
            if ($this->_canIndexAttribute($attribute) && $attribute->getBackendType() != 'static') {
                $dynamicFields[$attribute->getBackendTable()][] = (int) $attribute->getAttributeId();
            }
        }

        $websiteId = Mage::app()->getStore($storeId)->getWebsite()->getId();
        $lastObjectId = 0;

        while (true) {

            $entities = $this->_getSearchableEntities($storeId, $entityIds, $lastObjectId);

            if (!$entities) {
                break;
            }

            $ids = array_keys($entities);
            $lastObjectId = end($ids);

            $entities = $this->_addAdvancedIndex($entities, $storeId);

            if (!empty($entities)) {
                $this->_getRequestPathForProduct($entities, $storeId,$languageCode);
                $ids = array_keys($entities);

                $entityRelations = $this->_getChildrenIds($ids, $websiteId);
                if (!empty($entityRelations)) {
                    $allChildrenIds = call_user_func_array('array_merge', $entityRelations);
                    $enableChildrenIds = array();
                    foreach(array_chunk($allChildrenIds,$this->_getBatchIndexingSize()) as $childrenIds){
                        $childEntities = $this->_getSearchableEntities($storeId, $childrenIds);
                        $enableChildrenIds = $enableChildrenIds + array_keys($childEntities);
                        $index = Mage::getResourceSingleton('smile_elasticsearch/engine_index');
                        $childEntities = $index->addAdvancedIndexForChild($childEntities, $storeId);
                        $entities = $entities + $childEntities;
                    }
                    $ids = array_merge($ids, $enableChildrenIds);
                    $this->_getSupperAttributeIds($entities);
                }

                $entityIndexes    = array();
                $entityAttributes = $this->_getAttributes($storeId, $ids, $dynamicFields);

                foreach ($entities as &$entityData) {

                    if (!isset($entityAttributes[$entityData['entity_id']])) {
                        continue;
                    }
                    $entityData['sku_' . $languageCode] = $entityData['sku'];
                    if(isset($entityData['thumbnail']) && $entityData['thumbnail'] == 'no_selection'){
                        unset($entityData['thumbnail']);
                    }
                    if(isset($entityData['small_image']) && $entityData['small_image'] == 'no_selection'){
                        unset($entityData['small_image']);
                    }
                    if(isset($entityData['alt_small_image']) && $entityData['alt_small_image'] == 'no_selection'){
                        unset($entityData['alt_small_image']);
                    }
                    $entityTypeId = isset($entityData['type_id']) ? $entityData['type_id'] : null;
                    if(isset($entityRelations[$entityData['entity_id']])){
                        $entityData['children_ids'] = array_intersect($entityRelations[$entityData['entity_id']], $ids);
                        $entityData['children_ids'] = array_values($entityData['children_ids']);
                        //$entityData['children_ids'] = $entityRelations[$entityData['entity_id']];
                    }
                    $this->_addChildrenData($entityData['entity_id'], $entityAttributes, $entityRelations, $storeId, $entityTypeId);

                    foreach ($entityAttributes[$entityData['entity_id']] as $attributeId => $value) {
                        $attribute = $attributesById[$attributeId];
                        $entityData += $this->_getAttributeIndexValues($attribute, $value, $storeId, $languageCode);
                    }

                    if(isset($entityData['special_price']) && $entityData['special_price']) {
                        $entityData['discount_sorting'] = $entityData['price'] - $entityData['special_price'];
                    }else{
                        $entityData['discount_sorting'] = 0;
                    }

                    $entityData['store_id'] = $storeId;
                    $entityData[Smile_ElasticSearch_Model_Resource_Engine_Elasticsearch::UNIQUE_KEY] = $entityData['entity_id'] . '|' . $storeId;
                    $entityIndexes[$entityData['entity_id']] = $entityData;
                }

                $this->_saveIndexes($storeId, $entityIndexes);
            }
        }
        return $this;
    }

    protected function _getMappingProperties()
    {
        $mapping = parent::_getMappingProperties(true);
        $mapping['properties']['categories'] = array('type' => 'long', 'fielddata' => array('format' => 'doc_values'));
        $mapping['properties']['children_ids'] = array('type' => 'long', 'fielddata' => array('format' => 'doc_values'));
        $mapping['properties']['show_in_categories'] = array('type' => 'long', 'fielddata' => array('format' => 'doc_values'));
        $mapping['properties']['supper_attribute_ids'] = array('type' => 'long', 'fielddata' => array('format' => 'doc_values'));
        $mapping['properties']['in_stock']   = array('type' => 'boolean', 'fielddata' => array('format' => 'doc_values'));

        foreach ($this->_stores as $store) {
            $languageCode = Mage::helper('smile_elasticsearch')->getLanguageCodeByStore($store);
            $fieldMapping = $this->_getStringMapping('category_name_' . $languageCode, $languageCode, 'string', true, true, true);
            $mapping['properties'] = array_merge($mapping['properties'], $fieldMapping);
            $mapping['properties']['request_path_' . $languageCode] = array('type' => 'string', 'fielddata' => array('format' => 'disabled'));
        }

        $mapping['properties']['category_position'] = array(
            'type' => 'nested',
            'properties' => array(
                'category_id' => array('type' => 'long', 'fielddata' => array('format' => 'doc_values')),
                'position'    => array('type' => 'long', 'fielddata' => array('format' => 'doc_values'))
            )
        );

        // Append dynamic mapping for product prices and discount fields
        $fieldTemplate = array(
            'match' => 'price_*', 'mapping' => array('type' => 'double', 'fielddata' => array('format' => 'doc_values'))
        );
        $mapping['dynamic_templates'][] = array('prices' => $fieldTemplate);

        $fieldTemplate = array(
            'match' => 'has_discount_*', 'mapping' => array('type' => 'boolean', 'fielddata' => array('format' => 'doc_values'))
        );
        $mapping['dynamic_templates'][] = array('has_discount' => $fieldTemplate);

        $mappingObject = new Varien_Object($mapping);
        Mage::dispatchEvent('search_engine_product_mapping_properties', array('mapping' => $mappingObject));

        return $mappingObject->getData();
    }

    protected function _addChildrenData($parentId, &$entityAttributes, $entityRelations, $storeId, $entityTypeId = null)
    {
        $forbiddenAttributesCode = array('visibility', 'status', 'price', 'tax_class_id');
        $attributesById = $this->_getAttributesById();
        $entityData = $entityAttributes[$parentId];
        if (isset($entityRelations[$parentId])) {
            foreach ($entityAttributes[$parentId] as $attributeId => $value) {
                $attribute = $attributesById[$attributeId];
                $attributeCode = $attribute->getAttributeCode();
                $isAttributeIndexed = !in_array($attributeCode, $forbiddenAttributesCode);

                if ($entityTypeId == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                    $frontendInput = $isAttributeIndexed ? $attribute->getFrontendInput() : false;
                    $isAttributeIndexed = $isAttributeIndexed && in_array($frontendInput, array('select', 'multiselect'));
                    $isAttributeIndexed = $isAttributeIndexed && (bool) $attribute->getIsConfigurable();
                } else {
                    $isAttributeIndexed = $isAttributeIndexed && $attribute->getBackendType() != 'static';
                }
                if($isAttributeIndexed){
                    unset($entityAttributes[$parentId][$attributeId]);
                }
            }
            foreach ($entityRelations[$parentId] as $childrenId) {
                if (isset($entityAttributes[$childrenId])) {

                    foreach ($entityAttributes[$childrenId] as $attributeId => $value) {
                        $attribute = $attributesById[$attributeId];
                        $attributeCode = $attribute->getAttributeCode();
                        $isAttributeIndexed = !in_array($attributeCode, $forbiddenAttributesCode);

                        if ($entityTypeId == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                            $frontendInput = $isAttributeIndexed ? $attribute->getFrontendInput() : false;
                            $isAttributeIndexed = $isAttributeIndexed && in_array($frontendInput, array('select', 'multiselect'));
                            $isAttributeIndexed = $isAttributeIndexed && (bool) $attribute->getIsConfigurable();
                        } else {
                            $isAttributeIndexed = $isAttributeIndexed && $attribute->getBackendType() != 'static';
                        }

                        if ($isAttributeIndexed && $value != null) {
                            if (!is_array($value) && ($attribute->getFrontendInput() == "multiselect")) {
                                $value = explode(',', $value);
                            }
                            if (!isset($entityAttributes[$parentId][$attributeId])) {
                                $entityAttributes[$parentId][$attributeId] =  $value;
                            } else {
                                if (!is_array($entityAttributes[$parentId][$attributeId])) {
                                    $entityAttributes[$parentId][$attributeId] = explode(
                                        ',', $entityAttributes[$parentId][$attributeId]
                                    );
                                }
                                if (is_array($value)) {
                                    $entityAttributes[$parentId][$attributeId] = array_merge(
                                        $value, $entityAttributes[$parentId][$attributeId]
                                    );
                                } else {
                                    $entityAttributes[$parentId][$attributeId][] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    protected function _getAttributeValue($attribute, $value, $storeId)
    {
        if ($attribute->usesSource()) {
            if (!is_array($value)) {
                $value = explode(',', $value);
            }
            $value = array_filter($value);
            $value = array_values($value);
            if ($attribute->getBackendType() == 'int') {
                $value = array_map('intval', $value);
            }
            if (count($value) == 1) {
                $value = current($value);
            }
        } else if ($attribute->getBackendType() == 'decimal') {
            $value = floatval($value);
        } else if ($attribute->getBackendType() == 'int') {
            $value = intval($value);
        }

        return $value;
    }

    protected function _getOptionsText($attribute, $value, $storeId)
    {
        $attributeId = $attribute->getAttributeId();
        if (!isset($this->_indexedOptionText[$attributeId]) || !isset($this->_indexedOptionText[$attributeId][$storeId])) {
            $this->_getAllOptionsText($attribute, $storeId);
        }

        if (is_array($value)) {
            //$value = array_values(array_intersect($this->_indexedOptionText[$attributeId][$storeId], $value));
            foreach($value as &$v){
                if(isset($this->_indexedOptionText[$attributeId][$storeId][$v])){
                    $v = $this->_indexedOptionText[$attributeId][$storeId][$v];
                }
            }
            if (empty($value)) {
                $value = false;
            } else if (count($value) == 1) {
                $value = current($value);
            }
        } else {
            $value = (string) trim($value, ',');
            if (isset($this->_indexedOptionText[$attributeId][$storeId][$value])) {
                $value = $this->_indexedOptionText[$attributeId][$storeId][$value];
            } else {
                $value == false;
            }
        }

        return $value;
    }

    protected function _getAttributesById()
    {
        if ($this->_attributesById === null) {
            $entityType = Mage::getModel('eav/entity_type')->loadByCode($this->_entityType);

            $attributes = Mage::getResourceModel($this->_attributeCollectionModel)
                ->setEntityTypeFilter($entityType->getEntityTypeId());

            if (method_exists($attributes, 'addToIndexFilter')) {
                $conditions = array(
                    'additional_table.is_searchable = 1',
                    'additional_table.is_visible_in_advanced_search = 1',
                    'additional_table.is_filterable > 0',
                    'additional_table.is_filterable_in_search = 1',
                    'additional_table.used_for_sort_by = 1',
                    'additional_table.is_used_for_promo_rules',
                    $this->getConnection()->quoteInto('main_table.attribute_code = ?', 'status'),
                    $this->getConnection()->quoteInto('main_table.attribute_code = ?', 'visibility'),
                    $this->getConnection()->quoteInto('main_table.attribute_code = ?', 'thumbnail'),
                    $this->getConnection()->quoteInto('main_table.attribute_code = ?', 'small_image'),
                    $this->getConnection()->quoteInto('main_table.attribute_code = ?', 'alt_small_image'),
                );

                $attributes->getSelect()->where(sprintf('(%s)', implode(' OR ', $conditions)));
            }

            $this->_attributesById = array();

            foreach ($attributes as $attribute) {
                if ($this->_canIndexAttribute($attribute)) {
                    $this->_attributesById[$attribute->getAttributeId()] = $attribute;
                }
            }
        }

        return $this->_attributesById;
    }

    /**
     * Retrive a bucket of indexable entities.
     *
     * @param int         $storeId Store id
     * @param string|null $ids     Ids filter
     * @param int         $lastId  First id
     *
     * @return array
     */
    protected function _getSearchableEntities($storeId, $ids = null, $lastId = 0)
    {
        $limit = $this->_getBatchIndexingSize();

        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $stock_id = Mage::helper('warehouse')->getStockIdByStoreId($storeId);

        $adapter   = $this->getConnection();
        $status_entity      = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status');

        $select = $adapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog/product'))
            )
            ->join(
                array('website' => $this->getTable('catalog/product_website')),
                $adapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id=?',
                    (int) $websiteId
                ),
                array()
            )
            ->joinLeft(
                array('stock_status' => $this->getTable('cataloginventory/stock_status')),
                $adapter->quoteInto(
                    'stock_status.product_id=e.entity_id AND stock_status.website_id=? AND stock_status.stock_id='.$stock_id,
                    (int) $websiteId
                ),
                array('in_stock' => new Zend_Db_Expr("COALESCE(stock_status.stock_status, 0)"),
                    'qty' => 'qty'
                )
            )
            ->joinLeft(
                array('p_status' => $status_entity->getBackendTable()),
                $adapter->quoteInto(
                    'p_status.entity_id=e.entity_id AND p_status.store_id in(?) AND p_status.attribute_id='.$status_entity->getAttributeId(),
                    array(0)
                ),
                array()
            )
            ->joinLeft(
                array('p_status_'.$storeId => $status_entity->getBackendTable()),
                $adapter->quoteInto(
                    'p_status_'.$storeId.'.entity_id=e.entity_id AND p_status_'.$storeId.'.store_id in(?) AND p_status_'.$storeId.'.attribute_id='.$status_entity->getAttributeId(),
                    array((int) $storeId)
                ),
                array()
            )
            ->where('IF(p_status_'.$storeId.'.value,p_status_'.$storeId.'.value, p_status.value)=?',Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        if (!is_null($ids)) {
            $select->where('e.entity_id IN(?)', $ids);
        }

        $select->where('e.entity_id>?', (int) $lastId)
            ->limit($limit)
            ->order(array('e.entity_id','p_status.store_id'));

        /**
         * Add additional external limitation
         */
        $eventNames = array(
            sprintf('prepare_catalog_%s_index_select', $this->_type),
            sprintf('prepare_catalog_search_%s_index_select', $this->_type),
        );
        foreach ($eventNames as $eventName) {
            Mage::dispatchEvent(
                $eventName,
                array(
                    'select'        => $select,
                    'entity_field'  => new Zend_Db_Expr('e.entity_id'),
                    'website_field' => new Zend_Db_Expr('website.website_id'),
                    'store_field'   => $storeId
                )
            );
        }

        $result = array();
        $values = $adapter->fetchAll($select);
        foreach ($values as $value) {
            $result[(int) $value['entity_id']] = $value;
        }

        return array_map(array($this, '_fixBaseFieldTypes'), $result);
    }

    protected function _getSupperAttributeIds( &$entityAttributes){
        $entityIds = array();
        foreach($entityAttributes as $entityAttribute){
            if($entityAttribute['type_id'] == 'configurable'){
                $entityIds[] = $entityAttribute['entity_id'];
            }
        }
        if(!empty($entityIds)){
            $adapter   = $this->getConnection();
            $select = $adapter->select()
                ->from(
                    array('main' => $this->getTable('catalog/product_super_attribute'))
                )
                ->where("main.product_id IN (?)", $entityIds);
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->columns(array('main.product_id','main.attribute_id'))
                ->order(array(
                    'main.product_id','main.position',
                ));
            $values = $adapter->fetchAll($select);
            foreach ($values as $value) {
                if(isset($entityAttributes[$value['product_id']])){
                    $entityAttributes[$value['product_id']]['supper_attribute_ids'][] = $value['attribute_id'];
                }
            }
        }
    }

    protected function _getRequestPathForProduct(&$entityAttributes,$store_id,$languageCode)
    {
        $entityIds = array();
        foreach($entityAttributes as $entityAttribute){
            $entityIds[] = $entityAttribute['entity_id'];
        }

        if(!empty($entityIds)) {
            $adapter = $this->getConnection();
            $idField = $adapter->getIfNullSql('url_rewrite_cat.id', 'default_urc.id');
            $requestPath = $adapter->getIfNullSql('url_rewrite.request_path', 'default_ur.request_path');

            $select = $adapter->select()
                ->from(array('main_table' => $this->getTable('catalog/product')),
                    array('product_id' => 'main_table.entity_id','id' => $idField))
                ->where('main_table.entity_id in (?)', $entityIds)
                ->joinLeft(array('url_rewrite_cat' => $this->getTable('enterprise_catalog/product')),
                    'url_rewrite_cat.product_id = main_table.entity_id AND url_rewrite_cat.store_id = ' .
                    (int)$store_id,
                    array(''))
                ->joinLeft(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                    'url_rewrite.url_rewrite_id = url_rewrite_cat.url_rewrite_id',
                    array(''))
                ->joinLeft(array('default_urc' => $this->getTable('enterprise_catalog/product')),
                    'default_urc.product_id = main_table.entity_id AND default_urc.store_id = 0',
                    array(''))
                ->joinLeft(array('default_ur' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                    'default_ur.url_rewrite_id = default_urc.url_rewrite_id',
                    array('request_path' => $requestPath));
            $values = $adapter->fetchAssoc($select);
            foreach ($values as $value) {
                if(isset($entityAttributes[$value['product_id']])){
                    $entityAttributes[$value['product_id']]['request_path_' . $languageCode] = $value['request_path'];
                }
            }
        }
    }
}