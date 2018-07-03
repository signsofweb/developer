<?php

/**
 * Location extension for Magento
 *
 */

/**
 * Class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Special_Price_Expired
 *
 */
class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Special_Price_Expired extends Mage_Core_Block_Template
{
    protected function _initModel()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('rule_id', null);
        $model = Mage::getModel('skusrule/rule_product_price');
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::throwException(Mage::helper('skusrule')->__('Wrong rule specified.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        return $model;
    }

    public function getStoreId()
    {
        $rule = $this->_initModel();
        $website = Mage::getModel('core/website')->load($rule->getWebsiteId());
        $storeIds = $website->getStoreIds();
        $storeId = 0;
        foreach($storeIds as $_storeId){
            $storeId = $_storeId;
        }
        return $storeId;
    }
    public function getListSkuByRuleId()
    {
        $rule = $this->_initModel();
        $skus = str_replace("\n",",",$rule->getSkus());
        $listSku = explode(',', $skus);
        return $listSku;
    }
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $rule = $this->_initModel();
        if(!$rule->getId()){
            return $html;
        }
        $listSku = $this->getListSkuByRuleId();
        if(empty($listSku[0])){
            return $html;
        }
        foreach($listSku as $_sku) {
            $_productId = Mage::getModel('catalog/product')->getIdBySku($_sku);
            $_product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())->load($_productId);
            $special_from_date = $_product->getSpecialFromDate() ? $_product->getSpecialFromDate() : $_product->getData()['special_from_date'];
            $special_to_date = $_product->getSpecialToDate() ? $_product->getSpecialToDate() : $_product->getData()['special_to_date'];
            $time = time();
            if($special_from_date && $special_to_date){
                
                if((strtotime($special_from_date) < $time || strtotime($special_from_date) == $time) && ($time < strtotime($special_to_date) || $time == strtotime($special_to_date)))
                {
                    $html .= $this->__('<div>%s - <span style="color: #3d6611;">(Available)</span></div>', $_sku);
                }else{
                    $html .= $this->__('<div>%s - <span style="color: #df280a;">(Expired)<span></div>', $_sku);
                }
            }
            if($special_from_date && !$special_to_date){
                if(strtotime($special_from_date) < $time || strtotime($special_from_date) == $time)
                {
                    $html .= $this->__('<div>%s - <span style="color: #3d6611;">(Available)</span></div>', $_sku);
                }else{
                    $html .= $this->__('<div>%s - <span style="color: #df280a;">(Expired)<span></div>', $_sku);
                }
            }
            
            if(!$special_from_date && $special_to_date){
                if($time < strtotime($special_to_date) || $time == strtotime($special_to_date))
                {
                    $html .= $this->__('<div>%s - <span style="color: #3d6611;">(Available)</span></div>', $_sku);
                }else{
                    $html .= $this->__('<div>%s - <span style="color: #df280a;">(Expired)<span></div>', $_sku);
                }
            }
            if(!$special_from_date && !$special_to_date) {
                $html .= $this->__('<div>%s - <span style="color: #3d6611;">(Available)</span></div>', $_sku);
            }
        }
        //$html .= ;
        return $html;
    }
}
