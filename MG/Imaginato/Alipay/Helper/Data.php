<?php

class Imaginato_Alipay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Checks for ability to alipay refund
     *
     * @param  int|Mage_Sales_Model_Order $order
     * @param  bool $forceCreate - set yes when you don't need to check config setting (for admin side)
     * @return bool
     */
    public function canRefund($order)
    {
        if($order->getStatus()!='alipay_trade_finished' && $order->getStatus()!='alipay_trade_success' && $order->getStatus()!=Mage_Sales_Model_Order::STATE_PROCESSING){
            return false;
        }
        $payment = $order->getPayment();
        if($payment->getMethod()!='alipay_payment'){
            return false;
        }
        $payment = Mage::getModel('imaginato_alipay/payment');
        return $payment->trade_query($order);
    }

}
