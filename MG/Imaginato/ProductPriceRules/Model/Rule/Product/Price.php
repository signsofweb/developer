<?php
class Imaginato_ProductPriceRules_Model_Rule_Product_Price extends Mage_Core_Model_Abstract
{
    protected $defaultPercent = 100;
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('skusrule/rule_product_price');
    }

    public function validateData(Varien_Object $object)
    {
        $output   = array();
        if(!$object->getRuleId() && $object->getSkus()){
            $str = str_replace("\n",",",$object->getSkus());
            $skus = explode(',', $str);
            foreach($skus as $sku) {
                $result = $this->_getResource()->checkValidateSku($sku, $object->getWebsiteId());
                if(count($result)){
                    $output[] = Mage::helper('skusrule')->__('%s - This sku has exists in the another rule, please check again!', $sku);
                }
            }
        }
        return !empty($output) ? $output : true;
    }

    public function getRuleBySkuAndWebsiteId($sku, $websiteId)
    {
        $result = $this->_getResource()->getRuleBySkuAndWebsiteId($sku, $websiteId);
        return $result;
    }

    public function getRuleByPercentAndWebsiteId($percent, $websiteId)
    {
        $result = $this->_getResource()->getRuleByPercentAndWebsiteId($percent, $websiteId);
        return $result;
    }

    public function insertSkuForRuleByWebsiteIdAndPercent($sku, $websiteId, $percent)
    {
        $result = $this->_getResource()->insertSkuForRuleByWebsiteIdAndPercent($sku, $websiteId, $percent);
        return $result;
    }

    public function insertSkuByRuleId($ruleId, $listSku)
    {
        $this->_getResource()->insertSkuByRuleId($ruleId, $listSku);
    }

    public function removeSkuByRuleId($ruleId)
    {
        $this->_getResource()->removeSkuByRuleId($ruleId);
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        if($this->getPromoRule()['product_ids']){
            $productIds = explode('&', $this->getPromoRule()['product_ids']);
            $website = Mage::getModel('core/website')->load($this->getWebsiteId());
            $storeIds = $website->getStoreIds();
            $storeId = 0;
            foreach($storeIds as $_storeId){
                $storeId = $_storeId;
            }
            $listSku = array();
            foreach($productIds as $_productId) {
                $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($_productId);
                if($_product->getId()){
                    $listSku[] = $_product->getSku();
                    $price = $_product->getPrice() * (float)(($this->defaultPercent - $this->getPercent())/$this->defaultPercent);
                    $_product->setSpecialPrice($price);
                    $_product->setSpecialFromDate(false);
                    $_product->setSpecialFromDateIsFormated(true);
                    //To stop Magento regenerating url-key for store, set following,
                    $_product->setUrlKey(false);
                    $_product->setRuleId($this->getRuleId());
                    $_product->save();
                }
            }
            $this->insertSkuByRuleId($this->getRuleId(), $listSku);
        }
        return $this;
    }

    /**
     * Processing object before delete data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeDelete()
    {
        $this->removeSkuByRuleId($this->getRuleId());
        return $this;
    }
}
