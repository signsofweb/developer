<?php

class Imaginato_Contacts_Model_Resource_Enquerytype_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('imaginato_contacts/enquerytype');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['stores'] = 'store_table.store_id';
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        if (!is_array($store)) {
            $store = array($store);
        }

        if ($withAdmin) {
            $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $this->addFilter('stores', array('in' => $store), 'public');

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('stores')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('imaginato_contacts/enquerytypestoreview')),
                'main_table.entity_id = store_table.enquery_type_id',
                array()
            );

            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }
    
}