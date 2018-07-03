<?php
 class Imaginato_Reward_Block_Coupon_List extends Mage_Core_Block_Template
 {
     protected $_collection = null;
     protected $_template = 'reward/coupon/list.phtml';

     public function getCoupon()
     {
         if (0 == $this->_getCollection()->getSize()) {
             return false;
         }
         return $this->_collection;
     }

     protected function getStatus($item){
         $now = Varien_Date::formatDate(time());
         if($item->getData('usage_per_customer')!=0 && $item->getData('usage_limit') == $item->getData('usage_per_customer')){
             return 'Used';
         }elseif(!empty($item->getData('coupon_expiration_date')) && $item->getData('coupon_expiration_date') <= $now){
             return 'Expired';
         }elseif($item->getData('is_active')=='0'){
             return 'Inactive';
         }else{
             return 'Valid';
         }
     }

     protected function _getCollection()
     {
         if (!$this->_collection) {
             $session = Mage::getSingleton('customer/session');
             $customerId = $session->getCustomerId();
             $this->_collection = Mage::getModel('imaginato_reward/reward_coupon_history')->getCollection()
                 ->addFieldToFilter('customer_id',$customerId)
                 ->setOrder('created_at','Desc');
             $this->_collection->getSelect()
                 ->joinInner(
                     array('coupon' => $this->_collection->getTable('salesrule/coupon')),
                     'coupon.coupon_id=main_table.coupon_id',
                     array('coupon_code' => 'code', 'coupon_expiration_date' => 'expiration_date','usage_limit','usage_per_customer')
                 )
                 ->joinInner(
                     array('rule' => $this->_collection->getTable('salesrule/rule')),
                     'rule.rule_id=main_table.rule_id',
                     array('rule_name' => 'name','is_active','description')
                 )
                 ->joinInner(
                     array('customer_group' => $this->_collection->getTable('salesrule/customer_group')),
                     'customer_group.rule_id=main_table.rule_id',
                     null
                 )
                 ->joinInner(
                     array('website' => $this->_collection->getTable('salesrule/website')),
                     'website.rule_id=main_table.rule_id',
                     null
                 )
                 ->where('customer_group.customer_group_id=?',$session->getCustomer()->getGroupId())
                 ->where('website.website_id=?',Mage::app()->getWebsite()->getId());
         }
         return $this->_collection;
     }

     protected function _prepareLayout()
     {
         $pager = $this->getLayout()->createBlock('page/html_pager', 'reward.coupon.pager')
             ->setCollection($this->_getCollection())->setIsOutputRequired(false)
         ;
         $this->setChild('pager', $pager);
         return parent::_prepareLayout();
     }
 }