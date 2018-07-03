<?php

/**
 * Class Imaginato_Contacts_Model_Config_Source_Subject
 */
class Imaginato_Contacts_Model_Config_Source_Subject extends Varien_Object
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
		$options = [];
		$enqueries = $this->getEnqueries();
		foreach($enqueries as $_enquerytype) {
			$_options = [];
			foreach($_enquerytype['enqueries'] as $enquery){
				$_options[] = ['label' => $enquery['title'],'value' => $enquery['id']];
			}
			$options[] = ['label' => $_enquerytype['enquerytype']['title'],'value' => $_options];
		}
        if ($withEmpty) {
            array_unshift($options, ['label' => '','value' => [
						[
							'label' => 'Please select subject',
							'value' => ''
						]
					]]
            );
        }
        return $options;
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
	
	protected function getEnqueries()
    {
        $enqueryModel = Mage::getModel('imaginato_contacts/enqueries')->getCollection();

        $enqueries = $enqueryModel->addEnquerytypeFilter();

        $returnval = array();

        foreach($enqueries as $enquery)
        {
            if(!isset($returnval[$enquery->getEnquerytypeId()]['enquerytype']))
            {
                $returnval[$enquery->getEnquerytypeId()]['enquerytype'] = array(
                    'title' => $enquery->getEnquerytypeTitle(), 
                    'short_order' => $enquery->getEnquerytypeShortOrder(),
                );
            }
            $returnval[$enquery->getEnquerytypeId()]['enqueries'][] = array(
                'id' => $enquery->getId(),
                'title' => $enquery->getTitle(),
                'short_order' => $enquery->getShortOrder(),
                'email' => $enquery->getEmail(),
            );
        }
        return $returnval;
    }
}
