<?php
class Imaginato_SpecialPrice_Adminhtml_Imaginato_SpecialpriceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Get rule helper
     *
     * @return Imaginato_ProductPriceRules_Helper_Data
     */
    protected function getRecordHelper()
    {
        return Mage::helper('imaginato_specialprice');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/imaginato_specialprice')
            ->_addBreadcrumb(
                $this->__('Promotions'),
                $this->__('Promotions')
            );
        return $this;
    }

    protected function _initModel($noId = false)
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('record_id', null);
        $model = Mage::getModel('imaginato_specialprice/record');
        // 2. Initial checking
        if (!$noId && $id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::throwException(Mage::helper('skusrule')->__('Wrong record specified.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->getProductIds();
            $model->getWebsiteIds();
        }else{
            // set entered data if was error when we do save
            $data = Mage::getSingleton('adminhtml/session')->getProductRuleData(true);
            if (!empty($data)) {
                $model->addData($data);
            }
        }
        return $model;
    }

    public function indexAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Batch Update Product Sprcial Price'));

        $this->_initAction()
            ->_addBreadcrumb(
                $this->__('Batch Update Product Sprcial Price'),
                $this->__('Batch Update Product Sprcial Price')
            )->renderLayout();

    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = $this->_initModel();
                $data = $this->getRequest()->getPost('general');
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                if (isset($data['skus'])) {
                    $post_skus = explode(',', $data['skus']);
                    $collection = Mage::getModel('catalog/product')->getCollection();
                    $collection->addFieldToFilter('sku', array('in' => $post_skus));
                    $collection->getSelect()->reset('columns')->columns(array('entity_id','sku'));
                    $model->setData('skuCollection',$collection);
                    $select_skus = $collection->getColumnValues('sku');
                    $diff = array_diff($post_skus,$select_skus);
                    if($diff){
                        throw new Mage_Core_Exception(
                            $this->__('Sku is error: %s',implode(',',$diff))
                        );
                    }
                    $data['posted_products'] = $collection->getAllIds();

                }
                $model->addData($data);
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('The special price has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setProductRuleData($data);
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while saving the rule data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setProductRuleData($data);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete rule action
     */
    public function deleteAction()
    {
        $record = $this->_initModel();
        if ($record->getId()) {
            try {
                $deleteWebsiteId = $this->getRequest()->getParam('website_id');
                $websiteIds = $record->getWebsiteIds();
                $record->setPostedWebsites(array_diff($websiteIds,array($deleteWebsiteId)));
                $record->setPostedProducts($record->getProductIds());
                $record->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rule has been deleted.'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}
