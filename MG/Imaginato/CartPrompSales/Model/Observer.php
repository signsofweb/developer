<?php

class Imaginato_CartPrompSales_Model_Observer
{
    public function addCartCheck(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('cartprompsales/generl/active') || !Mage::getStoreConfig('cartprompsales/generl/check_to_add')) {
            return $this;
        }
        $_cart = Mage::getSingleton('checkout/cart');

        $product = $observer->getEvent()->getProduct();

        $prompSalesProducts = Mage::getModel('cartprompsales/product')->getProductsPosition(0);

        if(!in_array($product->getId(),array_keys($prompSalesProducts))){
            return $this;
        }

        $cartProductNow = array_diff($_cart->getProductIds(),array($product->getId()));
        if ($cartProductNow && array_keys($prompSalesProducts) && array_intersect($cartProductNow,array_keys($prompSalesProducts))) {
            Mage::throwException(
                Mage::helper('core')->__('Only one special item can be added to cart.')
            );
        }
        return $this;
    }
    public function checkPrompSalesProduct(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('cartprompsales/generl/active') || !Mage::getStoreConfig('cartprompsales/generl/check_to_cart')) {
            return $this;
        }
        $quote = $observer->getQuote();

        $prompSalesProducts = Mage::getModel('cartprompsales/product')->getProductsPosition(0);
        $prompSalesProductIds = array_keys($prompSalesProducts);
        if(empty($prompSalesProductIds)){
            return $this;
        }
        $quoteSubtotal = $this->getQuoteSubtotal($quote);

        $checkSubtotal = Mage::getStoreConfig('cartprompsales/generl/check_to_cart_total');
        if(doubleval($checkSubtotal)>$quoteSubtotal){
            foreach($quote->getAllVisibleItems() as $item){
                if(in_array($item->getProductId(),$prompSalesProductIds)){
                    $item->isDeleted(true);
                }
            }
        }

        return $this;
    }

    public function getQuoteSubtotal($quote){

        $checkCategory = Mage::getStoreConfig('cartprompsales/generl/check_to_cart_category_blacklist');
        $quoteSubtotal = 0 ;
        if(empty($checkCategory) || empty(explode(',',$checkCategory))){
            $quoteSubtotal = $quote->getBaseGrandTotal();
        }else{
            $checkCategoryIds = explode(',',$checkCategory);
            foreach($quote->getAllVisibleItems() as $item){
                $categorIds = $item->getProduct()->getCategoryIds();
                if(!array_intersect($categorIds,$checkCategoryIds)){
                    $quoteSubtotal += $item->getBaseRowTotalInclTax();
                }
            }
        }
        return $quoteSubtotal;
    }
}
