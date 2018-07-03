<?php

class Imaginato_CartPrompSales_Model_Resource_Product_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('cartprompsales/product');
    }
}
