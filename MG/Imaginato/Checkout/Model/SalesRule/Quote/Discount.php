<?php

class Imaginato_Checkout_Model_SalesRule_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount
{
    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (!Mage::getStoreConfig('imaginato_checkout/general/breakdown'))
            return parent::fetch($address);

        $amount = $address->getDiscountAmount();
        if ($amount != 0) {
            $address->addTotal(array(
                'code'      => $this->getCode(),
                'title'     => Mage::helper('sales')->__('Discount'),
                'value'     => $amount,
                'full_info' => $address->getFullDescr(),
            ));
        }
        return $this;
    }
}