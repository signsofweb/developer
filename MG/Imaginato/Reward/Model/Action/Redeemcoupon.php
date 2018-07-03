<?php

class Imaginato_Reward_Model_Action_Redeemcoupon extends Enterprise_Reward_Model_Action_Abstract
{

    /**
     * Return action message for history log
     *
     * @param array $args Additional history data
     * @return string
     */
    public function getHistoryMessage($args = array())
    {
        $ruleId = isset($args['rule_id']) ? $args['rule_id'] : '';
        $ruleData = Mage::getModel('salesrule/rule')->load($ruleId);

        return Mage::helper('enterprise_reward')->__('Used points for redeem coupon(%s).', $ruleData->getName());
    }

    /**
     * Setter for $_entity and add some extra data to history
     *
     * @param Varien_Object $entity
     * @return Enterprise_Reward_Model_Action_Abstract
     */
    public function setEntity($entity)
    {
        parent::setEntity($entity);
        $this->getHistory()->addAdditionalData(array(
            'rule_id' => $this->getEntity()->getCoupon()
        ));
        return $this;
    }

    /**
     * Retrieve points delta for action
     *
     * @param int $websiteId
     * @return int
     */
    public function getPoints($websiteId)
    {
        $pointsDelta = -$this->getEntity()->getPoints();
        return $pointsDelta;
    }

    public function canAddRewardPoints()
    {
        $pointsBalance = $this->getReward()->loadByCustomer()->getPointsBalance();
        if($this->getEntity()->getPoints() > $pointsBalance){
            return false;
        }
        return true;
    }
}
