<?php

class Imaginato_Reward_Model_Reward extends Enterprise_Reward_Model_Reward
{
    const REWARD_ACTION_REDEEMCOUPON    = 12;

    protected function _construct()
    {
        self::$_actionModelClasses = array(
            self::REWARD_ACTION_REDEEMCOUPON        => 'imaginato_reward/action_redeemcoupon'
        );
        parent::_construct();
    }

    public function setOldRates($rate){
        if ($rate->getCurrencyAmount() && $rate->getCurrencyAmount()) {
            $this->_rates[Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS] = $rate;
        };
        return $this;
    }
}
