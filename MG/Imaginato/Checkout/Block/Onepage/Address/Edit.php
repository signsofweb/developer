<?php

class Imaginato_Checkout_Block_Onepage_Address_Edit extends Mage_Checkout_Block_Onepage_Abstract
{

    protected $_address;
    protected $_type;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('imaginato/checkout/onepage/address/edit.phtml');
        $this->_address = Mage::getModel('customer/address');
    }

    public function getTitle(){
        return $this->getType()?'Edit Address':'Add New Address';
    }

    public function setAddress($addressId){
        $this->_address->load($addressId);
        return $this;
    }

    public function getAddress(){
        return $this->_address;
    }

    public function setType($type){
        $this->_type = $type;
        return $this;
    }

    public function getType(){
        return $this->_type;
    }

    public function isDefaultBilling()
    {
        $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
    }

    public function isDefaultShipping()
    {
        $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
    }


    public function getCustomerAddressCount()
    {
        return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
    }

    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();
    }
}
