<?php
class Imaginato_Orderexport_Model_System_Config_Backend_Ratefield extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            if (is_object($this->getValue())) {
                $serializedValue = $this->getValue()->asArray();
            } else {
                $serializedValue = $this->getValue();
            }

            $unserializedValue = false;
            if (!empty($serializedValue)) {
                try {
                    $unserializedValue = unserialize($serializedValue);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            $this->setValue($unserializedValue);
        }
    }
}