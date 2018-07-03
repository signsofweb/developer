<?php

/**
 * Class Imaginato_Shipment_Helper_Import
 */
class Imaginato_Shipment_Helper_Import extends Mage_Core_Helper_Abstract
{
    /**
     * @param array $columns
     * @return array
     */
    public function getOrderEntities($columns = array('*'))
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->getSelect()->reset('columns')->columns($columns);
        $collection->getSelect()->where('state IN (?)', array('new', 'pending_payment', 'processing', 'complete', 'holded', 'payment_review'));

        $entities = $collection->getData();

        return $entities;
    }

    /**
     * @param Mage_Sales_Model_Order $salesOrder
     * @return Mage_Sales_Model_Order
     */
    public function setShippedStatus(Mage_Sales_Model_Order $salesOrder)
    {
        return $this->setOrderStatus($salesOrder, Imaginato_Shipment_Model_Import::STATE_SHIPPED);
    }

    /**
     * @param Mage_Sales_Model_Order $salesOrder
     * @param $newStatus
     * @return Mage_Sales_Model_Order
     */
    public function setOrderStatus(Mage_Sales_Model_Order $salesOrder, $newStatus)
    {

        $userNotification = $salesOrder->hasCustomerNoteNotify() ? $salesOrder->getCustomerNoteNotify() : null;
        $salesOrder->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $newStatus, '', $userNotification);

        return $salesOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $salesOrder
     * @return Mage_Sales_Model_Order
     */
    public function setPartialShippedStatus(Mage_Sales_Model_Order $salesOrder)
    {
        return $this->setOrderStatus($salesOrder, Imaginato_Shipment_Model_Import::STATE_PARTIAL_SHIPPED);
    }

    /**
     * @param array $rowData
     * @return mixed|string
     */
    public function getCarrierTitle(array $rowData)
    {
        $carrierCode = $this->getCarrierCode($rowData[Imaginato_Shipment_Model_Import_Entity::COL_CARRIER]);

        if (isset($rowData[Imaginato_Shipment_Model_Import_Entity::COL_TITLE])
            && !empty($rowData[Imaginato_Shipment_Model_Import_Entity::COL_TITLE])
        ) {
            $carrierTitle = $rowData[Imaginato_Shipment_Model_Import_Entity::COL_TITLE];
        } else {
            if ($carrierCode != 'custom') {
                /** @var Mage_Shipping_Model_Config $shipConfig */
                $shipConfig = Mage::getSingleton('shipping/config');
                $carrier = $shipConfig->getCarrierInstance($carrierCode);
                $carrierTitle = $carrier->getConfigData(Imaginato_Shipment_Model_Import_Entity::COL_TITLE);
            } else {
                $carrierTitle = $this->__('Custom Value');
            }
        }

        return $carrierTitle;
    }

    /**
     * @param $rawCode
     * @return int|string
     */
    public function getCarrierCode($rawCode)
    {
        /** @var Imaginato_Shipment_Helper_Data $helper */
        $helper = Mage::helper('imaginato_shipment');
        $carrierCodeToId = $helper->getAvailableCarriers();

        if (isset($carrierCodeToId[$rawCode])) {
            return $rawCode;
        }

        //check if carrier code is a Carrier Title
        foreach ($carrierCodeToId as $code => $data) {
            if ($rawCode == $data[Imaginato_Shipment_Model_Import_Entity::COL_TITLE]) {
                return $code;
                break;
            }
        }

        return '';
    }

    /**
     * @param array $rowData
     * @return string
     */
    public function getHashFromRow(array $rowData)
    {
        if (!isset($rowData[Imaginato_Shipment_Model_Import_Entity::COL_CARRIER])
            || !isset($rowData[Imaginato_Shipment_Model_Import_Entity::COL_TITLE])
            || !isset($rowData[Imaginato_Shipment_Model_Import_Entity::COL_NUMBER])
        ) {
            return null;
        }

        $code = $rowData[Imaginato_Shipment_Model_Import_Entity::COL_CARRIER];
        $title = $rowData[Imaginato_Shipment_Model_Import_Entity::COL_TITLE];
        $number = $rowData[Imaginato_Shipment_Model_Import_Entity::COL_NUMBER];
        return $this->trackingInfoHash($code, $title, $number);
    }

    /**
     * @param $rawCarrierCode
     * @param $carrierTitle
     * @param $carrierNumber
     * @return string
     */
    public function trackingInfoHash($rawCarrierCode, $carrierTitle, $carrierNumber)
    {
        $carrierCode = $this->getCarrierCode($rawCarrierCode);
        return md5($carrierCode . $carrierTitle . $carrierNumber);
    }

    public function addNewShipment(Imaginato_Shipment_Model_Import_Entity $entity, array $rowData, $rowNum, $noticeCode = null)
    {
        $entity->addSavedShipments($rowData, $rowNum);

        $noticeCode = ($noticeCode) ? $noticeCode : $entity::NOTICE_NEW_SHIPMENT;

        //notification for new shipment
        $entity->addNotice($noticeCode, $rowNum);

        return $entity;
    }

    public function addNewTrack(Imaginato_Shipment_Model_Import_Entity $entity, array $rowData, $rowNum, $noticeCode = null)
    {
        $entity->addSavedTracks($rowData, $rowNum);

        $noticeCode = ($noticeCode) ? $noticeCode : $entity::NOTICE_SHIPMENT_NO_TRACKS;

        //notification for order which already has shipments
        //and the shipment has no track, will update tracking info
        $entity->addNotice($noticeCode, $rowNum);

        return $entity;
    }
}