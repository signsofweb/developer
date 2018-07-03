<?php

class Imaginato_Reward_Model_Cron
{

    public function process()
    {
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $store_id = $store->getId();
            $confirm_days = Mage::getStoreConfig('enterprise_reward/points/confirm_days',$store_id);
            if(empty($confirm_days)){
                continue;
            }
            $staging_model = Mage::getModel('imaginato_reward/reward_staging');
            $confirm_time = $staging_model->getResource()->formatDate(time()-(86400*$confirm_days));
            $staging_data = $staging_model->getCollection()
                ->addFieldToFilter('status',Imaginato_Reward_Model_Reward_Staging::STAGING_STATUS_PRESSING)
                ->addFieldToFilter('store_id',$store_id)
                ->addFieldToFilter('created_at',array('to'=>$confirm_time));
            foreach ($staging_data->getItems() as $staging) {
                $staging->agreeReward();
            }
        }
        }
}
