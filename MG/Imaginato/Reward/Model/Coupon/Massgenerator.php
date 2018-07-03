<?php

class Imaginato_Reward_Model_Coupon_Massgenerator extends Mage_SalesRule_Model_Coupon_Massgenerator
{

    /**
     * Generate Coupons Pool
     *
     * @return Mage_SalesRule_Model_Coupon_Massgenerator
     */
    public function generatePool()
    {
        $size = 1;

        $maxProbability = self::MAX_PROBABILITY_OF_GUESSING;
        $maxAttempts = self::MAX_GENERATE_ATTEMPTS;

        /** @var $coupon Mage_SalesRule_Model_Coupon */
        $coupon = Mage::getModel('salesrule/coupon');

        $chars = count(Mage::helper('salesrule/coupon')->getCharset($this->getFormat()));
        $length = (int) $this->getLength();
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;
        //increase the length of Code if probability is low
        if ($probability > $maxProbability) {
            do {
                $length++;
                $maxCodes = pow($chars, $length);
                $probability = $size / $maxCodes;
            } while ($probability > $maxProbability);
            $this->setLength($length);
        }

        $now = $this->getResource()->formatDate(
            Mage::getSingleton('core/date')->gmtTimestamp()
        );

        $attempt = 0;
        do {
            if ($attempt >= $maxAttempts) {
                Mage::throwException(Mage::helper('salesrule')->__('Unable to create requested Coupon Qty. Please check settings and try again.'));
            }
            $code = $this->generateCode();
            $attempt++;
        } while ($this->getResource()->exists($code));

        $expirationDate = $this->getToDate();
        if ($expirationDate instanceof Zend_Date) {
            $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        $coupon->setId(null)
            ->setRuleId($this->getRuleId())
            ->setUsageLimit($this->getUsesPerCoupon())
            ->setUsagePerCustomer($this->getUsesPerCustomer())
            ->setExpirationDate($expirationDate)
            ->setCreatedAt($now)
            ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
            ->setCode($code)
            ->save();
        return $coupon;
    }
}