<?php

class Imaginato_SpecialPrice_Block_Adminhtml_Record_Grid extends Mage_Adminhtml_Block_Template
{
    protected $_website_rocords;
    protected $_rocord_collection;
    /**
     * Initialize template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('imaginato/specialprice/list.phtml');
        $this->initCollection();
    }

    protected function initCollection()
    {
        $collection = Mage::getModel('imaginato_specialprice/record')->getCollection();
        $collection->addWebsitesToResult();
        $collection->addProductsToResult();
        $collection->load();

        $website_records = array();
        foreach(Mage::app()->getWebsites() as $website){
            $website_records[$website->getId()] = array();
        }
        foreach($collection->getItems() as $item){
            foreach($item->getWebsiteIds() as $websiteId){
                $website_records[$websiteId][] = $item->getId();
            }
        }
        $this->_website_rocords = $website_records;
        $this->_rocord_collection = $collection;
    }

    public function getWebsiteRocords(){
        return $this->_website_rocords;
    }

    public function getRocord($record_id){
        return $this->_rocord_collection->getItemById($record_id);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('record_id',array(
            'header'    => $this->_getHelper()->__('ID'),
            'width'     => '10',
            'type'      => 'number',
            'sortable'  => true,
            'index'     => 'record_id',
        ));
        $this->addColumn('name', array(
            'header'    => $this->_getHelper()->__('Record Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));
        $this->addColumn('discount_rate', array(
            'header'    => $this->_getHelper()->__('MD%'),
            'align'     => 'left',
            'type'      => 'number',
            'index'     => 'discount_rate',
        ));
        $this->addColumn('from_date', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Date Start'),
            'index'     => 'from_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('to_date', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Date Expire'),
            'index'     => 'to_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('rule_website', array(
                'header'    => Mage::helper('salesrule')->__('Website'),
                'align'     =>'left',
                'index'     => 'website_ids',
                'type'      => 'options',
                'sortable'  => false,
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width'     => 200,
            ));
        }
        $this->addColumn('created_at', array( 
            'header'    => $this->_getHelper()->__('Created At'),
            'align'     => 'left',
            'type'      => 'datetime',
            'sortable'  => true,
            'index'     => 'created_at'
        ));
        $this->addColumn('updated_at', array( 
            'header'    => $this->_getHelper()->__('Updated At'),
            'align'     =>    'left',
            'type'      => 'datetime',
            'sortable'  => true,
            'index'     => 'updated_at'
        ));

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('record_id');
        $this->setMassactionIdFilter('record_id');
        $this->getMassactionBlock()->setFormFieldName('record_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->_getHelper()->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->_getHelper()->__('Are you sure?')
        ));
        return parent::_prepareMassaction();
    }
    
    /**
     * @return Imaginato_ProductPriceRules_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function _getHelper()
    {
        return Mage::helper('imaginato_specialprice');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('record_id' => $row->getId()));
    }
}
