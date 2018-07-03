<?php

class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_promo_product';
        $this->_headerText = $this->helper('skusrule')->__('Product special price rules');
        $this->_blockGroup = 'skusrule';
        parent::__construct();
    }
}
