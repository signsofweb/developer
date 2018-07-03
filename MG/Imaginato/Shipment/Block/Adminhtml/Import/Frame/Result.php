<?php

/**
 * Class Imaginato_Shipment_Block_Adminhtml_Import_Frame_Result
 */
class Imaginato_Shipment_Block_Adminhtml_Import_Frame_Result extends Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
{

    /**
     * Import button HTML for append to message.
     *
     * @return string
     */
    public function getImportButtonHtml()
    {
        return '&nbsp;&nbsp;<button onclick="editForm.startImport(\'' . $this->getImportStartUrl()
            . '\', \'' . Imaginato_Shipment_Model_Import::FIELD_NAME_SOURCE_FILE . '\');" class="scalable save"'
            . ' type="button"><span><span><span>' . $this->__('Import') . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl()
    {
        return $this->getUrl('*/*/importPost');
    }

}
