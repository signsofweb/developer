<?php
/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Edit
 *
 */
class Imaginato_Contacts_Block_Adminhtml_Contact_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'imaginato_contacts';

    public function __construct()
    {
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_contact';
        $this->_mode = 'edit';

        parent::__construct();
        $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_updateButton('delete', 'label', Mage::helper('imaginato_contacts')->__('Delete Contact'));
    }
	/**
     * This function return’s the Text to display as the form header.
     */
    public function getHeaderText()
    {
        if (!$this->hasData('header_text')) {
            if (Mage::registry('contact_data')->getId()) {
                $title = (Mage::registry('contact_data')->getTitle()) ? Mage::registry('contact_data')->getTitle() : Mage::registry('contact_data')->getName();
                if ($title) {
                    return Mage::helper('imaginato_contacts')->__("View Contact '%s'", $this->escapeHtml($title));
                } else {
                    return Mage::helper('imaginato_contacts')->__("View Contact");
                }
            } else {
                return Mage::helper('imaginato_contacts')->__('New Contact');
            }
        }
        return $this->getData('header_text');
    }
}
