<?php

class Imaginato_SpecialPrice_Block_Adminhtml_Record_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'imaginato_specialprice';

    public function __construct()
    {
        $this->_objectId = 'record_id';
        $this->_controller = 'adminhtml_record';
        $this->_mode = 'edit';

        parent::__construct();
        $this->_removeButton('back');



    }

    protected function _prepareLayout()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareLayout();
    }
}
