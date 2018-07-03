<?php

class Imaginato_DataCash_Model_Observer
{

    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        $input = $observer->getEvent()->getInput();
        /* @var $quote Mage_Sales_Model_Quote */
        $payment = $observer->getEvent()->getPayment();
        if (Mage::app()->getRequest()->getControllerName() == 'adminhtml_sales_order_create'
            && Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId()
            && $input->getMethod() == 'datacash_api'
            && $payment->getMethod() == 'datacash_api') {

            $input
                ->setCcType($payment->getCcType())
                ->setCcOwner($payment->getCcOwner())
                ->setCcNumber($payment->getCcLast4())
                ->setCcExpMonth($payment->getCcExpMonth())
                ->setCcExpYear($payment->getCcExpYear())
                ->setCcSsIssue($payment->getCcSsIssue())
                ->setCcSsStartMonth($payment->getCcSsStartMonth())
                ->setCcSsStartYear($payment->getCcSsStartYear());
        }

        return $this;
    }
}
