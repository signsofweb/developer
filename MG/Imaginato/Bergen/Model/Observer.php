<?php

class Imaginato_Bergen_Model_Observer
{

    public function addMassAction(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract)
            && $block->getRequest()->getControllerName() == 'sales_order'
        ) {
            $block->addItem('bergen_status', array(
                'label' => 'Ready For Bergen',
                'url' => Mage::app()->getStore()->getUrl('adminhtml/sales_order/massBergenStatus'),
            ));
        }
    }
}
