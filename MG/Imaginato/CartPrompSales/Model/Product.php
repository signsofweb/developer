<?php

class Imaginato_CartPrompSales_Model_Product extends Mage_Core_Model_Abstract
{
    protected $_mainTable;
    protected $_productStorePosition;

    protected function _construct()
    {
        $this->_init('cartprompsales/product');
        $this->_mainTable = $this->getResource()->getMainTable();
    }

    public function savePrompProducts($store_id,$products)
    {
        /**
         * Example re-save product
         */
        if ($store_id === null || $products === null) {
            return $this;
        }
        $this->setAffectedProduct($products);

        /**
         * old category-product relationships
         */
        $oldProducts = $this->getProductsPosition($store_id);

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');

        /**
         * Delete products from category
         */
        if (!empty($delete)) {
            $cond = array(
                'product_id IN(?)' => array_keys($delete),
                'store_id=?' => $store_id
            );
            $adapter->delete($this->_mainTable, $cond);
        }

        /**
         * Add products to category
         */
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $productId => $position) {
                $data[] = array(
                    'store_id' => (int)$store_id,
                    'product_id'  => (int)$productId,
                    'position'    => (int)$position
                );
            }
            $adapter->insertMultiple($this->_mainTable, $data);
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = array(
                    'store_id = ?'=> (int)$store_id,
                    'product_id = ?' => (int)$productId
                );
                $bind  = array('position' => (int)$position);
                $adapter->update($this->_mainTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            /**
             * Setting affected products to category for third party engine index refresh
             */
            $productIds = array_keys($insert + $delete + $update);
            $this->setAffectedProductIds($productIds);
        }
        return $this;
    }

    public function getProductsPosition($store_id)
    {
        if(!isset($this->_productStorePosition[$store_id])){
            $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
            $select = $readAdapter->select()
                ->from($this->_mainTable, array('product_id', 'position'))
                ->where('store_id = :store_id');
            $bind = array('store_id' => (int)$store_id);

            $this->_productStorePosition[$store_id] = $readAdapter->fetchPairs($select, $bind);
        }
        return $this->_productStorePosition[$store_id];
    }
}
