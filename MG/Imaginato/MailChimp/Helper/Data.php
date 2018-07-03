<?php

class Imaginato_MailChimp_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $_cycle_group;
    public $_content_group;

    /**
     * Get Config value for certain scope.
     * 
     * @param  $path
     * @param  $scopeId
     * @param  null    $scope
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getConfigValueForScope($path, $scopeId, $scope = null)
    {
        if ($scope == 'websites') {
            $configValue = Mage::app()->getWebsite($scopeId)->getConfig($path);
        } else {
            $configValue = Mage::getStoreConfig($path, $scopeId);
        }
        return $configValue;
    }

    public function getGroup($scopeId, $scope = null)
    {
        return $this->getConfigValueForScope(Imaginato_MailChimp_Model_Config::GENERAL_GROUP, $scopeId, $scope);
    }

    public function getGroupField($scopeId, $scope = null)
    {
        return unserialize($this->getConfigValueForScope(Imaginato_MailChimp_Model_Config::GENERAL_GROUP_FIELDS, $scopeId, $scope));
    }

    public function getContent($scopeId, $scope = null)
    {
        return $this->getConfigValueForScope(Imaginato_MailChimp_Model_Config::GENERAL_CONTENT, $scopeId, $scope);
    }

    public function getContentField($scopeId, $scope = null)
    {
        return unserialize($this->getConfigValueForScope(Imaginato_MailChimp_Model_Config::GENERAL_CONTENT_FIELDS, $scopeId, $scope));
    }

    public function _getCycleGroupOptions(){
        if(!$this->_cycle_group){
            $options = array();
            $stores = Mage::app()->getStores();
            foreach($stores as $store){
                $options = array_merge($options,$this->getGroupField($store->getId()));
            }
            $this->_cycle_group = $options;
        }
        return $this->_cycle_group;
    }

    public function _getContentGroupOptions(){
        if(!$this->_content_group){
            $options = array();
            $stores = Mage::app()->getStores();
            foreach($stores as $store){
                $options = array_merge($options,$this->getContentField($store->getId()));
            }
            $this->_content_group = $options;
        }
        return $this->_content_group;
    }
}
