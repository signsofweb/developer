<?php
 
class Imaginato_Orderexport_Model_Initoptionaction
{
	public function addMassAction($observer) {
   	$block = $observer->getEvent()->getBlock();
   	if(($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction)
		&& strstr( $block->getRequest()->getControllerName(), 'sales_order'))
		{ 
      $block->addItem('orderchinaexport', array(
        'label'=> Mage::helper('sales')->__('China order export'),
        'url'  => Mage::getModel('adminhtml/url')->getUrl('*/sales_order_export/csvchinaexport'),
          ));   
    } 
  }

}