<?php

class Imaginato_GiftCardAccount_CartController extends Mage_Core_Controller_Front_Action
{

    /**
     * Add Gift Card to current quote
     *
     */
    public function addAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                if (strlen($code) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH) {
                    Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.'));
                }
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->addToCart();
                Mage::getSingleton('checkout/session')->setGiftCardsMessage(
                    $this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($code))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));
                Mage::getSingleton('checkout/session')->setGiftCardsMessage(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->setGiftCardsMessage($e, $this->__('Cannot apply gift card.'));
            }
        }
        $this->_redirect('checkout/cart',array('_secure'=>true));
    }
}
