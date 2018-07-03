<?php

class Imaginato_Criteo_Block_Criteo extends Mage_Core_Block_Template
{
    protected $_account = '';

    protected $_helper = null;

    protected function _construct()
    {
        parent::_construct();

        $this->_helper = Mage::helper('criteo');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $isActive = $this->_helper->isActive();
        $this->_account = $this->_helper->getAccount();
        $viewEventHtml = $this->getViewEvent();

        if(!$isActive || !$this->_account || !$viewEventHtml){
            $this->setTemplate('');
        }
    }

    protected function getViewEvent()
    {
        $isloggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        $request = $this->getRequest();
        $action = $request->getActionName();
        $controller = $request->getControllerName();
        $module = $request->getModuleName();
        $identifier = trim($request->getPathInfo(), '/');
        $html = '';
        $itemsCount = 0;
        $transaction_id = '';
        if($controller == 'cart' && $action == 'index' && $module == 'checkout'){
            $quote  = Mage::getSingleton('checkout/session')->getQuote();
            $quote_items = array();
            foreach($quote->getAllItems() as $item){
                if(!$item->getParentItem()){
                    $quote_items[] = array(
                        'id'=>$item->getProduct()->getData('sku'),
                        'price'=>round($item->getCalculationPrice(), 2),
                        'quantity'=>(int)$item->getQty()
                    );
                }
            }
        }elseif($controller == 'onepage' && $action == 'success' && $module == 'checkout'){
            $checkout  = Mage::getSingleton('checkout/session');
            $orderId = $checkout->getData('last_order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $transaction_id = $order->getIncrementId();
            $items = $order->getItemsCollection();

            $_jsItems = array();
            foreach($items as $item){
                if($item->getParentItemId()){
                    continue;
                }
                $_jsItems[] = array(
                    'id'=>$item->getProduct()->getSku(),
                    'price'=>$item->getPrice(),
                    'quantity'=>(int)$item->getQtyOrdered()
                );
            }

            $_jsItems = json_encode( $_jsItems,true);
        }

        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();

        if($action == 'view' && $controller == 'page' && $module == 'cmsadvanced' && empty($identifier)){
            //home page
            $html = '{ event: "viewHome" }';
        }elseif($action == 'index' && $controller == 'index' && $module == 'cms' && empty($identifier)){
            //home page
            $html = '{ event: "viewHome" }';
        }elseif($controller == 'category' && $action == 'view' && $module == 'catalog'){
            //category product list
            $html = '{ event: "viewList", item: eval("["+jQuery("#criteo_items").html()+"]") }';
        }elseif($controller == 'result' && $action == 'index' && $module == 'catalogsearch'){
            //search result list
            $html = '{ event: "viewList", item: eval("["+jQuery("#criteo_items").html()+"]") }';
        }elseif($controller == 'product' && $action == 'view' && $module == 'catalog'){
            //product detail
            $product = Mage::registry('current_product');
            $html = '{ event: "viewItem", item: "'.$product->getSku().'" }';
        }elseif($controller == 'cart' && $action == 'index' && $module == 'checkout'){
            $html = '{ event: "viewBasket",currency:"'.$currency.'", item: '.json_encode($quote_items,true).'}';
        }elseif($controller == 'onepage' && $action == 'success' && $module == 'checkout' && !empty($_jsItems)){
            $html = '{ event: "trackTransaction",currency:"'.$currency.'",id: "'.$transaction_id.'",item:  eval('.$_jsItems.')}';
        }

        return $html;
    }
}