<?php

/**
 * Class Imaginato_Shipment_Service_GenerateCSV
 */
class Imaginato_Shipment_Service_GenerateCSV
{
    /**
     * @var array
     */
    private $_orderIds;

    private $_collectionOrders;

    private $_contentCSV;

    public function __construct($ordersId = array())
    {
        $this->_orderIds = $ordersId;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        $this->_loadOrderObjects();

        $templateLine = Mage::helper('imaginato_shipment')->loadTemplate();

        $this->_prepareData($templateLine);

        return $this->_contentCSV;
    }

    private function _loadOrderObjects()
    {
        $this->_collectionOrders = array();

        /** @var Innoexts_Warehouse_Model_Sales_Order $salesOrderModel */
        $salesOrderModel = Mage::getModel('sales/order');

        if (!empty($this->_orderIds)) {
            foreach ($this->_orderIds as $id) {
                $instance = $salesOrderModel->load($id);
                array_push($this->_collectionOrders, $instance);
            }
        } else {
            /** @var Mage_Sales_Model_Resource_Order_Collection $_ordersCol */
            $_ordersCol = $salesOrderModel->getCollection();
            $_ordersCol->addAttributeToSelect('*')
                ->addFieldToFilter('status', 'processing')
                ->setOrder('created_at', 'desc')
                ->getSelect()
                ->limit(10);

            /** @var Innoexts_Warehouse_Model_Sales_Order $order */
            foreach ($_ordersCol as $order) {
                array_push($this->_collectionOrders, $order);
            }
        }
    }

    /**
     * @param array $templateLine
     */
    private function _prepareData($templateLine)
    {
        if (empty($templateLine) || !is_array($templateLine) || empty($this->_collectionOrders)) {
            return;
        }

        $this->_contentCSV = '';
        $carrierCode = Mage::helper('imaginato_shipment')->getAvailableCarriers();

        //remove everything before '.' (dot) char for header
        $headerLine = Mage::helper('imaginato_shipment')->getCleanTemplateLine();

        //set Header
        $this->_contentCSV .= implode(',', $headerLine) . "\n";

        //iterate on the orders selected
        foreach ($this->_collectionOrders as $order) {
            $carrier = array_rand($carrierCode);
            $lineItem = '';

            $lastEl = end($templateLine);
            // iterate on the items in template
            foreach ($templateLine as $index => $t) {

                $item = '';
                list($object, $attribute) = explode('.', $t);

                switch ($object) {

                    case 'order':

                        $item = $order->getData($attribute);
                        break;

                    case 'customer':

                        if ($attribute == 'name') {
                            $item = $order->getData('customer_firstname') . ' ' .
                                $order->getData('customer_lastname');
                        } else {
                            $item = $order->getData("customer_{$attribute}");
                        }

                        break;

                    case 'address':

                        $address = $order->getShippingAddress();

                        if (strpos($attribute, 'street_') !== false) {
                            $street = explode('_', $attribute);
                            $item = $address->getStreet($street[1]);
                        } else {
                            $item = $address->getData($attribute);
                        }

                        break;

                    case 'shipment':
                        if ($attribute == 'sku') {
                            /** @var Mage_Sales_Model_Resource_Order_Item_Collection $itemColl */
                            $itemColl = $order->getItemsCollection();
                            $firstItem = $itemColl->getFirstItem();
                            $item = $firstItem->getSku();
                        } else if ($attribute == 'carrier') {
                            $item = $carrier;
                        } else if ($attribute == 'title') {
                            $carrier = $carrierCode[$carrier];
                            $item = (isset($carrier['title'])) ? $carrier['title'] : $randKey;
                        } else if ($attribute == 'number') {
                            $digits = 3;
                            $item = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                        } else if ($attribute == 'qty') {
                            $item = 1;
                        }
                        break;
                }

                if ($t == $lastEl) {
                    $lineItem .= "{$item}";
                } else {
                    $lineItem .= "{$item},";
                }
            }

            // endline
            $this->_contentCSV .= $lineItem . "\n";
        }
    }

}