<?php

class Imaginato_Reward_Model_Resource_Reward_Coupon_History extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_coupon_history', 'history_id');
    }
}
