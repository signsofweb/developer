<?php

class Imaginato_Catalog_Helper_Data extends Mage_Core_Helper_Data
{
    public function canDelete($product){
        $black_list = Mage::getStoreConfig('catalog/blacklist/productids');
        $black_list_array = explode(',',$black_list);
        $product_sku = $product->getData('sku');
        return !in_array($product_sku,$black_list_array);
    }
}
