<?php

class Imaginato_Reward_Model_Resource_Reward_Staging extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_staging', 'staging_id');
    }
}
