<?php

/**
 * Class Imaginato_Checkout_Block_Checkout_Discount
 */
class Imaginato_Checkout_Block_Checkout_Discount extends Mage_Checkout_Block_Total_Default
{
    protected function _construct()
    {
        if (Mage::getStoreConfig('imaginato_checkout/general/breakdown')) {
            $this->_template = 'checkout/discount.phtml';
        }

        parent::_construct();
    }
}