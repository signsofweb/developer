<?php

class Imaginato_Size_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('related_size_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', array('_current' => true));
    }

    /**
     * Get products by current chart
     *
     * @return array
     */
    public function getRelatedProductsByChart()
    {
        $chartId = $this->getChart()->getId();
        return $this->getChart()->getRelatedProductsByChart($chartId);
    }

    /**
     * @return Imaginato_Size_Model_Block
     */
    public function getChart()
    {
        return Mage::registry('size_block');
    }

    protected function _beforeToHtml()
    {
        $this->_prepareGrid();
        return $this;
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if (is_null($products)) {
            $products = $this->getChart()->getRelatedProducts();
            return array_keys($products);
        }
        return $products;
    }

    protected function _prepareCollection()
    {
        $store_id = $this->_getStore()->getId();

        /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('season')
            ->addAttributeToSelect('type_id')
            ->addStoreFilter($store_id);
        $collection->joinField('chart',
            'size/block_product',
            'block_id',
            'product_id=entity_id',
            'block_id=' . (int)$this->getRequest()->getParam('id', 0),
            'left');
        $collection->joinAttribute(
            'price',
            'catalog_product/price',
            'entity_id',
            null,
            'left',
            $store_id
        );
        $this->setCollection($collection);

        if ($this->getChart()->getId()) {
            $productIds = $this->_getSelectedProducts();
            if (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            }
        }

        return parent::_prepareCollection();
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _getStoreIds()
    {
        $storeIds = $this->getChart()->getStoreId();

        return $storeIds;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'   => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width'    => '60',
            'index'    => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index'  => 'name'
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width'  => '80',
            'index'  => 'sku'
        ));

        $this->addColumn('type',
            array(
                'header'  => Mage::helper('catalog')->__('Type'),
                'index'   => 'type_id',
                'type'    => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));

        $entityType = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY);
        $attribute = Mage::getModel('eav/config')->getAttribute($entityType->getEntityTypeId(), 'season');
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($attribute->getId());
        $optionCollection->getSelect()->joinLeft(
            array('optionVals' => $optionCollection->getTable('eav/attribute_option_value')),
            'optionVals.option_id = main_table.option_id and optionVals.store_id = 0',
            array('value')
        );
        $optionArray = array();
        foreach ($optionCollection->toOptionArray() as &$option) {
            $optionArray[$option['value']] = $option['label'];
        }
        $this->addColumn('season', array(
            'header'  => Mage::helper('catalog')->__('Season'),
            'index'   => 'season',
            'type'    => 'options',
            'options' => $optionArray
        ));
        unset($option);
        $typeattribute = Mage::getModel('eav/config')->getAttribute($entityType->getEntityTypeId(), 'type');
        $typeoptionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($typeattribute->getId());
        $typeoptionCollection->getSelect()->joinLeft(
            array('optionVals' => $typeoptionCollection->getTable('eav/attribute_option_value')),
            'optionVals.option_id = main_table.option_id and optionVals.store_id = 0',
            array('value')
        );
        $typeoptionArray = array();
        foreach ($typeoptionCollection->toOptionArray() as &$option) {
            $typeoptionArray[$option['value']] = $option['label'];
        }
        $this->addColumn('sku_type', array(
            'header'  => Mage::helper('catalog')->__('Sku Type'),
            'index'   => 'type',
            'type'    => 'options',
            'options' => $typeoptionArray
        ));
        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'width'         => '1',
            'currency_code' => (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        return parent::_prepareColumns();
    }

}

