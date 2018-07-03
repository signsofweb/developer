<?php

class Imaginato_Newsletter_Model_Monkey_Observer extends Ebizmarts_MageMonkey_Model_Observer
{
    /**
     * Handle Subscriber object saving process
     *
     * @param Varien_Event_Observer $observer
     * @return void|Varien_Event_Observer
     */
    public function handleSubscriber(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('monkey')->canMonkey()) {
            return $observer;
        }

        if (TRUE === Mage::helper('monkey')->isWebhookRequest()) {
            return $observer;
        }

        $subscriber = $observer->getEvent()->getSubscriber();

        if ($subscriber->getBulksync()) {
            return $observer;
        }

        if (Mage::getSingleton('core/session')->getMonkeyCheckout(TRUE)) {
            return $observer;
        }

        $email = $subscriber->getSubscriberEmail();
        if ($subscriber->getMcStoreId()) {
            $listId = Mage::helper('monkey')->getDefaultList($subscriber->getMcStoreId());
        } elseif ($subscriber->getStoreId()) {
            $listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());
        } else {
            $listId = Mage::helper('monkey')->getDefaultList(Mage::app()->getStore()->getId());
        }
        $subscriber->setImportMode(TRUE);
        $isConfirmNeed = FALSE;
        if (!Mage::helper('monkey')->isAdmin() &&
            (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId()) == 1)
        ) {
            $isConfirmNeed = TRUE;
        }

        if ($isConfirmNeed) {
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
        }

        //Check if customer is not yet subscribed on MailChimp
        $isOnMailChimp = Mage::helper('monkey')->subscribedToList($email, $listId);

        //Flag only is TRUE when changing to SUBSCRIBE
        if (TRUE === $subscriber->getIsStatusChanged()) {

            if ($isOnMailChimp == 1) {
                return $observer;
            }

            if ($isConfirmNeed) {
                $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request has been sent.'));
            }

            Mage::getSingleton('monkey/api')->listSubscribe($listId, $email, $this->_mergeVars($subscriber), 'html', $isConfirmNeed);

        }

    }
}
