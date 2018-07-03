<?php

class Imaginato_ElasticSearchExt_Model_Resource_Catalog_Product_Collection extends Smile_ElasticSearch_Model_Resource_Catalog_Product_Collection
{
    protected $_searchResult = null;

    protected $_childSearchResult = null;

    protected $_childrenIds = array();
    protected $_limit = false;
    public function getChildrenIds(){
        return $this->_childrenIds;
    }

    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $this->_prepareQuery();
            $this->_searchResult = $this->_searchEngineQuery->search();
            $this->_totalRecords = isset($this->_searchResult['total_count']) ? $this->_searchResult['total_count'] : null;
            if(Mage::helper('imaginato_elasticsearchext/debug')->enable()){
                Mage::log(Mage::app()->getRequest()->getRequestString(),1,'debug/elastic_search.log');
                Mage::log(Mage::app()->getRequest()->getRequestUri(),1,'debug/elastic_search.log');
                Mage::log('total: '.$this->_totalRecords,1,'debug/elastic_search.log');
            }
            if (isset($this->_searchResult['facets'])) {
                $this->_facets = array_merge($this->_facets, $this->_searchResult['facets']);
            }

            $this->_isSpellChecked = $this->_searchEngineQuery->isSpellchecked();
        }

        return $this->_totalRecords;
    }

    protected function _beforeLoad()
    {
        $ids = array();
        if(!$this->_searchResult){
            $this->_prepareQuery();
            $this->_searchResult = $this->_searchEngineQuery->search();
        }
        $ids = isset($this->_searchResult['ids']) ? $this->_searchResult['ids'] : array();
        if (isset($this->_searchResult['facets'])) {
            $this->_facets = array_merge($this->_facets, $this->_searchResult['facets']);
        }
        $this->_totalRecords = isset($this->_searchResult['total_count']) ? $this->_searchResult['total_count'] : null;
        $this->_isSpellChecked = $this->getSearchEngineQuery()->isSpellchecked();

        if (empty($ids)) {
            $ids = array(0); // Fix for no result
        }

        $this->addIdFilter($ids);
        $this->_searchedEntityIds = $ids;
        $this->_limit = $this->_pageSize;
        $this->_pageSize = false;

        return Mage_Catalog_Model_Resource_Product_Collection::_beforeLoad();
    }

    /**
     * Retrieves parameters.
     *
     * @return array
     */
    protected function _prepareQuery()
    {
        $query = $this->getSearchEngineQuery();
        $toolbar = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar', microtime());

        $this->_curPage = $toolbar->getCurrentPage();
        $this->_pageSize = (int)$toolbar->getLimit();

        if ($toolbar->getCurrentOrder()) {
            $this->setOrder($toolbar->getCurrentOrder(), $toolbar->getCurrentDirection());
        }

        if (!empty($this->_sortBy)) {
            $query->addSortOrder($this->_sortBy);
        }

        if ($this->_pageSize !== false && $this->_curPage !== false) {
            $query->setPageParams($this->_curPage, $this->_pageSize);
        }

        if ($this->getStoreId()) {
            $query->addFilter('terms', array('store_id' => $this->getStoreId()));
        }

        if (!empty($this->_facets)) {
            $query->resetFacets();
        }
        $this->_searchEngineQuery = $query;
    }

    /**
     * Load entities records into items
     *
     * @throws Exception
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $entity = $this->getEntity();

        if ($this->_pageSize) {
            $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
        }

        $this->printLogQuery($printQuery, $logQuery);

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->_prepareSelect($this->getSelect());
            $rows = $this->_fetchAll($query);
        } catch (Exception $e) {
            Mage::printException($e, $query);
            $this->printLogQuery(true, true, $query);
            throw $e;
        }
        $this->_childrenIds = array();
        foreach($this->_searchResult['docs'] as $doc){
            if(isset($doc['children_ids'])){
                $this->_childrenIds = array_merge($this->_childrenIds,$doc['children_ids']);
            }
        }

        if(!empty($this->_childrenIds)){
                $query = $this->_engine->createQuery('product');
                if ($this->getStoreId()) {
                    $store = Mage::app()->getStore($this->getStoreId());
                    $query->setLanguageCode(Mage::helper('smile_elasticsearch')->getLanguageCodeByStore($store));
                }
                $query->addFilter('terms', array('store_id' => $this->getStoreId()));
                $query->addFilter('terms', array('entity_id' => $this->_childrenIds));
                $this->_childSearchResult = $query->search();

        }
        foreach ($rows as $v) {
            $object = $this->getNewEmptyItem()
                ->setData($v);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = array($object);
            }
        }
        if(Mage::helper('imaginato_elasticsearchext/debug')->enable()){
            Mage::log('elastic ids: '.json_encode($this->_searchResult['ids']),1,'debug/elastic_search.log');
            Mage::log('magento ids: '.json_encode(array_keys($this->_itemsById)),1,'debug/elastic_search.log');
        }

        return $this;
    }

    protected function _afterLoad()
    {
        $languageCode = '';
        if ($this->getStoreId()) {
            $store = Mage::app()->getStore($this->getStoreId());
            $languageCode = Mage::helper('smile_elasticsearch')->getLanguageCodeByStore($store);
        }
        $sortedItems = array();
        foreach ($this->_searchResult['ids'] as $id) {
            if (isset($this->_items[$id])) {
                $sortedItems[$id] = $this->_items[$id];
            }
        }
        $this->_items = &$sortedItems;

        foreach($this->_items as $item){
            $doc = $this->_searchResult['docs'][$item->getId()];
            if(isset($doc['entity_id'])){
                unset($doc['entity_id']);
                if(isset($doc['thumbnail'])){
                    $doc['thumbnail'] = $doc['thumbnail'][0];
                }
                if(isset($doc['small_image'])){
                    $doc['small_image'] = $doc['small_image'][0];
                }
                if(isset($doc['small_image'])){
                    $doc['alt_small_image'] = $doc['alt_small_image'][0];
                }
                if(isset($doc['request_path_' . $languageCode])){
                    $doc['request_path'] = $doc['request_path_' . $languageCode][0];
                    unset($doc['request_path_' . $languageCode]);
                }
                $item->addData($doc);
                if(isset($doc['children_ids'])){
                    $children = array();
                    foreach($doc['children_ids'] as $child_id){
                        if(isset($this->_childSearchResult['docs'][$child_id])){
                            $childDoc = $this->_childSearchResult['docs'][$child_id];
                            if(isset($childDoc['thumbnail'])){
                                $childDoc['thumbnail'] = $childDoc['thumbnail'][0];
                            }
                            if(isset($childDoc['small_image'])){
                                $childDoc['small_image'] = $childDoc['small_image'][0];
                            }
                            if(isset($childDoc['small_image'])){
                                $childDoc['alt_small_image'] = $childDoc['alt_small_image'][0];
                            }
                            $children[$child_id] = $childDoc;
                        }
                    }
                    if(!empty($children)){
                        $item->setChildren($children);
                    }
                }
            }
        }

        return $this;
    }

    protected  function prepareChildSelect($children_ids){
        $select = $this->_conn->getSelect();
        if ($this->isEnabledFlat()) {
            $select
                ->from(array(self::MAIN_TABLE_ALIAS => $this->getEntity()->getFlatTableName()), null)
                ->columns(array('status' => new Zend_Db_Expr(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)));
            $this->addAttributeToSelect(array('entity_id', 'type_id', 'attribute_set_id'));
        } else {
            $select->from(array(self::MAIN_TABLE_ALIAS => $this->getEntity()->getEntityTable()));
        }
        $select->reset(Zend_Db_Select::SQL_WHERE);
        $select->where('entity_id IN (?)',$children_ids);
    }

    public function getLastPageNumber()
    {
        $collectionSize = (int) $this->getSize();

        if((int)$this->_limit > 0){
            $this->_pageSize = $this->_limit;
        }

        if (0 === $collectionSize) {
            return 1;
        }
        elseif($this->_pageSize) {
            return ceil($collectionSize/$this->_pageSize);
        }
        else{
            return 1;
        }
    }
}