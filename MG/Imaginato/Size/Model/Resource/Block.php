<?php

class Imaginato_Size_Model_Resource_Block extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Chart products table name
     *
     * @var string
     */
    protected $_chartProductTable;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $resource = Mage::getSingleton('core/resource');
        $this->_chartProductTable = $resource->getTableName('size/block_product');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Imaginato_Size_Model_Resource_Block
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Get all associated products
     *
     * @param Imaginato_Size_Model_Block $chart
     * @return array
     */
    public function getRelatedProducts($chart)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_chartProductTable, array('product_id'))
            ->where('block_id = :block_id');
        $bind = array('block_id' => (int)$chart->getId());

        return $this->_getWriteAdapter()->fetchPairs($select, $bind);
    }

    /**
     * Get products that associated to chart id
     *
     * @param int $chartId
     * @return array
     */
    public function getRelatedProductsByChart($chartId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_chartProductTable, array('product_id'))
            ->where('block_id = ?', $chartId);
        return $adapter->fetchCol($select);
    }

    /**
     * Get chart that associated to product id
     *
     * @param int $productId
     * @return string
     */
    public function getChartByRelatedProductId($productId)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_chartProductTable, array('block_id'))
            ->where('product_id = ?', $productId);
        return $adapter->fetchOne($select);
    }

    /**
     * Bind specified products to chart by chart id
     *
     * @param int $chartId
     * @param array $products
     * @return Imaginato_Size_Model_Resource_Block
     */
    public function saveRelatedProductsChart($chartId, $products)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($products as $productId) {
            $adapter->insertOnDuplicate(
                $this->_chartProductTable,
                array('product_id' => $productId, 'block_id' => $chartId),
                array('block_id')
            );
        }

        if (empty($products)) {
            $products = array(0);
        }

        $adapter->delete($this->_chartProductTable,
            array('block_id = ?' => $chartId, 'product_id NOT IN (?)' => $products)
        );
        return $this;
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('size/block', 'block_id');
    }

    /**
     * Process block data before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Imaginato_Size_Model_Resource_Block
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = array(
            'block_id = ?' => (int)$object->getId(),
        );

        $this->_getWriteAdapter()->delete($this->getTable('size/block_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations before object save
     *
     * @param Imaginato_Size_Model_Block $object
     * @return Imaginato_Size_Model_Resource_Block
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$this->getIsUniqueBlockToStores($object)) {
            Mage::throwException(Mage::helper('size')->__('A chart identifier with the same properties already exists in the selected store.'));
        }

        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
        return $this;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniqueBlockToStores(Mage_Core_Model_Abstract $object)
    {
        if (Mage::app()->isSingleStoreMode()) {
            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getReadAdapter()->select()
            ->from(array('cb' => $this->getMainTable()))
            ->join(
                array('cbs' => $this->getTable('size/block_store')),
                'cb.block_id = cbs.block_id',
                array()
            )->where('cb.identifier = ?', $object->getData('identifier'))
            ->where('cbs.store_id IN (?)', $stores);

        if ($object->getId()) {
            $select->where('cb.block_id <> ?', $object->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Perform operations after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Imaginato_Size_Model_Resource_Block
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table = $this->getTable('size/block_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'block_id = ?'    => (int)$object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array(
                    'block_id' => (int)$object->getId(),
                    'store_id' => (int)$storeId
                );
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);

    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getTable('size/block_store'), 'store_id')
            ->where('block_id = :block_id');

        $binds = array(
            ':block_id' => (int)$id
        );

        return $adapter->fetchCol($select, $binds);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Imaginato_Size_Model_Resource_Block
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Imaginato_Size_Model_Block $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = array(
                (int)$object->getStoreId(),
                Mage_Core_Model_App::ADMIN_STORE_ID,
            );

            $select->join(
                array('cbs' => $this->getTable('size/block_store')),
                $this->getMainTable() . '.block_id = cbs.block_id',
                array('store_id')
            )->where('is_active = ?', 1)
                ->where('cbs.store_id in (?) ', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }
}