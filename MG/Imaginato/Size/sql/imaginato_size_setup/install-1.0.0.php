<?php


/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'size/block'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('size/block'))
    ->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Chart ID')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
    ), 'Chart Title')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
    ), 'Chart String Identifier')
    ->addColumn('content', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(), 'Block Content')
    ->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Block Creation Time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Block Modification Time')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '1',
    ), 'Is Chart Active')
    ->setComment('Size Chart Block Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'size/block_store'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('size/block_store'))
    ->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'primary'  => true,
    ), 'Chart ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Store ID')
    ->addIndex($installer->getIdxName('size/block_store', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('size/block_store', 'block_id', 'size/block', 'block_id'),
        'block_id', $installer->getTable('size/block'), 'block_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('size/block_store', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Size Chart To Store Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'size/block_product'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('size/block_product'))
    ->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        'default'  => '0',
    ), 'Chart ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        'default'  => '0',
    ), 'Product ID')
    ->addIndex($this->getIdxName('size/block_product', array('product_id')), array('product_id'))
    ->addForeignKey($this->getFkName('size/block_product', 'block_id', 'size/block', 'block_id'), 'block_id', $this->getTable('size/block'), 'block_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($this->getFkName('size/block_product', 'product_id', 'catalog/product', 'entity_id'), 'product_id', $this->getTable('catalog/product'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Chart to Product Linkage Table');
$this->getConnection()->createTable($table);

$installer->endSetup();