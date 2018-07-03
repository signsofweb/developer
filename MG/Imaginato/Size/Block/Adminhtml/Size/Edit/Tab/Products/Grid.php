<?php

class Imaginato_Size_Block_Adminhtml_Size_Edit_Tab_Products_Grid
    extends Imaginato_Size_Block_Adminhtml_Product_Grid
{

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', array('_current' => true));
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('related_size_products_grid');
        $this->setVarNameFilter('related_size_products_filter');
        if ($this->getChart()->getId()) {
            $this->setDefaultFilter(array('in_chart' => 1));
        }
    }

    /**
     * Create grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_chart', array(
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_chart',
            'values'           => $this->getSelectedProducts(),
            'align'            => 'center',
            'index'            => 'entity_id'
        ));

        parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_chart') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Get selected products ids for in chart flag
     *
     * @return array
     */
    protected function getSelectedProducts()
    {
        $charts = $this->getSelectedSizeProducts();
        if (is_null($charts)) {
            $charts = $this->getRelatedProductsByChart();
        }
        return $charts;
    }

    protected function _prepareMassaction()
    {
        return $this;
    }
}
