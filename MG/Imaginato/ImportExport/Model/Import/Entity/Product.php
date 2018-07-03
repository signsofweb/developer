<?php

/**
 * Export product
 *
 * @category   Imaginato
 * @package    Imaginato_ImportExport
 */
class Imaginato_ImportExport_Model_Import_Entity_Product extends Enterprise_ImportExport_Model_Import_Entity_Product
{
    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array(
        '_store', '_attribute_set', '_type', self::COL_CATEGORY, self::COL_ROOT_CATEGORY, '_product_websites',
        '_stock_warehouse',
        '_tier_price_website', '_tier_price_customer_group', '_tier_price_qty', '_tier_price_price',
        '_links_related_sku', '_group_price_website', '_group_price_customer_group', '_group_price_price',
        '_links_related_position', '_links_crosssell_sku', '_links_crosssell_position', '_links_upsell_sku',
        '_links_upsell_position', '_custom_option_store', '_custom_option_type', '_custom_option_title',
        '_custom_option_is_required', '_custom_option_price', '_custom_option_sku', '_custom_option_max_characters',
        '_custom_option_sort_order', '_custom_option_file_extension', '_custom_option_image_size_x',
        '_custom_option_image_size_y', '_custom_option_row_title', '_custom_option_row_price',
        '_custom_option_row_sku', '_custom_option_row_sort', '_media_attribute_id', '_media_image', '_media_lable',
        '_media_position', '_media_is_disabled'
    );

    /**
     * Warehouse code to stock_id
     *
     * @var array
     */
    protected $_warehouseCodeToStockId = array();

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_initWarehouses();
    }


    protected function _initWarehouses()
    {
        $warehouses = Mage::helper('warehouse')->getWarehouses();
        foreach ($warehouses as $warehouse) {
            $this->_warehouseCodeToStockId[$warehouse->getCode()] = $warehouse->getStockId();
        }
        return $this;
    }

    /**
     * Stock item saving.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveStockItem()
    {
        $defaultStockData = array(
            'manage_stock'                  => 1,
            'use_config_manage_stock'       => 1,
            'qty'                           => 0,
            'min_qty'                       => 0,
            'use_config_min_qty'            => 1,
            'min_sale_qty'                  => 1,
            'use_config_min_sale_qty'       => 1,
            'max_sale_qty'                  => 10000,
            'use_config_max_sale_qty'       => 1,
            'is_qty_decimal'                => 0,
            'backorders'                    => 0,
            'use_config_backorders'         => 1,
            'notify_stock_qty'              => 1,
            'use_config_notify_stock_qty'   => 1,
            'enable_qty_increments'         => 0,
            'use_config_enable_qty_inc'     => 1,
            'qty_increments'                => 0,
            'use_config_qty_increments'     => 1,
            'is_in_stock'                   => 0,
            'low_stock_date'                => null,
            'stock_status_changed_auto'     => 0,
            'is_decimal_divided'            => 0
        );

        $entityTable = $this->getResourceModel('cataloginventory/stock_item')->getMainTable();
        $helper      = $this->getHelper('catalogInventory');

        while ($bunch = $this->getNextBunch()) {
            $stockData = array();

            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                $this->_filterRowData($rowData);
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);
                if (self::SCOPE_DEFAULT == $rowScope) {
                    $rowSku = $rowData[self::COL_SKU];
                }
                if (null === $rowSku) {
                    continue;
                }
                if (empty($rowData['_stock_warehouse'])){
                    continue;
                }

                $row = array();
                $row['product_id'] = $this->_newSku[$rowSku]['entity_id'];
                $row['stock_id'] = $this->_warehouseCodeToStockId[$rowData['_stock_warehouse']]?$this->_warehouseCodeToStockId[$rowData['_stock_warehouse']]:1;

                /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $stockItem = $this->getModel('cataloginventory/stock_item');
                $stockItem->loadByProduct($row['product_id']);
                $existStockData = $stockItem->getData();

                $row = array_merge(
                    $defaultStockData,
                    array_intersect_key($existStockData, $defaultStockData),
                    array_intersect_key($rowData, $defaultStockData),
                    $row
                );

                $stockItem->setData($row);
                unset($row);
                if ($helper->isQty($this->_newSku[$rowSku]['type_id'])) {
                    if ($stockItem->verifyNotification()) {
                        $stockItem->setLowStockDate(Mage::app()->getLocale()
                            ->date(null, null, null, false)
                            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                        );
                    }
                    $stockItem->setStockStatusChangedAutomatically((int) !$stockItem->verifyStock());
                } else {
                    $stockItem->setQty(0);
                }
                $stockData[] = $stockItem->unsetOldData()->getData();
            }

            // Insert rows
            if ($stockData) {
                $this->_connection->insertOnDuplicate($entityTable, $stockData);
                Mage::dispatchEvent('catalog_product_import_stock_finish_after', array('stock' => $stockData));
            }
        }
        return $this;
    }

}
