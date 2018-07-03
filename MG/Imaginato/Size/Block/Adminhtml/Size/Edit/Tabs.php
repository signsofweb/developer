<?php

/**
 * Class Imaginato_Size_Block_Adminhtml_Size_Edit_Tabs
 */
class Imaginato_Size_Block_Adminhtml_Size_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Imaginato_Size_Block_Adminhtml_Size_Edit_Tabs constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('size_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('size')->__('Size Chart'));
    }
}
