<?php

class Imaginato_Reward_Model_Resource_Reward_Staging_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('imaginato_reward/reward_staging');
    }

    public function addCustomerEmail()
    {
        if ($this->getFlag('customer_added')) {
            return $this;
        }

        $customer = Mage::getModel('customer/customer');

        $this->getSelect()
            ->joinInner(
                array('ce' => $customer->getAttribute('email')->getBackend()->getTable()),
                'ce.entity_id=main_table.customer_id',
                array('customer_email' => 'email')
            );

        $this->setFlag('customer_added', true);
        return $this;
    }

    public function addOrderInfo()
    {
        if ($this->getFlag('order_added')) {
            return $this;
        }

        $this->getSelect()
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                'order.entity_id = main_table.parent_id',
                array('order_status' => 'status','order_created_at'=>'created_at')
            );

        $this->setFlag('order_added', true);
        return $this;
    }
}
