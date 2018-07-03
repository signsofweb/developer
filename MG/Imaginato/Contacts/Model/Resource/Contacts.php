<?php

class Imaginato_Contacts_Model_Resource_Contacts extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('imaginato_contacts/contacts', 'entity_id');
    }
}