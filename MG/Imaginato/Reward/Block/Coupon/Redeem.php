<?php
 class Imaginato_Reward_Block_Coupon_Redeem extends Mage_Core_Block_Template
 {
     protected $_template = 'reward/coupon/redeem.phtml';

     public function getRule()
     {
         $collection = Mage::getModel('enterprise_reward/reward_rate')->getCollection()
             ->addFieldToFilter('direction',Imaginato_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_COUPON)
             ->setOrder('points','DESC');
         $collection->getSelect()
             ->joinInner(
                 array('rule' => $collection->getTable('salesrule/rule')),
                 'rule.rule_id=main_table.coupon'
             )
             ->joinInner(
                 array('customer_group' => $collection->getTable('salesrule/customer_group')),
                 'customer_group.rule_id=rule.rule_id',
                 null
             )
             ->joinInner(
                 array('website' => $collection->getTable('salesrule/website')),
                 'website.rule_id=rule.rule_id',
                 null
             );
         $now = Mage::getModel('core/date')->date('Y-m-d');
         $group_id = Mage::getSingleton('customer/session')->getCustomer()->getGroupId();
         $website_id = Mage::app()->getWebsite()->getId();
         $collection->getSelect()
             ->where('main_table.customer_group_id in(?)',array($group_id,0))
             ->where('main_table.website_id in(?)',array($website_id,0))
             ->where('rule.coupon_type = ?', Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
             ->where('rule.is_active = ?', 1)
             ->where('rule.use_auto_generation = ?', 1)
             ->where('rule.from_date is null or rule.from_date <= ?', $now)
             ->where('rule.to_date is null or rule.to_date >= ?', $now)
             ->where('customer_group.customer_group_id=?',$group_id)
             ->where('website.website_id=?',$website_id);
         return $collection;
     }
 }