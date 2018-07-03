<?php

class Imaginato_Orderexport_Model_Resource_Lineitem extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement    = false;
    protected $_useIsObjectNew       = true;
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('imaginato_orderexport/lineitem', 'item_id');
    }
}
