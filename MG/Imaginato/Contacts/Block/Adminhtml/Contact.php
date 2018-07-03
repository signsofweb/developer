<?php

class Imaginato_Contacts_Block_Adminhtml_Contact extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_contact';
        $this->_headerText = $this->helper('imaginato_contacts')->__('Manage Contacts');
        $this->_blockGroup = 'imaginato_contacts';
        parent::__construct();
		$this->_removeButton('add');
    }
}
