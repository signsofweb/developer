<?php

class Imaginato_Reward_Model_Observer extends Enterprise_Reward_Model_Observer
{
    public function orderCompleted($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerIsGuest()
            || !Mage::helper('enterprise_reward')->isEnabled())
        {
            return $this;
        }

        if ($order->getCustomerId() && $this->_isOrderPaidNow($order)) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($order)
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA);

            $staging_model = Mage::getModel('imaginato_reward/reward_staging');
            $staging_model
                ->setWebsiteId($reward->getWebsiteId())
                ->setStoreId($reward->getStore()->getStoreId())
                ->setCustomerId($order->getCustomerId())
                ->setParentId($order->getId())
                ->setIncrementId($order->getIncrementId())
                ->setStatus(0)
                ->setRateData(serialize($reward->getRateToPoints()->getData()))
                ->setPointsDelta((int)$reward->getPointsDelta());
            $staging_model->save();
        }

        return $this;
    }
}
