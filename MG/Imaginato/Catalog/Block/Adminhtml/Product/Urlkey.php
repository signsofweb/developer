<?php

class Imaginato_Catalog_Block_Adminhtml_Product_Urlkey extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('imaginato/catalog/urlkey/detail.phtml');
        $this->_controller = 'system_account';
        $this->_addButton('clear_product', array(
            'label'   => Mage::helper('enterprise_logging')->__('Clear Up Product'),
            'onclick' => "setLocation('" . Mage::getSingleton('adminhtml/url')->getUrl('*/*/clearProduct'). "')",
            'class'   => 'save'
        ));
        $this->_addButton('clear_category', array(
            'label'   => Mage::helper('enterprise_logging')->__('Clear Up Category'),
            'onclick' => "setLocation('" . Mage::getSingleton('adminhtml/url')->getUrl('*/*/clearCategory'). "')",
            'class'   => 'save'
        ));
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('Url Key Clear Up');
    }
    public function getNoDefaultData()
    {
        return Mage::registry('no_default_data');
    }
    public function getDiffUrlKeyData()
    {
        return Mage::registry('diff_url_key_data');
    }
    public function getRepeatUrlKeyData()
    {
        return Mage::registry('repeat_url_key_data');
    }
}
