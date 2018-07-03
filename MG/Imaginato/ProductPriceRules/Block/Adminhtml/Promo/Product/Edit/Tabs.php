<?php

/**
 * Class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit_Tabs
 *
 * @method setTitle()
 */
class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected $_blockGroup = 'skusrule';

    /**
     * Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit_Tabs constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('block_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('skusrule')->__('Rule Information'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->addTab('form_section_general', array(
            'label'   => Mage::helper('skusrule')->__('General'),
            'content' => $this->getLayout()->createBlock('skusrule/adminhtml_promo_product_edit_tab_form')->toHtml() . $this->getLayout()->createBlock('skusrule/adminhtml_promo_product_special_price_expired')->toHtml(),
            'active'  => true
        ));
        $this->addTab('product', array(
            'label' => Mage::helper('skusrule')->__('Products'),
            'title' => Mage::helper('skusrule')->__('Products'),
            'url' => $this->getUrl('*/*/products', array('_current' => true)),
            'class' => 'ajax',
        ));
        return parent::_prepareLayout();
    }
}
