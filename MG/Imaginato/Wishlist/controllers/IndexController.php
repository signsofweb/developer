<?php

require_once 'Enterprise/Wishlist/controllers/IndexController.php';
class Imaginato_Wishlist_IndexController extends Enterprise_Wishlist_IndexController
{
    /**
     * Add the item to wish list
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    protected function _addItemToWishList()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            if($this->addAjaxResult()){
                return;
            }
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            if($this->addAjaxResult()){
                return;
            }
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist.',
                $product->getName());
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }
        if($this->addAjaxResult()){
            return;
        }

        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }

    protected function addAjaxResult(){
        if($this->getRequest()->isAjax()){
            $response       = new Varien_Object();
            $messageBlock = $this->getLayout()->getMessagesBlock();
            $messageBlock->setMessages($this->_getSession()->getMessages(true));
            $response->setMessage($messageBlock->getGroupedHtml());
            $this->getResponse()->setBody($response->toJson());
            return true;
        }
        return false;
    }
}
