<?php

class Imaginato_TargetRule_Model_Resource_Rule extends Enterprise_TargetRule_Model_Resource_Rule
{
    public function cronFlush()
    {
        $collection = Mage::getModel('enterprise_targetrule/rule')->getCollection();
        $collection->addFilter('is_active','1');
        foreach($collection as $object){
            $this->unbindRuleFromEntity($object->getId(), array(), 'product');
            /** @var $catalogFlatHelper Mage_Catalog_Helper_Product_Flat */
            $catalogFlatHelper = Mage::helper('catalog/product_flat');
            $storeId = Mage::app()->getDefaultStoreView()->getId();

            if ($catalogFlatHelper->isEnabled() && $catalogFlatHelper->isBuilt($storeId)) {
                $this->_fillProductsByRule($object);
            } else {
                $this->bindRuleToEntity($object->getId(), $object->getMatchingProductIds(), 'product');
            }
        }
    }
}
