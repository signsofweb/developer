<?php
class Imaginato_Reward_Adminhtml_Reward_HistoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if module functionality enabled
     *
     * @return Enterprise_Reward_Adminhtml_Reward_RateController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_reward')->isEnabled() && $this->getRequest()->getActionName() != 'noroute') {
            $this->_forward('noroute');
        }
        return $this;
    }

    /**
     * History Staging Action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Staging Rewards'));
        $this->loadLayout();
        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('imaginato_reward/adminhtml_customer_reward_staging')
        );
        $this->renderLayout();
    }

    /**
     * Agree selected orders
     */
    public function massAgreeAction()
    {
        $stagingIds = $this->getRequest()->getPost('staging_ids', array());
        $countAgreeStaging = 0;
        $countNonAgreeStaging = 0;
        foreach ($stagingIds as $stagingId) {
            $staging = Mage::getModel('imaginato_reward/reward_staging')->load($stagingId);
            if ($staging->isAllowDetail()) {
                $staging->agreeReward();
                $countAgreeStaging++;
            } else {
                $countNonAgreeStaging++;
            }
        }
        if ($countNonAgreeStaging) {
            if ($countAgreeStaging) {
                $this->_getSession()->addError($this->__('%s staging(s) cannot be agreed', $countNonAgreeStaging));
            } else {
                $this->_getSession()->addError($this->__('The staging(s) cannot be agreed'));
            }
        }
        if ($countAgreeStaging) {
            $this->_getSession()->addSuccess($this->__('%s staging(s) have been agreed.', $countAgreeStaging));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Refuse selected orders
     */
    public function massRefuseAction()
    {
        $stagingIds = $this->getRequest()->getPost('staging_ids', array());
        $countRefuseStaging = 0;
        $countNonRefuseStaging = 0;
        foreach ($stagingIds as $stagingId) {
            $staging = Mage::getModel('imaginato_reward/reward_staging')->load($stagingId);
            if ($staging->isAllowDetail()) {
                $staging->refuseReward();
                $countRefuseStaging++;
            } else {
                $countNonRefuseStaging++;
            }
        }
        if ($countNonRefuseStaging) {
            if ($countRefuseStaging) {
                $this->_getSession()->addError($this->__('%s staging(s) cannot be refuse', $countNonRefuseStaging));
            } else {
                $this->_getSession()->addError($this->__('The staging(s) cannot be refuse'));
            }
        }
        if ($countRefuseStaging) {
            $this->_getSession()->addSuccess($this->__('%s staging(s) have been refuse.', $countRefuseStaging));
        }
        $this->_redirect('*/*/');
    }
}
