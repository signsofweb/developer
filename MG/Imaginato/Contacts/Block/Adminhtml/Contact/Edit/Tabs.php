<?php

/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Tabs
 *
 * @method setTitle()
 */
class Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected $_blockGroup = 'imaginato_contacts';

    /**
     * Imaginato_Contacts_Block_Adminhtml_Contact_Edit_Tabs constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('block_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('imaginato_contacts')->__('Contacts Information'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->addTab('form_section_general', array(
            'label'   => Mage::helper('imaginato_contacts')->__('General'),
            'content' => $this->getLayout()->createBlock('imaginato_contacts/adminhtml_contact_edit_tab_form')->toHtml(),
            'active'  => true
        ));
        return parent::_prepareLayout();
    }
}
