<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales/OrderController.php';

class Imaginato_Shipment_Adminhtml_Imaginato_ShipmentController
    extends Mage_Adminhtml_Sales_OrderController
{
    const FILENAME      = 'evisu_awb_sample_import_template.csv';
    const FILEINPUTNAME = 'import_file';

    /**
     * Export sample csv for AWB Import
     */
    public function exportPostAction()
    {
        $post = $this->getRequest()->getPost();

        /** @var Mage_Adminhtml_Model_Session $adminHtmlSession */
        $adminHtmlSession = Mage::getSingleton('adminhtml/session');

        $contentCSV = '';
        try {
            $orderIdsList = (isset($post['order_ids'])) ? $post['order_ids'] : array();

            $serviceGenerateCSV = new Imaginato_Shipment_Service_GenerateCSV($orderIdsList);
            $contentCSV = $serviceGenerateCSV->call();

            $adminHtmlSession->getMessages(true);
        } catch (Mage_Core_Exception $e) {
            $adminHtmlSession->addError($e->getMessage());
        } catch (Exception $e) {
            $adminHtmlSession->addError($this->getHelper()->__('AWB / Tracking export failed.'));
        }

        $this->_prepareDownloadResponse(self::FILENAME, $contentCSV);
    }

    /**
     * @return Imaginato_Shipment_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function getHelper()
    {
        return Mage::helper('imaginato_shipment');
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $maxUploadSize = Mage::helper('imaginato_shipment')->getMaxUploadSize();
        $this->_getSession()->addNotice(
            $this->__('Total size of uploadable files must not exceed %s', $maxUploadSize)
        );

        $this->_initAction()
            ->_addBreadcrumb($this->__('Import AWB'), $this->__('Import AWB'));

        $this->renderLayout();
    }

    protected function _initAction()
    {
        $this->_title($this->__('Import/Export AWB'))
            ->loadLayout()
            ->_setActiveMenu('sales/imaginato_shipment')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));

        return $this;
    }

    /**
     * Start import process action.
     *
     * @return void
     */
    public function importPostAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->loadLayout(false);

            /** @var Imaginato_Shipment_Block_Adminhtml_Import_Frame_Result $resultBlock */
            $resultBlock = $this->getLayout()->getBlock('imaginato_shipment.frame.result');

            /** @var $importModel Imaginato_Shipment_Model_Import */
            $importModel = Mage::getModel('imaginato_shipment/import');

            try {
                $importModel->importSource();
                $importModel->invalidateIndex();

                if ($importModel->getErrors()) {
                    foreach ($importModel->getErrors() as $errorCode => $rows) {
                        $error = $errorCode . ' ' . $this->__('in rows:') . ' ' . implode(', ', $rows);
                        $resultBlock->addError($error);
                    }
                }

                $resultBlock->addAction('show', 'import_validation_container')
                    ->addAction('innerHTML', 'import_validation_container_header', $this->__('Status'));

            } catch (Exception $e) {
                $resultBlock->addError($e->getMessage());
                //add error message to adminhtml/session for admin logging to catch this error
                Mage::getModel('adminhtml/session')->addError($e->getMessage());
                $this->renderLayout();
                return;
            }

            $resultBlock->addAction('hide', array('edit_form', 'upload_button', 'messages'))
                ->addSuccess($this->__('Import process done.'));
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Validate uploaded files action.
     *
     * @return void
     */
    public function validateAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->loadLayout(false);
            /** @var Imaginato_Shipment_Block_Adminhtml_Import_Frame_Result $resultBlock */
            $resultBlock = $this->getLayout()->getBlock('imaginato_shipment.frame.result');
            // common actions
            $resultBlock->addAction('show', 'import_validation_container')
                ->addAction('clear', array(
                        Imaginato_Shipment_Model_Import::FIELD_NAME_SOURCE_FILE,
                        Imaginato_Shipment_Model_Import::FIELD_NAME_IMG_ARCHIVE_FILE)
                );

            try {
                /** @var Imaginato_Shipment_Model_Import $import */
                $import = Mage::getModel('imaginato_shipment/import');
                $validationResult = $import->validateSource($import->setData($data)->uploadSource());

                if (!$import->getProcessedRowsCount()) {
                    $resultBlock->addError($this->__('File does not contain data. Please upload another one'));
                } else {
                    if (!$validationResult) {
                        if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
                            $resultBlock->addNotice(
                                $this->__('File is totally invalid. Please fix errors and re-upload file')
                            );
                        } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
                            $resultBlock->addNotice(
                                $this->__('Errors limit (%d) reached. Please fix errors and re-upload file', $import->getErrorsLimit())
                            );
                        } else {
                            if ($import->isImportAllowed()) {
                                $resultBlock->addNotice(
                                    $this->__('Please fix errors and re-upload file or simply press "Import" button to skip rows with errors'),
                                    true
                                );
                            } else {
                                $resultBlock->addNotice(
                                    $this->__('File is partially valid, but import is not possible'), false
                                );
                            }
                        }
                        // errors info
                        foreach ($import->getErrors() as $errorCode => $rows) {
                            $error = $errorCode . ' ' . $this->__('in rows:') . ' ' . implode(', ', $rows);
                            $resultBlock->addError($error);
                        }
                    } else {
                        if ($import->isImportAllowed()) {
                            $resultBlock->addSuccess(
                                $this->__('File is valid! To start import process press "Import" button'), true
                            );
                        } else {
                            $resultBlock->addError(
                                $this->__('File is valid, but import is not possible'), false
                            );
                        }
                    }
                    $resultBlock->addNotice($import->getNotices());
                    $resultBlock->addNotice($this->__('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d', $import->getProcessedRowsCount(), $import->getProcessedEntitiesCount(), $import->getInvalidRowsCount(), $import->getErrorsCount()));
                }
            } catch (Exception $e) {
                $resultBlock->addNotice($this->__('Please fix errors and re-upload file'))
                    ->addError($e->getMessage());
            }
            $this->renderLayout();
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $this->loadLayout(false);
            /** @var Imaginato_Shipment_Block_Adminhtml_Import_Frame_Result $resultBlock */
            $resultBlock = $this->getLayout()->getBlock('imaginato_shipment.frame.result');
            $resultBlock->addError($this->__('File was not uploaded'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Data is invalid or file is not uploaded'));
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Check access (in the ACL) for current user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/imaginato_shipment');
    }
}
