<?php

class Imaginato_Newsletter_ManageController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_subscribed = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer)->isSubscribed();
        $change_subscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false);
        if($customer_subscribed && !$change_subscribed){
            return $this->_redirect('*/*/confirm');
        }
        try {
            Mage::getSingleton('customer/session')->getCustomer()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
            ->save();
            if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                if(Mage::getSingleton('customer/session')->getMessages()->count()==0){
                    Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
                }
            } else {
                Mage::getSingleton('customer/session')->getMessages()->clear();
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }

    public function confirmAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Confirm Subscription'));
        $this->renderLayout();
    }

    public function saveConfirmAction()
    {
        $subscription = $this->getRequest()->getParam('subscription');
        $status = (boolean)$subscription['status'];
        $store_id = Mage::app()->getStore()->getId();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if($status){
            if (!$this->_validateFormKey()) {
                return $this->_redirect('customer/account/');
            }

            $interests = array();

            $group_fields = Mage::helper('imaginato_mailchimp')->getGroupField($store_id);
            foreach($group_fields as $key=>$value){
                if($key==$subscription['group']){
                    $interests[$key] = true;
                }else{
                    $interests[$key] = false;
                }
            }

            $content_fields = Mage::helper('imaginato_mailchimp')->getContentField($store_id);
            foreach($content_fields as $key=>$value){
                if(in_array($key,$subscription['content'])){
                    $interests[$key] = true;
                }else{
                    $interests[$key] = false;
                }
            }

            $customer_subscribed = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
            $customer_subscribed->setData('mailchimp_sync_cycle_group',$subscription['group']);
            $customer_subscribed->setData('mailchimp_sync_content_group',implode(',',$subscription['content']));
            $customer_subscribed->save();

            if($customer->getEmail()){
                $helper = Mage::helper('mailchimp');
                $mailchimpApi = $helper->getApi($store_id);
                $listId = $helper->getGeneralList($store_id);
                $md5HashEmail = md5(strtolower($customer->getEmail()));
                $mailchimpApi->lists->members->update($listId, $md5HashEmail, null, null, null,$interests);
            }
        }

        try {
            $customer
                ->setStoreId($store_id)
                ->setIsSubscribed($status)
                ->save();
            if ($status) {
                if(Mage::getSingleton('customer/session')->getMessages()->count()==0){
                    Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
                }
            } else {
                Mage::getSingleton('customer/session')->getMessages()->clear();
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
