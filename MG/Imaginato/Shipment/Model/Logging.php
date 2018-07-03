<?php

/**
 * Class Imaginato_Shipment_Model_Logging
 */
class Imaginato_Shipment_Model_Logging
{
    /**
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @param $processor
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchAwbImport($config, $eventModel, $processor)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo(Mage::helper('imaginato_shipment')->__('AWB / Tracking Import'));
    }

    /**
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @param $processor
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchAwbExport($config, $eventModel, $processor)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo(Mage::helper('imaginato_shipment')->__('AWB / Tracking Export CSV Sample'));
    }
}