<?php

/**
 * Class Imaginato_Shipment_Block_Adminhtml_Import_Edit_Form
 */
class Imaginato_Shipment_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add fieldset
     *
     * @return Imaginato_Shipment_Block_Adminhtml_Import_Edit_Form|Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('imaginato_shipment')->__('Import Settings')));
        $fieldset->addField(Imaginato_Shipment_Model_Import::FIELD_NAME_SOURCE_FILE, 'file', array(
            'name'     => Imaginato_Shipment_Model_Import::FIELD_NAME_SOURCE_FILE,
            'label'    => Mage::helper('imaginato_shipment')->__('Select File to Import'),
            'title'    => Mage::helper('imaginato_shipment')->__('Select File to Import'),
            'required' => true
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
