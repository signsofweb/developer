<?php

class Imaginato_CartPrompSales_Adminhtml_Promo_cartSalesController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('promo/cartsales');
        $this->_title($this->__('Shopping Cart Promp Sales'));

        $this->renderLayout();
    }

    public function saveAction(){
        $product = $this->getRequest()->getPost('promp_products', array());
        $store_id = $this->getRequest()->getPost('store_id', 0);

        $products = array();
        $products_split = explode('&', $product);
        foreach ($products_split as $row) {
            $arr = explode('=', $row);
            if (count($arr) == 2) {
                $products[$arr[0]] = $arr[1];
            }
        }

        Mage::getModel('cartprompsales/product')->savePrompProducts($store_id,$products);

        return $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('cartprompsales/main_edit_form_product', 'cartprompsales.product.grid',
                array('js_form_object' => 'conditions_fieldset')
            )->toHtml()
        );
    }
}
