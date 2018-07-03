<?php

class Imaginato_Contacts_Block_Adminhtml_Customer_Service_Enquery_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

  protected function _prepareForm()
  {
        $model = Mage::registry('enquery_data');
        $data = $model->getData();

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('enquery', array('legend'=>Mage::helper('imaginato_contacts')->__('Category information')));

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));
        }

        $fieldset->addField('enabled', 'select', array(
            'label' => Mage::helper('imaginato_contacts')->__('Enabled'),
            'required' => true,
            'name' => 'enabled',
            'values' => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('imaginato_contacts')->__('Yes'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('imaginato_contacts')->__('No'),
              ))
        ));

        $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('imaginato_contacts')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                    'name'      => 'stores[]',
                    'label'     => Mage::helper('imaginato_contacts')->__('Select Store'),
                    'title'     => Mage::helper('imaginato_contacts')->__('Select Store'),
                    'required'  => true,
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                ));
        }
        else {
            $fieldset->addField('stores', 'hidden', array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                ));
        }

        $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('imaginato_contacts')->__('Email address'),
          'name'      => 'email',
          'required'  => true
        ));

        $fieldset->addField('short_order', 'text', array(
          'label'     => Mage::helper('imaginato_contacts')->__('Order'),
          'name'      => 'short_order',
        ));

        $fieldset->addField('enquery_type_id', 'select', array(
          'label'     => Mage::helper('imaginato_contacts')->__('Enquery type'),
          'name'      => 'enquery_type_id',
          'required'  => true,
          'values' => Mage::getModel('imaginato_contacts/config_source_enquerytype')->toOptionArray()
        ));

     
      if(!empty($data)) {
            $form->setValues($data);
        }
      return parent::_prepareForm();
  }
}