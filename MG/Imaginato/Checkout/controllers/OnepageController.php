<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';
class Imaginato_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    protected function _preDispatchValidateCustomer($redirect = false, $addErrors = true)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId()) {
            $validationResult = $customer->validate();
            if ((true !== $validationResult) && is_array($validationResult)) {
                if ($addErrors) {
                    foreach ($validationResult as $error) {
                        Mage::getSingleton('customer/session')->addError($error);
                    }
                }
                if ($redirect) {
                    $this->_redirect('customer/account/edit');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Save checkout billing address and shipping address
     */
    public function saveAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            if($customerAddressId){
                $data = array_merge($data,Mage::getModel('customer/address')->load($customerAddressId)->getData());
            }

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
            if (isset($data['use_for_shipping']) && $data['use_for_shipping'] != 1){
                $shippingData = $this->getRequest()->getPost('shipping', array());
                $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                if($shippingAddressId){
                    $shippingData = array_merge($shippingData,Mage::getModel('customer/address')->load($shippingAddressId)->getData());
                }
                $shippingResult = $this->getOnepage()->saveShipping($shippingData, $shippingAddressId);
                $result = array_merge($result,$shippingResult);
            }

            if (!isset($result['error'])) {
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $this->loadLayout('checkout_onepage_review');
                    $result['goto_section'] = 'review';
                    $result['update_section'] = array(
                        'name' => 'review',
                        'html' => $this->_getReviewHtml()
                    );
                } else {
//                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('billing');
                    if(isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1){
                        $result['duplicateBillingInfo'] = 'true';
                    }
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingDataAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            // $result will contain error data if shipping method is empty
            if (!$result) {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                    array(
                        'request' => $this->getRequest(),
                        'quote'   => $this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();

                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
        }
    }

    public function gotoPaymentAction(){
        if ($this->_expireAjax()) {
            return;
        }
        Mage::dispatchEvent(
            'checkout_controller_onepage_save_shipping_method',
            array(
                'request' => $this->getRequest(),
                'quote'   => $this->getOnepage()->getQuote()
            )
        );
        $this->getOnepage()->getQuote()->collectTotals();
        $this->getOnepage()->getCheckout()
            ->setStepData('review', 'allow', true)
            ->setStepData('review', 'complete', true);
        $result['goto_section'] = 'payment';
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => $this->_getPaymentMethodsHtml()
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentDataAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->saveOrderAction();
                return;
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $result['payment'] = true;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode()) {
                    $result['succ'] = true;
                    $result['coupon'] = $couponCode;
                    $result['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode));
                } else {
                    $result['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
                }
            } else {
                $result['succ'] = true;
                $result['message'] = $this->__('Coupon code was canceled.');
            }

        } catch (Mage_Core_Exception $e) {
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            $result['message'] = $this->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }
        if($result['succ']){
            $result['html'] = $this->loadLayout('checkout_onepage_review')->_getReviewHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Add Gift Card to current quote
     *
     */
    public function addGiftCardAction()
    {
        $data = $this->getRequest()->getParams();
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                if (strlen($code) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH) {
                    Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.'));
                }
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->addToCart();
                Mage::getSingleton('checkout/cart')->getQuote()->collectTotals()->save();
                $result['succ'] = true;
                $result['message'] = $this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($code));
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));
                $result['message'] = $e->getMessage();
            } catch (Exception $e) {
                $result['message'] = $this->__('Cannot apply gift card.');
                Mage::logException($e);
            }
        }
        if($result['succ']){
            $result['html'] = $this->loadLayout('checkout_onepage_review')->_getReviewHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function editAddressAction(){
        $addressType = $this->getRequest()->get('type');
        $addressId = $this->getRequest()->get('address_id');

        $addressBlock = $this->getLayout()->createBlock('imaginato_checkout/onepage_address_edit');
        $addressBlock->setType($addressType);
        $addressBlock->setAddress($addressId);

        $html = $addressBlock->toHtml();

        $this->getResponse()->setBody($html);
    }

    public function editPostAddressAction(){
        if ($this->getRequest()->isAjax()) {
            if (!$this->_validateFormKey()) {
                return $this->_redirect('*/*/');
            }
            $address_type = $this->getRequest()->getParam('address_type')?$this->getRequest()->getParam('address_type'):'address';
            // Save data
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam($address_type)['address_id'];
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }

            $errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
            $this->getRequest()->setParams($this->getRequest()->getParam($address_type));
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing'))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping'));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
                }
            } catch (Mage_Core_Exception $e) {
                $errors = array_merge($errors, array($e->getMessage()));
            } catch (Exception $e) {
                $errors = array_merge($errors, array('Cannot save address.'));
            }
            $result['error'] = $errors?true:false;
            $result['message'] = $errors;
            $result['address_id'] = $address->getId();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function changeAddressAction(){
        $addressType = $this->getRequest()->get('type');
        $addressId = $this->getRequest()->get('address_id');

        $addressBlock = $this->getLayout()->createBlock('imaginato_checkout/onepage_address_list');
        $addressBlock->setType($addressType);
        $addressBlock->setAddress($addressId);

        $html = $addressBlock->toHtml();

        $this->getResponse()->setBody($html);
    }

    public function reloadAddressAction(){
        $addressId = $this->getRequest()->get('address_id');

        $html = Mage::getModel('customer/address')->load($addressId)->format('html');

        $this->getResponse()->setBody($html);
    }
}