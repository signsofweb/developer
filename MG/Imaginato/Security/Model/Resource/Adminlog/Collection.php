<?php
class Imaginato_Security_Model_Resource_Adminlog_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('imaginato_security/adminlog');
    }
}