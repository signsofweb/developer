<?php

class Imaginato_Alipay_Adminhtml_Imaginato_AlipayController extends Mage_Adminhtml_Controller_Action
{
    private $_order;
    /**
     * Init active menu and set breadcrumb
     *
     * @return Imaginato_Alipay_Adminhtml_Imaginato_AlipayController
     */
    protected function _initAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $this->_getSession()->addError($this->__('Please specify order id to be refund.'));
            $this->_redirect('*/sales_order');
        } else {
            $order = Mage::getModel('sales/order')->load($orderId);
            $payment = $order->getPayment();
            if(!$order->getId()){
                $this->_getSession()->addError(
                    Mage::helper('imaginato_alipay')->__('This is a invalid order number')
                );
                $this->_redirect('*/sales_order');
                return;
            }
            if (!Mage::helper('imaginato_alipay')->canRefund($order, true)) {
                $this->_getSession()->addError(
                    Mage::helper('imaginato_alipay')->__('This order is not allowed to be refunded')
                );
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
                return;
            }
            $this->_order = $order;
        }
    }

    /**
     * Alipay refund
     */
    public function refundAction()
    {
        $this->_initAction();
        if(empty($this->_order)){
            return;
        }
        Mage::register('current_order', $this->_order);
        $this->loadLayout()->_setActiveMenu('imaginato/alipay');
        $this->_title($this->__('Alipay Refund'));
        $this->renderLayout();
    }

    public function saveAction(){
        $this->_initAction();
        if(empty($this->_order)){
            return;
        }
        $refund = $this->getRequest()->getParam('refund');
        if(!$refund['total'] && $refund['total']<=0){
            $this->_getSession()->addError(
                Mage::helper('imaginato_alipay')->__('This order is not allowed to be refunded')
            );
        }
        if(Mage::getModel('imaginato_alipay/payment')->forex_refund($this->_order,$refund['total'],$refund['comment_text'])){
            $this->_order->addStatusToHistory(
                'alipay_refund_success',
                Mage::helper('alipay')->__('REFUND SUCCESS'));
            try{
                $this->_order->save();
            } catch(Exception $e){
                $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            }
            $this->_getSession()->addSuccess(Mage::helper('imaginato_alipay')->__('The order is return.'));
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $this->_order->getId()));
    }
}
