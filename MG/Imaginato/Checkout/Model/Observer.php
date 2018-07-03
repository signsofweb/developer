<?php

/**
 * Class Imaginato_Checkout_Model_Observer
 *
 * @property array $descrPerItem
 * @property array $descr
 */
class Imaginato_Checkout_Model_Observer
{
    const TYPE_AMOUNT = 'money_amount';

    /**
     * @param Varien_Event_Observer $observer
     * Process quote item validation and discount calculation
     *
     * @return $this
     */
    public function handleValidation($observer)
    {
        try {
            if (Mage::getStoreConfig('imaginato_checkout/general/breakdown')) {
                $this->process($observer);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), 1, 'imaginato_checkout.log');
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    protected function process(Varien_Event_Observer $observer)
    {
        /** @var Mage_SalesRule_Model_Rule $rule */
        $rule = $observer->getEvent()->getRule();

        /** @var Innoexts_Warehouse_Model_Sales_Quote_Item|Mage_Sales_Model_Quote_Item $item */
        $item = $observer->getEvent()->getItem();

        if (!$item->getId()) {
            return false;
        }

        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $observer->getEvent()->getAddress();

        /** @var Varien_Object $result */
        $result = $observer->getEvent()->getResult();
        $amountToDisplay = $result->getDiscountAmount();
        $baseAmt = null;

        $currentCurr = Mage::app()->getStore()->getCurrentCurrencyCode();
        /** @var Innoexts_WarehouseOneStepCheckout_Model_Sales_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($currentCurr !== $quote->getQuoteCurrencyCode()) {
            $quoteCurr = $quote->getQuoteCurrencyCode();
            $baseCurr = $quote->getBaseCurrencyCode();
            $price = $amountToDisplay;
            //Convert from base to quote currency
            if ($quote->hasFlagFromOrderDetail() || $quote->hasFlagFromOrderAdmin()) {
                $amountToDisplay = Mage::helper('imaginato_checkout')->currencyConvert($price, $baseCurr, $quoteCurr);
            } else {
                //from checkout
                $quoteCurrency = Mage::app()->getStore()->getCurrentCurrency();
                $amountToDisplay = $quoteCurrency->format($price, array('precision' => 2), false);
            }

            //is from Admin BE
            if ($quote->hasFlagFromOrderAdmin()) {
                $baseAmt = $price;
            }
        }

        Mage::unregister('checkout_full_descr_' . $quote->getId());
        if ($amountToDisplay > 0.0001) {
            $this->_addFullDescription($address, $rule, $item, $amountToDisplay, $baseAmt);
            Mage::register('checkout_full_descr_' . $quote->getId(), $this->descr);
        }

        return true;
    }

    /**
     * Adds a detailed description of the discount
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_SalesRule_Model_Rule $rule
     * @param Mage_Sales_Model_Quote_Item $item
     * @param $discount
     * @param array $baseAmt
     * @return $this
     */
    protected function _addFullDescription($address, $rule, $item, $discount, $baseAmt = null)
    {
        // we need this to fix double prices with one step checkouts
        $ind = $rule->getId() . '-' . $item->getId();
        if (isset($this->descrPerItem[$ind])) {
            return $this;
        }
        $this->descrPerItem[$ind] = true;

        $descr = $address->getFullDescr();
        if (!is_array($descr)) {
            $descr = array();
        }

        if (empty($descr[$rule->getId()])) {

            $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
            if (!$ruleLabel) {
                //	UseAutoGeneration
                if ($rule->getUseAutoGeneration() || $rule->getCouponCode()) {
                    $ruleLabel = $rule->getCouponCode();
                }
            }

            if (!$ruleLabel) {
                $ruleLabel = $rule->getName();
            }

            $descr[$rule->getId()] = array('label' => '<strong>' . htmlspecialchars($ruleLabel) . '</strong>', 'amount' => 0);
        }
        // skip the rule as it adds discount to each item
//        $skipTypes = array('cart_fixed', self::TYPE_AMOUNT);

        //code below will list all of items which affected by the discount rule
        //updated : hide items list
//        if (!in_array($rule->getSimpleAction(), $skipTypes) && Mage::getStoreConfig('imaginato_checkout/general/breakdown')) {
//            $sep = ($descr[$rule->getId()]['amount'] > 0) ? ', <br/> ' : ': <br/> ';
//            $descr[$rule->getId()]['label'] = $descr[$rule->getId()]['label'] . $sep . htmlspecialchars($item->getName());
//        }

        $descr[$rule->getId()]['amount'] += $discount;

        if (!is_null($baseAmt)) {
            $descr[$rule->getId()]['baseAmt'] = $baseAmt;
        }

        $address->setFullDescr($descr);
        $this->descr = $descr;

        return $this;
    }

    public function revertQuoteInCrementId($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        if (empty($order->getId())) {
            $quote->setReservedOrderId(null);
        }
        return $this;
    }
}
