<?php

class Imaginato_Reward_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param   Mage_SalesRule_Model_Rule $rule
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    protected function _canProcessRule($rule, $address)
    {
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }
        /**
         * check relevant customer
         */
        if ($rule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC) {
            $couponCode = $address->getQuote()->getCouponCode();
            $customerId = $address->getQuote()->getCustomerId();
            if (strlen($couponCode)) {
                $coupon = Mage::getModel('salesrule/coupon');
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    $rewardCoupon = Mage::getModel('imaginato_reward/reward_coupon_history')->load($coupon->getId(),'coupon_id');
                    if($rewardCoupon->getId() && $rewardCoupon->getCustomerId()!=$customerId){
                        return false;
                    }
                }
            }
        }

        /**
         * check per coupon usage limit
         */
        if ($rule->getCouponType() != Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if (strlen($couponCode)) {
                $coupon = Mage::getModel('salesrule/coupon');
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    // check entire usage limit
                    if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                        $rule->setIsValidForAddress($address, false);
                        return false;
                    }
                    // check per customer usage limit
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = new Varien_Object();
                        Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
                            $couponUsage, $customerId, $coupon->getId());
                        if ($couponUsage->getCouponId() &&
                            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                        ) {
                            $rule->setIsValidForAddress($address, false);
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId     = $address->getQuote()->getCustomerId();
            $ruleCustomer   = Mage::getModel('salesrule/rule_customer');
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        $rule->afterLoad();
        /**
         * quote does not meet rule's conditions
         */
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        return true;
    }
}
