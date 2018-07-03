<?php
require_once 'Enterprise/Reward/controllers/Adminhtml/Reward/RateController.php';
class Imaginato_Reward_Adminhtml_Reward_RateController extends Enterprise_Reward_Adminhtml_Reward_RateController
{

    /**
     * Validate Action
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object(array('error' => false));
        $post     = $this->getRequest()->getParam('rate');
        $message  = null;

        if($post['direction'] == Imaginato_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_COUPON){
            if(isset($post['equal_value'])){
                $rule = Mage::getModel('salesrule/rule')->load($post['equal_value']);
                $now = Mage::getModel('core/date')->date('Y-m-d');
                if($rule->getData('is_active')==1 && $rule->getData('use_auto_generation')==1 &&
                    (empty($rule->getData('from_date'))||$rule->getData('from_date')<=$now) &&
                    (empty($rule->getData('to_date'))||$rule->getData('to_date')>=$now)){
                    $rule_id = $rule->getId();
                }
            }
        }

        if (!isset($post['customer_group_id'])
            || !isset($post['website_id'])
            || !isset($post['direction'])
            || !isset($post['value'])
            || !isset($post['equal_value'])) {
            $message = $this->__('Please enter all Rate information.');
        } elseif ($post['direction'] == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY
                  && ((int) $post['value'] <= 0 || (float) $post['equal_value'] <= 0)) {
              if ((int) $post['value'] <= 0) {
                  $message = $this->__('Please enter a positive integer number in the left rate field.');
              } else {
                  $message = $this->__('Please enter a positive number in the right rate field.');
              }
        } elseif ($post['direction'] == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
                  && ((float) $post['value'] <= 0 || (int) $post['equal_value'] <= 0)) {
              if ((int) $post['equal_value'] <= 0) {
                  $message = $this->__('Please enter a positive integer number in the right rate field.');
              } else {
                  $message = $this->__('Please enter a positive number in the left rate field.');
              }
        } elseif ($post['direction'] == Imaginato_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_COUPON
            && ((float) $post['value'] <= 0 || empty($rule_id))) {
            if (empty($rule_id)) {
                $message = $this->__('Please select a correct coupon in the right rate field.');
            } else {
                $message = $this->__('Please enter a positive integer number in the left rate field.');
            }
        } else {
            $rate       = $this->_initRate();
            $isRateUnique = $rate->getIsRateUnique(
                $post['website_id'],
                $post['customer_group_id'],
                $post['direction'],
                $rule_id?$rule_id:0
            );

            if (!$isRateUnique) {
                $message = $this->__('Rate with the same website, customer group and direction or covering rate already exists.');
            }
        }

        if ($message) {
            $this->_getSession()->addError($message);
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }
}
