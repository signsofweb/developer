<?php

class Imaginato_Bergen_Block_Adminhtml_Order_View_Buttons extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function addButtons()
    {
        $container = $this->getParentBlock();
        if ($container instanceof Mage_Adminhtml_Block_Template && $container->getOrderId()) {
            $order = $container->getOrder();
            if ($order && $order->getData('sync_bergen_status')!=1) {
                $url = Mage::getSingleton('adminhtml/url')
                   ->getUrl('*/*/bergenStatus', array('order_id' => $container->getOrderId()));
                $container->addButton('bergen_status', array(
                    'label' => $this->__('Ready For Bergen'),
                    'onclick' => "setLocation('" . $url . "')",
                ), 0, 45);
            }
        }
        return $this;
    }
}
