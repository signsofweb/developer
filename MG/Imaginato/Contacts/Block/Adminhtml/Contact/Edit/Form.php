<?php

/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Form
 *
 */
class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_blockGroup = 'imaginato_contacts';

    protected function _prepareForm()
    {
        $this->_objectId = 'entity_id';

        $form = new Varien_Data_Form(array(
										'id' => 'edit_form',
										'action' => '',
										'method' => 'post',
										'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
