<?php

/**
 * Class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit_Tab_Form
 *
 */
class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_blockGroup = 'skusrule';

    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('promo_product_rule');
        $data = $model->getData();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_General', array('legend'=>Mage::helper('skusrule')->__('General information')));
        
        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }
        $fieldset->addField('name', 'text', array(
            'name'  => 'rule_name',
            'class'     => '',
            'required'  => true,
            'label'     => Mage::helper('skusrule')->__('Rule Name')
        ));

        $fieldset->addField('percent', 'text', array(
            'name'  => 'percent',
            'class'     => 'validate-not-negative-number',
            'required'  => true,
            'label'     => Mage::helper('skusrule')->__('MD%')
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_id', 'hidden', array(
                'name'     => 'website_id',
                'value'    => $websiteId
            ));
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField('website_id', 'select', array(
                'name'     => 'website_id',
                'label'     => Mage::helper('skusrule')->__('Websites'),
                'title'     => Mage::helper('skusrule')->__('Websites'),
                'required' => true,
                'disabled' => $model->getId()? true : false,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }

        if(!empty($data)) {
            $form->setValues($data);
        }
       return parent::_prepareForm();
    }
}
