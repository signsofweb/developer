<?php

class Imaginato_Size_Adminhtml_SizeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('CMS'))->_title($this->__('Size Chart'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Init actions
     *
     * @return Imaginato_Size_Adminhtml_SizeController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('cms/size')
            ->_addBreadcrumb(Mage::helper('size')->__('CMS'), Mage::helper('size')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('size')->__('Size Chart'), Mage::helper('size')->__('Size Chart'));
        return $this;
    }

    /**
     * Create new Chart block
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit Chart block
     */
    public function editAction()
    {
        $this->_title($this->__('CMS'))->_title($this->__('Size Chart'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('block_id');
        $model = Mage::getModel('size/block');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('size')->__('This chart no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $this->__('New Chart'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('size_block', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('size')->__('Edit Block') : Mage::helper('size')->__('New Chart'), $id ? Mage::helper('size')->__('Edit Chart') : Mage::helper('size')->__('New Chart'))
            ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('block_id');
            $model = Mage::getModel('size/block')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('size')->__('This block no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }

            // prepare post data
            if (isset($data['related_products_chart'])) {
                $related = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['related_products_chart']);
                foreach ($related as $_key => $_rid) {
                    $related[$_key] = (int)$_rid;
                }
                $data['related_products_chart'] = $related;
            }

            // try to save it
            try {
                if (!empty($data)) {
                    $model->addData($data);
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                }
                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('size')->__('The chart has been saved.'));

                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('block_id' => $model->getId()));
                    return;
                }

                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('size')->__('Unable to save the chart.'));
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $redirectBack = true;
                Mage::logException($e);
            }
            if ($redirectBack) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('block_id')) {
            $title = "";
            try {
                // init model and delete
                $model = Mage::getModel('size/block');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('size')->__('The block has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('block_id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('size')->__('Unable to find a block to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Grid Action
     *
     * @return void
     */
    public function productsGridAction()
    {
        if (!$sizechart = $this->_initChart()) {
            return;
        }

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('size/adminhtml_size_edit_tab_products_grid', 'related_size_products_grid')
                ->setSelectedSizeProducts($this->getRequest()->getPost('selected_size_products'))
                ->toHtml()
        );
    }

    protected function _initChart()
    {
        $id = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        /** @var Imaginato_Size_Model_Block $model */
        $model = Mage::getModel('size/block');
        $model->setStoreId($storeId);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('size')->__('This chart no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        if ($activeTabId = (string)$this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('size_block', $model);

        if (!Mage::registry('current_chart')) {
            Mage::register('current_chart', $model);
        }

        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $model;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('size/block');
    }
}
