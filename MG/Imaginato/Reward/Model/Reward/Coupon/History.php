<?php

class Imaginato_Reward_Model_Reward_Coupon_History extends Mage_Core_Model_Abstract
{
    public $_redeem_flg = false;

    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_coupon_history');
    }

    public function redeemCoupon($customerId,$rate_Id)
    {
        if(empty($customerId) || empty($rate_Id)){
            return;
        }
        $rate = Mage::getModel('enterprise_reward/reward_rate')->load($rate_Id);
        if(empty($rate->getData('coupon'))){
            return;
        }
        $rule = Mage::getModel('salesrule/rule')->load($rate->getData('coupon'));
        if(empty($rule->getId())){
            return;
        }


        $reward = Mage::getModel('enterprise_reward/reward')
            ->setActionEntity($rate)
            ->setCustomerId($customerId)
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->setAction(Imaginato_Reward_Model_Reward::REWARD_ACTION_REDEEMCOUPON)
            ->updateRewardPoints();
        if (!$reward->getRewardPointsUpdated() || !$reward->getPointsDelta()) {
            return;
        }

        $data = array(
            'rule_id'=>$rule->getId(),
            'length'=>'12',
            'format'=>'alphanum',
            'users_per_coupon'=>'1',
            'users_per_customer'=>'1'
        );
        if($rule->getData('to_data')){
            $data['to_data'] = $rule->getData('to_data');
        }

        $generator = Mage::getModel('imaginato_reward/coupon_massgenerator');
        $generator->setData($data);
        $coupon = $generator->generatePool();
        $now = $this->getResource()->formatDate(
            Mage::getSingleton('core/date')->gmtTimestamp()
        );
        if(!$coupon->getId()){
            return;
        }
        $this->setId(null)
            ->setData('coupon_id',$coupon->getId())
            ->setData('rule_id',$coupon->getRuleId())
            ->setData('customer_id',$customerId)
            ->setData('points',$rate->getData('points'))
            ->setData('created_at',$now)
            ->save();
        $this->_redeem_flg = true;
    }

}
