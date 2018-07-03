<?php

class Imaginato_GiftCardAccount_Block_Sales_Order_Item_Renderer extends Enterprise_GiftCard_Block_Sales_Order_Item_Renderer
{

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        $result = parent::_getGiftcardOptions();
        if ($value = $this->_prepareCustomOption('giftcard_created_codes')) {
            $result[] = array(
                'label'=>Mage::helper('enterprise_giftcard')->__('Gift Card Accounts'),
                'value'=>implode('<br />',$value),
            );
        }
        return $result;
    }
}
