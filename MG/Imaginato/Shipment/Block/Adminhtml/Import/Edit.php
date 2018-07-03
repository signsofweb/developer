<?php

/**
 * Class Imaginato_Shipment_Block_Adminhtml_Import_Edit
 */
class Imaginato_Shipment_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->removeButton('back')
            ->removeButton('reset')
            ->_updateButton('save', 'label', $this->__('Check Data'))
            ->_updateButton('save', 'id', 'upload_button')
            ->_updateButton('save', 'onclick', 'editForm.postToFrame();');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('imaginato_shipment')->__('Import AWB');
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'import_id';
        $this->_blockGroup = 'imaginato_shipment';
        $this->_controller = 'adminhtml_import';
    }
}
