<?php

class Imaginato_Size_Block_Adminhtml_Size_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'block_id';
        $this->_blockGroup = 'size';
        $this->_controller = 'adminhtml_size';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('size')->__('Save Chart'));
        $this->_updateButton('delete', 'label', Mage::helper('size')->__('Delete Chart'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('size')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'block_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('size_block') && Mage::registry('size_block')->getId()) {
            return Mage::helper('size')->__("Edit Chart '%s'", $this->escapeHtml(Mage::registry('size_block')->getTitle()));
        } else {
            return Mage::helper('size')->__('New Chart');
        }
    }

}
