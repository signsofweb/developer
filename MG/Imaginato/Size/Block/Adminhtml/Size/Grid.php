<?php

class Imaginato_Size_Block_Adminhtml_Size_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sizeGrid');
        $this->setDefaultSort('size_identifier');
        $this->setDefaultDir('ASC');
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('block_id' => $row->getId()));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('size/block')->getCollection();
        /* @var $collection Imaginato_Size_Model_Resource_Block_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('title', array(
            'header' => Mage::helper('size')->__('Title'),
            'align'  => 'left',
            'index'  => 'title',
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('size')->__('Identifier'),
            'align'  => 'left',
            'index'  => 'identifier'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'     => Mage::helper('size')->__('Store View'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_all'  => true,
                'store_view' => true,
                'sortable'   => false,
                'filter_condition_callback'
                             => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_active', array(
            'header'  => Mage::helper('size')->__('Status'),
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => array(
                0 => Mage::helper('size')->__('Disabled'),
                1 => Mage::helper('size')->__('Enabled')
            ),
        ));

        $this->addColumn('creation_time', array(
            'header' => Mage::helper('size')->__('Date Created'),
            'index'  => 'creation_time',
            'type'   => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header' => Mage::helper('size')->__('Last Modified'),
            'index'  => 'update_time',
            'type'   => 'datetime',
        ));

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

}
