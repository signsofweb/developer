<?php

class Imaginato_Reward_Model_Reward_Staging extends Mage_Core_Model_Abstract
{
    const STAGING_STATUS_PRESSING  = 0;
    const STAGING_STATUS_AGREE     = 1;
    const STAGING_STATUS_REFUSE    = 2;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_staging');
    }

    protected function _beforeSave()
    {
        if ($this->isObjectNew()) {
            $this->addData(array(
                'created_at' => $this->getResource()->formatDate(time())
            ));
        }

        return parent::_beforeSave();
    }

    public function getStatusOptionArray()
    {
        return array(
            self::STAGING_STATUS_PRESSING => 'Pending',
            self::STAGING_STATUS_AGREE    => 'Agree',
            self::STAGING_STATUS_REFUSE   => 'Refuse'
        );
    }

    public function isAllowDetail(){
        return $this->getData('status') == self::STAGING_STATUS_PRESSING;
    }

    public function agreeReward(){
        if(!$this->getId() || !$this->getParentId()){
            return;
        }

        $rate = Mage::getModel('enterprise_reward/reward_rate');
        $rate->addData(unserialize($this->getData('rate_data')));

        $order = Mage::getModel('sales/order')->load($this->getParentId());
        $reward = Mage::getModel('enterprise_reward/reward')
            ->setActionEntity($order)
            ->setCustomerId($order->getCustomerId())
            ->setWebsiteId($order->getStore()->getWebsiteId())
            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA)
            ->setOldRates($rate)
            ->updateRewardPoints();
        if ($reward->getRewardPointsUpdated() && $reward->getPointsDelta()) {
            $order->addStatusHistoryComment(
                Mage::helper('enterprise_reward')->__('Customer earned %s for the order.', Mage::helper('enterprise_reward')->formatReward($reward->getPointsDelta()))
            )->save();
        }

        $this->setStatus(self::STAGING_STATUS_AGREE);
        $this->save();

    }

    public function refuseReward(){
        if(!$this->getId()){
            return;
        }
        $this->setStatus(self::STAGING_STATUS_REFUSE);
        $this->save();
    }
}
