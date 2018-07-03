<?php

/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Tab_Form
 *
 */
class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_blockGroup = 'imaginato_contacts';

    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('contact_data');
        $data = $model->getData();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_General', array('legend'=>Mage::helper('imaginato_contacts')->__('General information')));
		
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));
        }
        $fieldset->addField('name', 'text', array(
            'name'  => 'name',
            'class'     => '',
            'required'  => true,
            'label'     => 'Name'
        ));
        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
          $fieldset->addField('store_id', 'text', array(
            'name'  => 'store_id',
            'class'     => '',
            'required'  => true,
            'label'     => 'Store Id'
          ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
        }
        $fieldset->addField('order_number', 'text', array(
            'name'  => 'order_number',
            'label'     => 'Order Number'
        ));
        $fieldset->addField('subject', 'select', array(
            'name'  => 'subject',
            'required'  => true,
            'label'     => 'Subject',
			'values'    => Mage::getModel('imaginato_contacts/config_source_subject')->toOptionArray(true),
        ));
        $fieldset->addField('email', 'text', array(
            'name'  => 'email',
            'required'  => true,
            'label'     => 'Email Address'
        ));
        $fieldset->addField('comment', 'editor', array(
            'name'  => 'comment',
            'required'  => false,
            'label'     => 'Comment'
        ));
		$fieldset->addField('file', 'image', array(
            'label' => 'Attachment',
            'name' => 'file'
        ));
        if(!empty($data)) {
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}
