<?php
class Imaginato_MailChimp_Block_Subscription_Confirm extends Mage_Core_Block_Template
{
    protected $_template = 'imaginato/subscription/confirm.phtml';
    protected $_customer;
    protected $_groupfileds;
    protected $_contentfileds;

    protected function _construct()
    {
        parent::_construct();
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function getTitle(){
        return $this->helper('mailchimp')->__('Hi %,do you wanna leave us and go,seriously?',$this->_customer->getName());
    }

    protected function getFormAction(){
        return $this->getUrl('*/*/saveConfirm');
    }

    protected function getGroupfields(){
        if(!$this->_groupfileds){
            $this->_groupfileds = $this->helper('imaginato_mailchimp')->getGroupField(Mage::app()->getStore()->getId());
        }
        return $this->_groupfileds;
    }

    protected function getContentfields(){
        if(!$this->_contentfileds){
            $this->_contentfileds = $this->helper('imaginato_mailchimp')->getContentField(Mage::app()->getStore()->getId());
        }
        return $this->_contentfileds;
    }
}