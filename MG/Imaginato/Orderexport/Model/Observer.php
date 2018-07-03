<?php

class Imaginato_Orderexport_Model_Observer
{
    public function resave_lineitem_order($observer)
    {
        $order = $observer->getEvent()->getOrder();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getId());
    }
    public function resave_lineitem_payment($observer)
    {
        $order = $observer->getEvent()->getPayment();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getParentId());
    }
    public function resave_lineitem_shipment($observer)
    {
        $order = $observer->getEvent()->getShipment();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getOrderId());
    }
    public function resave_lineitem_item($observer)
    {
        $order = $observer->getEvent()->getItem();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getOrderId());
    }
    public function resave_lineitem_address($observer)
    {
        $order = $observer->getEvent()->getAddress();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getParentId());
    }
    public function resave_lineitem_history($observer)
    {
        $order = $observer->getEvent()->getStatusHistory();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getParentId());
    }
    public function resave_lineitem_invoice($observer)
    {
        $order = $observer->getEvent()->getInvoice();
        Mage::getModel('imaginato_orderexport/lineitem')->resave($order->getOrderId());
    }
}
