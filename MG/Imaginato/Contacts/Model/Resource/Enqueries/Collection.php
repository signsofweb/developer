<?php

class Imaginato_Contacts_Model_Resource_Enqueries_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('imaginato_contacts/enqueries');
		$this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['stores'] = 'store_table.store_id';
    }

    public function addEnquerytypeFilter()
    {
        //collection = this
        $this->getSelect()->join(
            array('enquerytype' => $this->getTable('imaginato_contacts/enquerytype')),
            'main_table.enquery_type_id = enquerytype.entity_id where main_table.enabled = 1 and enquerytype.enabled = 1',
            array(
                'enquerytype_id' => 'enquerytype.entity_id',
                'enquerytype_title' => 'enquerytype.title',
                'enquerytype_short_order' => 'enquerytype.short_order',
            )
        )
        ->order('enquerytype.short_order', 'ASC')
        ->order('main_table.short_order', 'ASC');
        return $this;
    }

    public function addEnqueryStoreFilter()
    {
        //collection = this
        $this->getSelect()->join(
            array('enquerytype' => $this->getTable('imaginato_contacts/enquerytype')),
            'main_table.enquery_type_id = enquerytype.entity_id',
            array(
                'enquerytype_id' => 'enquerytype.entity_id',
                'enquerytype_title' => 'enquerytype.title',
                'enquerytype_short_order' => 'enquerytype.short_order',
            )
        )
        ->join(
            array('enquerytypestoreview' => $this->getTable('imaginato_contacts/enquerytypestoreview')),
            'enquerytypestoreview.enquery_type_id = enquerytype.entity_id',
            array()
        )
        ->join(
            array('enqueriesstoreview' => $this->getTable('imaginato_contacts/enqueriesstoreview')),
            'enqueriesstoreview.enquery_id = main_table.entity_id',
            array()
        )
        ->order('enquerytype.short_order', 'ASC')
        ->order('main_table.short_order', 'ASC');
        $this->addFieldToFilter('main_table.enabled', 1);
        $this->addFieldToFilter('enquerytype.enabled', 1);
        $store = Mage::app()->getStore()->getStoreId();
        $this->addFieldToFilter('enquerytypestoreview.store_id', [$store, 0]);
        $this->addFieldToFilter('enqueriesstoreview.store_id', [$store, 0]);
        return $this;
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
                array('store_table' => $this->getTable('imaginato_contacts/enqueriesstoreview')),
                'main_table.entity_id = store_table.enquery_id',
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