<?php

class Imaginato_Reward_CouponController extends Mage_Core_Controller_Front_Action
{
    /**
     * Predispatch
     * Check is customer authenticate
     * Check is RP enabled on frontend
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }


    public function indexAction(){
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('imaginato_reward')->__('My Coupon'));
        }
        $this->renderLayout();
    }

    public function redeemAction(){
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('imaginato_reward')->__('Redeem Coupon'));
        }
        $this->renderLayout();
    }

    /**
     * Save settings
     */
    public function redeemCouponAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/redeem');
        }
        if(!$this->getRequest()->getParam('id')){
            return $this->_redirect('*/*/redeem');
        }
        $customer = $this->_getCustomer();
        if ($customer->getId()) {
            $couponHistory = Mage::getModel('imaginato_reward/reward_coupon_history');
            $couponHistory->redeemCoupon($customer->getId(),$this->getRequest()->getParam('id'));
            if($couponHistory->_redeem_flg){
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('imaginato_reward')->__('Succeed of redeem coupon.'));
            }else{
                Mage::getSingleton('customer/session')->addError(Mage::helper('imaginato_reward')->__('Coupon redeem failed, please re-operate.'));
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomer()
    {
        return $this->_getSession()->getCustomer();
    }
}
