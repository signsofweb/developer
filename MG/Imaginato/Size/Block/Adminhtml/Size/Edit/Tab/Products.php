<?php

/**
 * Class Imaginato_Size_Block_Adminhtml_Size_Edit_Tab_Products
 */
class Imaginato_Size_Block_Adminhtml_Size_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Text_List
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('size')->__('Related Products');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('size')->__('Related Products');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
