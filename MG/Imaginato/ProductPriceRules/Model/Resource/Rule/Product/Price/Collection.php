<?php
class Imaginato_ProductPriceRules_Model_Resource_Rule_Product_Price_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('skusrule/rule_product_price');

    }
}
