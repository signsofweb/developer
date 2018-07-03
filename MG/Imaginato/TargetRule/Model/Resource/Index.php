<?php

class Imaginato_TargetRule_Model_Resource_Index extends Enterprise_TargetRule_Model_Resource_Index
{
    /**
     * Retrieve found product ids by Rule action conditions
     * If rule has cached select - get it
     *
     * @param Enterprise_TargetRule_Model_Rule $rule
     * @param Enterprise_TargetRule_Model_Index $object
     * @param int $limit
     * @param array $excludeProductIds
     * @return mixed
     */
    protected function _getProductIdsByRule($rule, $object, $limit, $excludeProductIds = array())
    {
        $rule->afterLoad();

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($object->getStoreId())
            ->addPriceData($object->getCustomerGroupId());
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        if (!Mage::getStoreConfigFlag('cataloginventory/options/show_out_of_stock')) {
            $stock_id = Mage::helper('warehouse')->getStockIdByStoreId($object->getStoreId());
            Mage::getModel('cataloginventory/stock_status')->setStockId($stock_id)->addIsInStockFilterToCollection($collection);
        }

        $actionSelect = $rule->getActionSelect();
        $actionBind   = $rule->getActionSelectBind();

        if (is_null($actionSelect)) {
            $actionBind   = array();
            $actionSelect = $rule->getActions()->getConditionForCollection($collection, $object, $actionBind);
            $rule->setActionSelect((string)$actionSelect)
                ->setActionSelectBind($actionBind)
                ->save();
        }

        if ($actionSelect) {
            $collection->getSelect()->where($actionSelect);
        }
        if ($excludeProductIds) {
            $collection->addFieldToFilter('entity_id', array('nin' => $excludeProductIds));
        }

        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('entity_id', 'e');
        $select->limit($limit);

        $bind   = $this->_prepareRuleActionSelectBind($object, $actionBind);
        $result = $this->_getReadAdapter()->fetchCol($select, $bind);

        return $result;
    }

    /**
     * Prepare bind array for product select
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @param array $actionBind
     * @return array
     */
    protected function _prepareRuleActionSelectBind($object, $actionBind)
    {
        $bind = array();
        if (!is_array($actionBind)) {
            $actionBind = array();
        }

        foreach ($actionBind as $bindData) {
            if (!is_array($bindData) || !array_key_exists('bind', $bindData) || !array_key_exists('field', $bindData)) {
                continue;
            }
            $k = $bindData['bind'];
            $v = $object->getProduct()->getDataUsingMethod($bindData['field']);

            if (!empty($bindData['callback'])) {
                $callbacks = $bindData['callback'];
                if (!is_array($callbacks)) {
                    $callbacks = array($callbacks);
                }
                foreach ($callbacks as $callback) {
                    if (is_array($callback)) {
                        $method = $callback[0];
                        $v = $this->$method($v, $callback[1]);
                    } else {
                        $v = $this->$callback($v);
                    }
                }
            }

            if (is_array($v)) {
                $v = join(',', $v);
            }

            $bind[$k] = $v;
        }

        return $bind;
    }
}

