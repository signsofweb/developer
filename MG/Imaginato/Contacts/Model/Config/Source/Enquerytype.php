<?php

/**
 * Class Imaginato_Contacts_Model_Config_Source_Enquerytype
 */
class Imaginato_Contacts_Model_Config_Source_Enquerytype extends Varien_Object
{
    protected $_options;

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->toOptionArray();
        }
        return $this->_options;
    }

    public function toOptionArray($withEmpty = false)
    {
        return $this->getEnquerytype();
    }

    public function getOptions()
    {
        $options = array();
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
	
	/**
     * @return Imaginato_Contacts_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function _getHelper()
    {
        return Mage::helper('imaginato_contacts');
    }
	
	protected function getEnquerytype() {
		$enquerytypes = Mage::getModel('imaginato_contacts/enquerytype')->getCollection();
		$returnval = array();
		foreach($enquerytypes as $enquerytype) {
		  $returnval[] = array('value'=>$enquerytype->getId(), 'label'=>$enquerytype->getTitle());
		}
		return $returnval;
	  }
}
