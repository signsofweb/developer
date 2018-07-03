<?php
   
class Imaginato_Googletagmanager_Block_Hreflangs extends Mage_Core_Block_Template
{ 
    public function getHreflangs(){
        $result = [];
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $result[] = [
                'lang_code'=> str_replace('_', '-', strtolower(Mage::getStoreConfig('general/locale/code',$store->getStoreId()))),
                'store_url'=> $store->getBaseUrl()
            ] ;
        } 
        return $result;
    }
}