<?php

class Imaginato_Reward_Block_Adminhtml_Reward_Rate_Edit_Form_Renderer_Rate
    extends Enterprise_Reward_Block_Adminhtml_Reward_Rate_Edit_Form_Renderer_Rate
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->setTemplate('imaginato/reward/rate/form/renderer/rate.phtml');
    }

    public function isCoupon(){
        return $this->getRate()->getDirection() == Imaginato_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_COUPON;
    }

    public function getRules(){
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('coupon_type',Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('is_active','1')
            ->addFieldToFilter('use_auto_generation','1');

        $now = Mage::getModel('core/date')->date('Y-m-d');
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('rule_id','name'))
            ->where('from_date is null or from_date <= ?', $now)
            ->where('to_date is null or to_date >= ?', $now);
        return $collection->getItems();
    }

    public function getCouponDirection(){
        return Imaginato_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_COUPON;
    }
}
