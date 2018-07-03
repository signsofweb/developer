<?php

class Imaginato_Bergen_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Change Bergen Status order
     */
    public function bergenStatusAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->setData('sync_bergen_status',1)->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been ready for bergen.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order was not ready for bergen.'));
            }
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Change Bergen Status selected orders
     */
    public function massBergenStatusAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countUnbergenStatus = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order && $order->getData('sync_bergen_status')!=1) {
                $order->setData('sync_bergen_status',1)->save();
            }
            $countUnbergenStatus++;
        }
        if ($countUnbergenStatus) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been ready for bergen.', $countUnbergenStatus));
        }
        $this->_redirect('*/*/');
    }
}
