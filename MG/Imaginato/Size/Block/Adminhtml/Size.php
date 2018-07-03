<?php

class Imaginato_Size_Block_Adminhtml_Size extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_size';
        $this->_blockGroup = 'size';
        $this->_headerText = Mage::helper('size')->__('Size Chart');
        $this->_addButtonLabel = Mage::helper('size')->__('Add New Size Chart');
        parent::__construct();
    }

}
