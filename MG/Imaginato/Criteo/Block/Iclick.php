<?php

class Imaginato_Criteo_Block_Iclick extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $helper = Mage::helper('criteo');
        $isActive = $helper->isActive();
        $_account = $helper->getAccount();

        if(!$isActive || !$_account){
            return '';
        }
        return parent::_toHtml();
    }
}