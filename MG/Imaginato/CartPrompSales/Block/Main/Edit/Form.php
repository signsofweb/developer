<?php

class Imaginato_CartPrompSales_Block_Main_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('cart_promop_sales_form');
        $this->setTitle(Mage::helper('catalogrule')->__('Rule Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post'));
        $form->setUseContainer(true);


        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('imaginato/cartpromosales/fieldset.phtml')->setProductsJson($this->getProductsJson());

        $form->addFieldset('conditions_fieldset',array())->setRenderer($renderer);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getProductsJson()
    {
        $store_id = $this->getRequest()->getPost('store_id', 0);
        $products = Mage::getModel('cartprompsales/product')->getProductsPosition($store_id);
        if (!empty($products)) {
            $this->getRequest()->setPost('selected_products', array_keys($products));
            return Mage::helper('core')->jsonEncode($products);
        }
        return '{}';
    }

}
