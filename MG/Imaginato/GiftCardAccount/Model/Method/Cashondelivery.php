<?php

class Imaginato_GiftCardAccount_Model_Method_Cashondelivery extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = 'cashondelivery';

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'payment/form_cashondelivery';
    protected $_infoBlockType = 'payment/info';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    public function isApplicableToQuote($quote, $checksBitMask)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'giftcard') {
                return false;
            }
        }
        return parent::isApplicableToQuote($quote, $checksBitMask);
    }

}
