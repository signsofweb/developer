<?php
class Imaginato_Orderexport_Sales_Order_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Exports orders defined by id in post param "order_ids" to csv and offers file directly for download
     * when finished.
     */
    public function csvchinaexportAction()
    {
    	$orders = $this->getRequest()->getPost('order_ids', array());
        $file = Mage::getModel('imaginato_orderexport/export_chinaexport')->exportOrders($orders);
        $this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export').'/'.$file));
    }
}
?>