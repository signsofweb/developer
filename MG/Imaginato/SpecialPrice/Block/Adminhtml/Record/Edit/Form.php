<?php

class Imaginato_SpecialPrice_Block_Adminhtml_Record_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_blockGroup = 'imaginato_specialprice';

    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'));
        $form->setFieldNameSuffix('general');
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_General', array('legend' => Mage::helper('imaginato_specialprice')->__('General information')));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'class' => '',
            'required' => true,
            'label' => Mage::helper('imaginato_specialprice')->__('Record Name')
        ));

        $fieldset->addField('skus', 'text', array(
            'name' => 'skus',
            'class' => '',
            'required' => true,
            'label' => Mage::helper('imaginato_specialprice')->__('Skus')
        ));

        $fieldset->addField('discount_rate', 'text', array(
            'name' => 'discount_rate',
            'class' => 'validate-number',
            'required' => true,
            'label' => Mage::helper('imaginato_specialprice')->__('MD%')
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => Mage::helper('imaginato_specialprice')->__('From Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'label' => Mage::helper('imaginato_specialprice')->__('To Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $field = $fieldset->addField('posted_websites', 'multiselect', array(
            'name' => 'posted_websites',
            'label' => Mage::helper('imaginato_specialprice')->__('Websites'),
            'title' => Mage::helper('imaginato_specialprice')->__('Websites'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $form->setValues(Mage::getSingleton('adminhtml/session')->getProductRuleData());
        return parent::_prepareForm();
    }
}
