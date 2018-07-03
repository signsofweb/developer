<?php

class Imaginato_GiftCardAccount_Adminhtml_GiftcardaccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Export GCA grid to MSXML
     */
    public function exportMsxmlAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.xml',
            $this->getLayout()->createBlock('imaginato_giftcardaccount/adminhtml_giftcardaccount_export_grid')
                ->getExcelFile($this->__('Gift Card Accounts'))
        );
    }

    /**
     * Export GCA grid to CSV
     */
    public function exportCsvAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.csv',
            $this->getLayout()->createBlock('imaginato_giftcardaccount/adminhtml_giftcardaccount_export_grid')->getCsvFile()
        );
    }
}
