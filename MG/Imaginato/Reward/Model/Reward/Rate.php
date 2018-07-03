<?php

class Imaginato_Reward_Model_Reward_Rate extends Enterprise_Reward_Model_Reward_Rate
{
    const RATE_EXCHANGE_DIRECTION_TO_CURRENCY = 1;
    const RATE_EXCHANGE_DIRECTION_TO_POINTS   = 2;
    const RATE_EXCHANGE_DIRECTION_TO_COUPON   = 3;

    /**
     * Rate text getter
     *
     * @param int $direction
     * @param int $points
     * @param float $amount
     * @param string $currencyCode
     * @return string|null
     */
    public static function getRewardRateText($row, $currencyCode = null)
    {
        switch ($row->getDirection()) {
            case self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY:
                return Mage::helper('enterprise_reward')->formatRateToCurrency($row->getPoints(), $row->getCurrencyAmount(), $currencyCode);
            case self::RATE_EXCHANGE_DIRECTION_TO_POINTS:
                return Mage::helper('enterprise_reward')->formatRateToPoints($row->getPoints(), $row->getCurrencyAmount(), $currencyCode);
            case self::RATE_EXCHANGE_DIRECTION_TO_COUPON:
                $conpon = Mage::getModel('salesrule/rule')->load($row->getCoupon());
                return sprintf('%1$s points = coupon(%2$s) ', $row->getPoints(), $conpon->getName());
        }
    }

    /**
     * Prepare values in order to defined direction
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    protected function _prepareRateValues()
    {
        if ($this->_getData('direction') == self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY) {
            $this->setData('points', (int)$this->_getData('value'));
            $this->setData('currency_amount', (float)$this->_getData('equal_value'));
        } elseif ($this->_getData('direction') == self::RATE_EXCHANGE_DIRECTION_TO_POINTS) {
            $this->setData('currency_amount', (float)$this->_getData('value'));
            $this->setData('points', (int)$this->_getData('equal_value'));
        } elseif ($this->_getData('direction') == self::RATE_EXCHANGE_DIRECTION_TO_COUPON) {
            $this->setData('points', (float)$this->_getData('value'));
            $this->setData('coupon', (int)$this->_getData('equal_value'));
        }
        return $this;
    }

    /**
     * Retrieve option array of rate directions with labels
     *
     * @return array
     */
    public function getDirectionsOptionArray()
    {
        $optArray = array(
            self::RATE_EXCHANGE_DIRECTION_TO_CURRENCY => Mage::helper('enterprise_reward')->__('Points to Currency'),
            self::RATE_EXCHANGE_DIRECTION_TO_POINTS => Mage::helper('enterprise_reward')->__('Currency to Points'),
            self::RATE_EXCHANGE_DIRECTION_TO_COUPON => Mage::helper('enterprise_reward')->__('Points to Coupon')
        );
        return $optArray;
    }

    /**
     * Check if given rate data (website, customer group, direction)
     * is unique to current (already loaded) rate
     *
     * @param integer $websiteId
     * @param integer $customerGroupId
     * @param integer $direction
     * @return boolean
     */
    public function getIsRateUnique($websiteId, $customerGroupId, $direction, $conpon)
    {
        $data = $this->_getResource()->get_rate_data($websiteId, $customerGroupId, $direction, $conpon);
        if ($data && $data['rate_id'] != $this->getId()) {
            return false;
        }
        return true;
    }
}
