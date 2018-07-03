<?php
class Imaginato_ProductPriceRules_Model_Catalog_Product_Observer 
{
    /**
     * Get rule helper
     *
     * @return Imaginato_ProductPriceRules_Helper_Data
     */
    protected function getRuleHelper()
    {
        return Mage::helper('skusrule');
    }

    /**
     * Remove product in rule
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function updateProductInRule(Varien_Event_Observer $observer)
    {
        $this->getRuleHelper()->updateProductInRule($observer->getEvent()->getProduct());
        return $this;
    }
    
}