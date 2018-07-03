<?php

/**
 * Class Imaginato_Shipment_Block_Adminhtml_Importexport
 */
class Imaginato_Shipment_Block_Adminhtml_Importexport extends Mage_Adminhtml_Block_Widget
{
    /**
     * Initialize factory instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('imaginato/shipment/importexport.phtml');
    }

    public function getTitle()
    {
        if ($this->getIsReadOnly()) {
            return Mage::helper('imaginato_shipment')->__('Export AWB');
        } else {
            return Mage::helper('imaginato_shipment')->__('Import / Export AWB');
        }
    }

    public function getIsReadOnly()
    {
        return (strpos($this->getNameInLayout(), strtolower($this->getModuleName())) !== false);
    }

    public function showCarrierCodeHints()
    {
        return true;
    }

}
