<?php

class Imaginato_Reward_Block_Adminhtml_Customer_Reward_Staging extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_customer_reward_staging';
        $this->_blockGroup = 'imaginato_reward';
        $this->_headerText = Mage::helper('customer')->__('Manage Staging Rewards');
        parent::__construct();
        $this->_removeButton('add');
    }
}
