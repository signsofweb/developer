<?php

/**
 * Class Imaginato_Shipment_Model_Observer
 */
class Imaginato_Shipment_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function includeOption(Varien_Event_Observer $observer)
    {
        // Get code of grid
        $idBlockObserver = $observer->getEvent()->getBlock()->getId();

        if ($idBlockObserver == 'sales_order_grid') {

            /** @var Mage_Adminhtml_Block_Sales_Order_Grid $block */
            $block = $observer->getEvent()
                ->getBlock();

            if ($block) {
                $block->addExportType('*/imaginato_shipment/exportPost', Mage::helper('sales')->__('CSV Sample for AWB Import'));
            }
        }
    }
}