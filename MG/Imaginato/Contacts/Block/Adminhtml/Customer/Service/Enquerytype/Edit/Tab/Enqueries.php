<?php

class Imaginato_Contacts_Block_Adminhtml_Customer_Service_Enquerytype_Edit_Tab_Enqueries extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('imaginatoEnquerytypeEnqueriesGrid');
    $this->setDefaultSort('entity_id');
    $this->setDefaultDir('desc');
    $this->setSaveParametersInSession(true);
  }

  protected function _prepareLayout()
  {
      parent::_prepareLayout();
  }

  protected function _prepareCollection()
    {
        $collection = Mage::getModel('imaginato_contacts/enquerytype')->getCollection();
        if ($this->getEnquerytype()->getId()) {
          $constraint = 'related.enquery_type_id='.$this->getEnquerytype()->getId();
        } else {
          $constraint = 'related.enquery_type_id is null';
        }
        $collection->setOrder('related.short_order', 'asc');
        $collection->getSelect()->joinLeft(
          array('related' => $collection->getTable('imaginato_contacts/enqueries')),
          'related.enquery_type_id=main_table.entity_id where '.$constraint)
          ->reset(Zend_Db_Select::COLUMNS)
          ->columns(['related.entity_id', 'related.title']);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

  protected function _prepareColumns()
  {
    $this->addColumn('enquery_id',
        array(
            'header' => $this->_getHelper()->__('ID'),
            'width'  => '1',
            'type'   => 'number',
            'sortable'=>true,
            'index'  => 'entity_id',
        )
    );
    $this->addColumn('enquery_title', 
      array(
          'header'=>$this->_getHelper()->__('Title'),
          'sortable'=>false,
          'index'=>'title',
          'name'=>'enquery_title'
      )
    );
    $this->addColumn('action',
      array(
          'header'    =>  $this->_getHelper()->__('Action'),
          'width'     => '100',
          'type'      => 'action',
          'getter'    => 'getId',
          'actions'   => array(
              array(
                  'caption'   => $this->_getHelper()->__('Edit'),
                  'url'       => array('base'=> '*/customer_service_enqueries/edit'),
                  'field'     => 'entity_id'
              )
          ),
          'filter'    => false,
          'sortable'  => false,
          'index'     => 'stores',
          'is_system' => true,
      ));
    return $this;
  }

  protected function getEnquerytype()
  {
    return Mage::registry('enquerytype_data');
  }

  public function _getHelper()
  {
    return Mage::helper('imaginato_contacts');
  }
}