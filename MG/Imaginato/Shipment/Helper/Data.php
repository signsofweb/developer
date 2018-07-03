<?php

/**
 * Class Imaginato_Shipment_Helper_Data
 */
class Imaginato_Shipment_Helper_Data extends Mage_ImportExport_Helper_Data
{
    const DEFAULT_TEMPLATE_LINE = 'order.increment_id|shipment.carrier|shipment.title|shipment.number|shipment.sku|shipment.qty';
    const IMPORT_ENTITY         = 'order';

    /**
     * remove everything before '.' (dot) char for header
     *
     * @return array
     */
    public static function getCleanTemplateLine()
    {
        $headerLine = array_map(function ($item) {
            $trimmed = trim(strstr($item, '.'), '.');
            if ($trimmed == 'increment_id') {
                $trimmed = 'Order Number';
            }
            return $trimmed;
        }, self::loadTemplate());

        return $headerLine;
    }

    /**
     * Get array of items in template line
     *
     * @return array
     */
    public static function loadTemplate()
    {
        $contentTemplate = self::DEFAULT_TEMPLATE_LINE;

        $templateLine = explode("|", $contentTemplate);

        return $templateLine;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return self::IMPORT_ENTITY;
    }

    /**
     * @return string
     */
    public function getEntityAdapter()
    {
        return 'imaginato_shipment/import_entity';
    }

    /**
     * @param bool $asVarien
     * @return array
     */
    public function getAvailableCarriers($asVarien = false)
    {
        /**
         * get all active carriers
         */
        $carriersData = array();
        /** @var Mage_Shipping_Model_Config $shipping_config */
        $shipping_config = Mage::getSingleton('shipping/config');
        $carriers = $shipping_config->getAllCarriers();

        /** @var Mage_Shipping_Model_Carrier_Abstract|Mage_Shipping_Model_Carrier_Interface $method */
        foreach ($carriers as $code => $method) {
            $carrierTitle = Mage::getStoreConfig("carriers/$code/title");
            if (!$carrierTitle || empty($carrierTitle) || !$method->isTrackingAvailable()) {
                continue;
            }

            $data = array(
                'code'    => $code,
                'title'   => $this->getCarrierTitle($code),
                'methods' => $method->getAllowedMethods(),
            );

            $carriersData[$code] = $asVarien ? new Varien_Object($data) : $data;
        }

        //Add "Custom Value"
        $customData = array('code' => 'custom', 'title' => 'Custom Value');
        $carriersData['custom'] = $asVarien ? new Varien_Object($customData) : $customData;

        return $carriersData;
    }

    /**
     * Get Carrier Title
     *
     * @param string $code
     * @return string
     */
    public function getCarrierTitle($code)
    {
        /** @var Mage_Shipping_Model_Config $config */
        $config = Mage::getSingleton('shipping/config');

        /** @var Mage_Usa_Model_Shipping_Carrier_Dhl $carrier */
        $carrier = $config->getCarrierInstance($code);

        return $carrier ? $carrier->getConfigData('title') : $this->__('Custom Value');
    }
}