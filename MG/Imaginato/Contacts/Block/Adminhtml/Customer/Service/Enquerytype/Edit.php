<?php
/**
 * Class Imaginato_Contacts_Block_Adminhtml_Contact_Edit
 *
 */
class Imaginato_Contacts_Block_Adminhtml_Customer_Service_Enquerytype_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'imaginato_contacts';

    public function __construct()
    {
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_customer_service_enquerytype';
        $this->_mode = 'edit';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('imaginato_contacts')->__('Save Enquery Type'));
        $this->_updateButton('delete', 'label', Mage::helper('imaginato_contacts')->__('Delete Enquery Type'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

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
            if (Mage::registry('enquerytype_data')->getId()) {
                $title = (Mage::registry('enquerytype_data')->getTitle()) ? Mage::registry('enquerytype_data')->getTitle() : Mage::registry('enquerytype_data')->getName();
                if ($title) {
                    return Mage::helper('imaginato_contacts')->__("Edit Enquery Type '%s'", $this->escapeHtml($title));
                } else {
                    return Mage::helper('imaginato_contacts')->__("Edit Enquery Type");
                }
            } else {
                return Mage::helper('imaginato_contacts')->__('New Enquery Type');
            }
        }
        return $this->getData('header_text');
    }
}
