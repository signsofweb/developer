<?php

/**
 * Location extension for Magento
 *
 */

/**
 * Class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit
 *
 */
class Imaginato_ProductPriceRules_Block_Adminhtml_Promo_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'skusrule';

    public function __construct()
    {
        $this->_objectId = 'rule_id';
        $this->_controller = 'adminhtml_promo_product';
        $this->_mode = 'edit';

        parent::__construct();

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ));

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    /**
     * This function return’s the Text to display as the form header.
     */
    public function getHeaderText()
    {
        if (!$this->hasData('header_text')) {
            if (Mage::registry('promo_product_rule')->getId()) {
                $title = Mage::registry('promo_product_rule')->getName();
                if ($title) {
                    return Mage::helper('skusrule')->__("Edit Rule '%s'", $this->escapeHtml($title));
                } else {
                    return Mage::helper('skusrule')->__("Edit Rule");
                }
            } else {
                return Mage::helper('skusrule')->__('New Rule');
            }
        }
        return $this->getData('header_text');
    }
}
