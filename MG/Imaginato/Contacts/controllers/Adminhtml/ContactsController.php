<?php

/**
 * Contacts extension for Magento
 *
 */
class Imaginato_Contacts_Adminhtml_ContactsController extends Mage_Adminhtml_Controller_Action
{
	/**
     * Init actions
     *
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_title($this->__('Contacts'));
        
        $this->loadLayout()
            ->_setActiveMenu('cms/imaginato_contacts')
            ->_addBreadcrumb(Mage::helper('imaginato_contacts')->__('Contacts'), Mage::helper('imaginato_contacts')->__('Contacts'))
        ;
        return $this;
    }

    protected function _initModel($noId = false)
    {
		// 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id', null);
        $model = Mage::getModel('imaginato_contacts/contacts');
		// 2. Initial checking
        if (!$noId && $id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('imaginato_contacts')->__('This contact no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        return $model;
    }


    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
	
	/**
     * Used for Ajax Based Grid
     *
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('imaginato_contacts/adminhtml_contact_grid')->toHtml()
        );
    }
	
	/**
     * Edit Contacts controller
     */
    public function editAction()
    {
      $id = $this->getRequest()->getParam('entity_id');
		$this->_title($this->__('Contacts'))
             ->_title($this->__('Manage Contacts'));
		$model = $this->_initModel();
        $this->_title($model->getId() ? $model->getName() : $this->__('New Contacts'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getContactData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
		// 4. Register model to use later in blocks
        Mage::register('contact_data', $model);
		// 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('imaginato_contacts')->__('Edit Contacts')
                    : Mage::helper('imaginato_contacts')->__('New Contacts'),
                $id ? Mage::helper('imaginato_contacts')->__('Edit Contacts')
                    : Mage::helper('imaginato_contacts')->__('New Contacts'));
        
         $this->_addContent($this->getLayout()->createBlock('imaginato_contacts/adminhtml_contact_edit'))
                ->_addLeft($this->getLayout()->createBlock('imaginato_contacts/adminhtml_contact_edit_tabs'));
        $this->renderLayout();
    }

	public function deleteAction()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            $model = $this->_initModel();
            try {
                $model->delete();
				$this->_redirect('*/*/');
                $this->_getSession()->addSuccess($this->__('The item has been deleted.'));
				return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
				// go back to edit form
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
		// display error message
        $this->_getSession()->addError(Mage::helper('imaginato_contacts')->__('Unable to find a contact to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        if ($entityIds = $this->getRequest()->getParam('entity_id')) {
            if (!is_array($entityIds)) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s).'));
            } else {
                try {
                    //Init Block
                    $model = $this->_initModel(true);
                    //massDelete Action
                    foreach ($entityIds as $entityId) {
                        $model->load($entityId)->delete();
                    }

                    $this->_getSession()->addSuccess(
                        $this->__(
                            'Total of %d item(s) were deleted.', count($entityIds)
                        )
                    );

                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => Mage::app()->getStore()->getId())));
    }

	/**
     * Export order grid to CSV, with clean formatting
     */
    public function exportCsvAction()
    {
        $fileName = 'contacts.csv';

        try {
            /** @var Imaginato_Contacts_Block_Adminhtml_Contact_Grid $grid */
            $grid = $this->getLayout()->createBlock('imaginato_contacts/adminhtml_contact_grid');
            if ($grid && $grid instanceof Mage_Adminhtml_Block_Widget_Grid) {
                $grid->setReadonlyGrid(true);
                $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
            } else {
                $this->_getSession()->addError($this->getHelper()->__('Failed to export.'));
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }
    }

}
