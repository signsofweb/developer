<?php
/** @var Imaginato_Shipment_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/** @var Mage_Sales_Model_Order_Status $status */
$status = Mage::getModel('sales/order_status');
$status->setStatus('imaginato_partial_shipped')->setLabel('Partial Shipped')
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();

$installer->endSetup();
