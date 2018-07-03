<?php

class Imaginato_Reward_Model_Resource_Reward_Coupon_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_coupon_history');
    }
}
