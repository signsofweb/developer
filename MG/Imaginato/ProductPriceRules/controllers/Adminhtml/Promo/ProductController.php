<?php
class Imaginato_ProductPriceRules_Adminhtml_Promo_ProductController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Get rule helper
     *
     * @return Imaginato_ProductPriceRules_Helper_Data
     */
    protected function getRuleHelper()
    {
        return Mage::helper('skusrule');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/skusrule')
            ->_addBreadcrumb(
                Mage::helper('skusrule')->__('Promotions'),
                Mage::helper('skusrule')->__('Promotions')
            );
        return $this;
    }

    protected function _initModel($noId = false)
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('rule_id', null);
        $model = Mage::getModel('skusrule/rule_product_price');
        // 2. Initial checking
        if (!$noId && $id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::throwException(Mage::helper('skusrule')->__('Wrong rule specified.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        return $model;
    }

    public function indexAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Product Price Rules'));

        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('skusrule')->__('Product Price'),
                Mage::helper('skusrule')->__('Product Price')
            )
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Product Price Rules'));

        $id = $this->getRequest()->getParam('rule_id');
        $model = $this->_initModel();
        $this->_title($model->getId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getProductRuleData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        Mage::register('promo_product_rule', $model);

        $breadcrumb = $id
            ? $this->getRuleHelper()->__('Edit Rule')
            : $this->getRuleHelper()->__('New Rule');
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_addContent($this->getLayout()->createBlock('skusrule/adminhtml_promo_product_edit'))
                ->_addLeft($this->getLayout()->createBlock('skusrule/adminhtml_promo_product_edit_tabs'));

        $this->renderLayout();

    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = $this->_initModel();
                $data = $this->getRequest()->getPost();
                $data['name'] = $data['rule_name'];
                unset($data['rule_name']);
                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setProductRuleData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }
                $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
                if($this->getRequest()->getParam('rule_id')){
                    $data['updated_at'] = $currentTimestamp;
                }else{
                    $data['created_at'] = $data['updated_at'] = $currentTimestamp;
                }
                $model->addData($data);


                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->getRuleHelper()->__('The rule has been saved.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('rule_id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->getRuleHelper()->__('An error occurred while saving the rule data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setProductRuleData($data);
                $this->_redirect('*/*/edit', array('rule_id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete rule action
     */
    public function deleteAction()
    {
        $rule = $this->_initModel();
        if ($rule->getId()) {
            try {
                $this->getRuleHelper()->updateProductSpecialPrice($rule);
                $rule->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rule has been deleted.'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Mass delete store location controller
     */
    public function massDeleteAction()
    {
        if ($ruleIds = $this->getRequest()->getParam('rule_id')) {
            if (!is_array($ruleIds)) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(es).'));
            } else {
                try {
                    //Init Model
                    $model = $this->_initModel(true);
                    //massDelete Action
                    foreach ($ruleIds as $ruleId) {
                        $rule = $model->load($ruleId);
                        $this->getRuleHelper()->updateProductSpecialPrice($rule);
                        $rule->delete();
                    }

                    $this->_getSession()->addSuccess(
                        $this->__(
                            'Total of %d item(s) were deleted.', count($ruleIds)
                        )
                    );

                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    /**
     * Used for Ajax Based Grid
     *
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('skusrule/adminhtml_promo_product_grid')->toHtml()
        );
    }

    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('promo.product.price.rule.edit.tab.product')
                ->setProductIds($this->getRequest()->getPost('product_ids', null));
        $this->renderLayout();
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('promo.product.price.rule.edit.tab.product')
                ->setProductIds($this->getRequest()->getPost('product_ids', null));
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/product');
    }
}
