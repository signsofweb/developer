<?php
/*
 *
 */

class  Imaginato_Contacts_Block_Adminhtml_System_Config_Form_Field_Subjects extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $_req = ' <span class="required">*</span>';
        $_hlp = Mage::helper('contacts');

        $this->addColumn('value',
            array(
                'label'     => $_hlp->__('Subjects').$_req,
                'style'     => 'width:400px',
                'class'     => 'required-entry',
                'renderer'  => false,
            )
        );

        $this->_addAfter        = false;
        $this->_addButtonLabel  = $_hlp->__('Add subject');

        parent::__construct();
    }
}