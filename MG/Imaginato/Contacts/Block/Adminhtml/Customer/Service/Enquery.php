<?php

class Imaginato_Contacts_Block_Adminhtml_Customer_Service_Enquery extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_customer_service_enquery';
        $this->_headerText = $this->helper('imaginato_contacts')->__('Manage Enquery');
        $this->_blockGroup = 'imaginato_contacts';
        $this->_addButtonLabel = $this->helper('imaginato_contacts')->__('Add New Enquery');
        parent::__construct();
    }
}
