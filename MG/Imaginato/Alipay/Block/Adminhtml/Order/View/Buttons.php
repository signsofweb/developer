<?php

class Imaginato_Alipay_Block_Adminhtml_Order_View_Buttons extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function addButtons()
    {
        $container = $this->getParentBlock();
        if ($container instanceof Mage_Adminhtml_Block_Template && $container->getOrderId()) {
            if(!$this->_isAllowedAction('alipay_refund')){
                return $this;
            }
            $isRefundable = Mage::helper('imaginato_alipay')->canRefund($container->getOrder());
            if ($isRefundable) {
                $url = Mage::getSingleton('adminhtml/url')
                   ->getUrl('*/imaginato_alipay/refund', array('order_id' => $container->getOrderId()));
                $order = 41;
                $container->addButton('alipay_refund', array(
                    'label' => Mage::helper('imaginato_alipay')->__('Alipay refund'),
                    'onclick' => "setLocation('" . $url . "')",
                ), 0, $order);
            }
        }
        return $this;
    }
}
