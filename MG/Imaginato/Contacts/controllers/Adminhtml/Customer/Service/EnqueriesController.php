<?php

/**
 * Contacts extension for Magento
 *
 */
class Imaginato_Contacts_Adminhtml_Customer_Service_EnqueriesController extends Mage_Adminhtml_Controller_Action
{
	/**
     * Init actions
     *
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_title($this->__('Customer Service Enqueries'));
        
        $this->loadLayout()
            ->_setActiveMenu('customer/imaginato_customer_service')
            ->_addBreadcrumb(Mage::helper('imaginato_contacts')->__('Customer Service Enqueries'), Mage::helper('imaginato_contacts')->__('Customer Service Enqueries'))
        ;

        //

            // $this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquerytype_grid')->toHtml();
        
        return $this;
    }

    protected function _initModel($noId = false)
    {
		// 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id', null);
        $model = Mage::getModel('imaginato_contacts/enqueries');
		// 2. Initial checking
        if (!$noId && $id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('imaginato_contacts')->__('This enquery is no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        return $model;
    }


    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquery'));
        $this->renderLayout();
    }
	
	/**
     * Used for Ajax Based Grid
     *
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->_addContent(
            $this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquery_grid')
        );
        $this->renderLayout();
    }

	/**
     * Create new Contacts controller
     */
    public function newAction()
    {
		// the same form is used to create and edit
        $this->_forward('edit');
    }
	
	/**
     * Edit Contacts controller
     */
    public function editAction()
    {
      $id = $this->getRequest()->getParam('entity_id');
		$this->_title($this->__('Customer Service Enqueries'))
             ->_title($this->__('Manage Enquery'));
		$model = $this->_initModel();
        $this->_title($model->getId() ? $model->getName() : $this->__('New Enquery'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getEnqueryData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
		// 4. Register model to use later in blocks
        Mage::register('enquery_data', $model);
		// 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('imaginato_contacts')->__('Edit Enquery')
                    : Mage::helper('imaginato_contacts')->__('New Enquery'),
                $id ? Mage::helper('imaginato_contacts')->__('Edit Enquery')
                    : Mage::helper('imaginato_contacts')->__('New Enquery'));
        
         $this->_addContent($this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquery_edit'))
                ->_addLeft($this->getLayout()->createBlock('imaginato_contacts/adminhtml_customer_service_enquery_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getPost();

        if ($data) {
			$model = $this->_initModel();
			$currentTimestamp = Mage::getModel('core/date')->timestamp(time());
			if ($id) {
                $data['updated_at'] = $currentTimestamp;
            }else {
                $data['created_at'] = $currentTimestamp;
				$data['updated_at'] = $currentTimestamp;
            }
			// init model and set data
			$model->setData($data);
            try {
                $model->save();
                $this->_getSession()->addSuccess($this->__('The item has been saved.'));
				// check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('entity_id' => $model->getId(), '_current'=>true));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setBlockData($data);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
			$this->_getSession()->setContactData($data);
            $this->_redirect('*/*/edit', array('entity_id' => $id));
            return;
        }
		$this->_redirect('*/*/');
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
        $this->_getSession()->addError(Mage::helper('imaginato_contacts')->__('Unable to find the enquery to delete.'));
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

}
