<?php
 class Imaginato_ElasticSearchExt_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
 {
     public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
     {
         $type_id = $product->getTypeId();
         if (Mage::helper('catalog')->canApplyMsrp($product)) {
             $realPriceHtml = $this->_preparePriceRenderer($type_id)
                 ->setProduct($product)
                 ->setDisplayMinimalPrice($displayMinimalPrice)
                 ->setIdSuffix($idSuffix)
                 ->toHtml();
             $product->setAddToCartUrl($this->getAddToCartUrl($product));
             $product->setRealPriceHtml($realPriceHtml);
             $type_id = $this->_mapRenderer;
         }

         return $this->_preparePriceRenderer($type_id)
             ->setProduct($product)
             ->setDisplayMinimalPrice($displayMinimalPrice)
             ->setIdSuffix($idSuffix)
             ->toHtml();
     }

     public function _preparePriceRenderer($productType)
     {
         return $this->_getPriceBlock($productType)
             ->setTemplate('catalog/product/list_price.phtml')
             ->setUseLinkForAsLowAs($this->_useLinkForAsLowAs);
     }

     public function isSaleable($product){
         if($product->getTypeId() == 'configurable'){
             $isSaleable = false;
             $children = $product->getChildren();
             foreach ($children as $child){
                 if($child['in_stock'][0] && $child['qty'][0] > 0){
                     $isSaleable = true;
                     break;
                 }
             }
             $product->setIsSalable($isSaleable);
             return $isSaleable;
         }else{
             return $product->isSaleable();
         }
     }
 }