<?php

class Imaginato_Checkout_Block_Onepage_Address_List extends Mage_Checkout_Block_Onepage_Abstract
{

    protected $_addressId;
    protected $_type;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('imaginato/checkout/onepage/address/list.phtml');
    }

    public function getTitle(){
        return 'Change '.ucfirst($this->getType()).' Address';
    }

    public function setAddress($addressId){
        $this->_addressId = $addressId;
        return $this;
    }

    public function getAddressId(){
        return $this->_addressId;
    }

    public function setType($type){
        $this->_type = $type;
        return $this;
    }

    public function getType(){
        return $this->_type;
    }
}
