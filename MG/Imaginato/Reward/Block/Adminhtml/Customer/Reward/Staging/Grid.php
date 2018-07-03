<?php

class Imaginato_Reward_Block_Adminhtml_Customer_Reward_Staging_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerRewardStaingGrid');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('imaginato_reward/reward_staging')->getCollection()
            ->addCustomerEmail()
            ->addOrderInfo()
            ->setOrder('staging_id', 'dasc');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_email', array(
            'type'     => 'text',
            'index'    => 'customer_email',
            'header'   => Mage::helper('imaginato_reward')->__('Customer_Email'),
            'sortable' => false,
            'width'    => 1,
            'filter_index' => 'ce.email'
        ));

        $this->addColumn('website', array(
            'type'     => 'options',
            'options'  => Mage::getModel('enterprise_reward/source_website')->toOptionArray(false),
            'index'    => 'website_id',
            'header'   => Mage::helper('imaginato_reward')->__('Website'),
            'sortable' => false,
            'filter_index' => 'main_table.website_id'
        ));

        $this->addColumn('status', array(
            'index'    => 'status',
            'type'     => 'options',
            'options'  => Mage::getModel('imaginato_reward/reward_staging')->getStatusOptionArray(),
            'header'   => Mage::helper('imaginato_reward')->__('Status'),
            'sortable' => false,
            'filter_index' => 'main_table.status'
        ));

        $this->addColumn('points_delta', array(
            'type'     => 'number',
            'index'    => 'points_delta',
            'header'   => Mage::helper('imaginato_reward')->__('Points'),
            'sortable' => false,
            'filter'   => false,
            'show_number_sign' => true,
            'width'    => 1,
        ));

        $this->addColumn('created_at', array(
            'type'     => 'datetime',
            'index'    => 'created_at',
            'header'   => Mage::helper('imaginato_reward')->__('Created At'),
            'sortable' => false,
            'align'    => 'left',
            'html_decorators' => 'nobr',
            'filter_index' => 'main_table.created_at'
        ));

        $this->addColumn('increment_id', array(
            'index'    => 'increment_id',
            'type'     => 'text',
            'header'   => Mage::helper('imaginato_reward')->__('Increment Id'),
            'sortable' => false,
            'filter_index' => 'main_table.increment_id'
        ));

        $this->addColumn('order_status', array(
            'header' => Mage::helper('sales')->__('Order Status'),
            'index' => 'order_status',
            'type'  => 'options',
            'width' => '70px',
            'sortable' => false,
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter_index' => 'order.status'
        ));

        $this->addColumn('order_created_at', array(
            'type'     => 'datetime',
            'index'    => 'order_created_at',
            'header'   => Mage::helper('imaginato_reward')->__('Order Created At'),
            'sortable' => false,
            'align'    => 'left',
            'html_decorators' => 'nobr',
            'filter_index' => 'order.created_at'
        ));

        $this->addColumn('action', array(
            'header'    =>  ' ',
            'filter'    =>  false,
            'sortable'  =>  false,
            'width'     => '200px',
            'renderer'  =>  'imaginato_reward/reward_renderer_action'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('staging_id');
        $this->getMassactionBlock()->setFormFieldName('staging_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('agree', array(
            'label'=> Mage::helper('imaginato_reward')->__('Agree'),
            'url'  => $this->getUrl('*/*/massAgree'),
        ));
        $this->getMassactionBlock()->addItem('refuse', array(
            'label'=> Mage::helper('imaginato_reward')->__('Refuse'),
            'url'  => $this->getUrl('*/*/massRefuse'),
        ));
        return $this;
    }
}
