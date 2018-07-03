<?php
class Imaginato_ProductPriceRules_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $defaultPercent = 100;
    protected function _initRule($ruleId = false)
    {
        $rule = Mage::getModel('skusrule/rule_product_price');
        // 2. Initial checking
        if ($ruleId) {
            $rule->load($ruleId);
            if (!$rule->getId()) {
                return false;
            }
        }
        return $rule;
    }

    public function getProduct( $productId, $storeId )
    {
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
        return $product;
    }
    public function bathUpdate( $productIds, $storeId )
    {
        foreach ( $productIds as $_productId )
        {
            $product = $this->getProduct( $_productId, $storeId );
            $this->updateProductInRule($product);
        }
        return $this;
    }

    public function updateProductSpecialPrice($rule)
    {
        $website = Mage::getModel('core/website')->load($rule->getWebsiteId());
        $storeIds = $website->getStoreIds();
        $storeId = 0;
        foreach($storeIds as $_storeId){
            $storeId = $_storeId;
        }
        $str = str_replace("\n",",",$rule->getSkus());
        $skus = explode(',', $str);
        foreach($skus as $_sku) {
            $_productId = Mage::getModel('catalog/product')->getIdBySku($_sku);
            $_product = $this->getProduct( $_productId, $storeId );
            if($_product->getId()){
                $_product->setSpecialPrice('');
                //To stop Magento regenerating url-key for store, set following,
                $_product->setUrlKey(false);
                $_product->save();
            }
        }
    }

    public function updateProductInRule($_product)
    {
        if($_product->getRuleId()){
            $_product->setRuleId(0);
            return false;
        }
        if(!$_product->getStoreId()){
            return false;
        }
        $rule = $this->_initRule();
        $store = Mage::getModel('core/store')->load($_product->getStoreId());
        $percent = number_format((($_product->getPrice() - $_product->getSpecialPrice())/$_product->getPrice()) * $this->defaultPercent, 2);
        if($percent < 0){
            return false;
        }
        $result = $rule->getRuleBySkuAndWebsiteId($_product->getSku(), $store->getWebsiteId());
        if(count($result)){
            $_percent = number_format($result[0]['percent'],2);
            if($percent%$_percent != 0){
                $str = str_replace("\n",",",$result[0]['skus']);
                $skus = explode(',', $str);
                $listSku = array_diff($skus, array($_product->getSku()));
                $rule->insertSkuByRuleId( (int)$result[0]['rule_id'], $listSku );
                $rule->insertSkuForRuleByWebsiteIdAndPercent($_product->getSku(), $store->getWebsiteId(), $percent);
            }
        }else{
            $result = $rule->getRuleByPercentAndWebsiteId($percent, $store->getWebsiteId());
            if(count($result)){
                $rule->insertSkuForRuleByWebsiteIdAndPercent($_product->getSku(), $store->getWebsiteId(), $percent);
            }
        }
    }

    public function getRuleProducts($rule_id)
    {
        $rule = Mage::getModel('skusrule/rule_product_price')->load($rule_id);
        $productIds = array();
        if($rule->getSkus()){
            $productSkus = explode(',', $rule->getSkus());
            foreach($productSkus as $productSku)
            {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productSku);
                if($product->getId())
                {
                    $productIds[] = $product->getId();
                }
            }
        }
        return $productIds;
    }
}
