<?php

class Imaginato_Contacts_Block_Adminhtml_Customer_Service_Enquerytype_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('enquerytype_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('imaginato_contacts')->__('General'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('general', array(
          'label'     => Mage::helper('imaginato_contacts')->__('General Information'),
          'title'     => Mage::helper('imaginato_contacts')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquerytype_edit_tab_form')->toHtml(),
      ));
	  
	  $this->addTab('enqueries', array(
          'label'     => Mage::helper('imaginato_contacts')->__('Enqueries'),
          'title'     => Mage::helper('imaginato_contacts')->__('Enqueries'),
          'content'   => $this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquerytype_edit_tab_enqueries')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}