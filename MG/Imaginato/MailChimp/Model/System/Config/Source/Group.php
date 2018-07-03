<?php
class Imaginato_MailChimp_Model_System_Config_Source_Group
{
    protected $_groups = null;

    public function __construct()
    {
        $scopeArray = explode('-', Mage::helper('mailchimp')->getScopeString());
        if ($this->_groups == null) {
            $apiKey = Mage::helper('mailchimp')->getApiKey($scopeArray[1], $scopeArray[0]);
            if ($apiKey) {
                try {
                    $api = Mage::helper('mailchimp')->getApi($scopeArray[1], $scopeArray[0]);
                    $listId = Mage::helper('mailchimp')->getGeneralList($scopeArray[1], $scopeArray[0]);
                    $this->_groups = $api->lists->interestCategory->getAll($listId);
                    if (isset($this->_groups['categories']) && count($this->_groups['categories']) == 0) {
                        $apiKeyArray = explode('-', $apiKey);
                        $anchorUrl = 'https://' . $apiKeyArray[1] . '.admin.mailchimp.com/dashboard/groups/';
                        $htmlAnchor = '<a target="_blank" href="' . $anchorUrl . '">' . $anchorUrl . '</a>';
                        $message = 'Please create a group at '. $htmlAnchor;
                        Mage::getSingleton('adminhtml/session')->addWarning($message);
                    }
                } catch(MailChimp_Error $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getFriendlyMessage());
                }
            }
        }
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups = array();

        if (is_array($this->_groups)) {
            $groups[] = array('value' => '', 'label' => Mage::helper('mailchimp')->__('--- Select a group ---'));
            foreach ($this->_groups['categories'] as $group) {
                $groups [] = array('value' => $group['id'], 'label' => $group['title']);
            }
        } else {
            $groups [] = array('value' => '', 'label' => Mage::helper('mailchimp')->__('--- No data ---'));
        }

        return $groups;
    }

}
