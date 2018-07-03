<?php
$installer = $this;
$installer->startSetup();

if (!$installer->tableExists($installer->getTable('imaginato_specialprice/record'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_specialprice/record'))
        ->addColumn('record_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Record Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
        ->addColumn('discount_rate', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
            'nullable'  => false,
        ), 'discount_rate')
        ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'From Date')
        ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'To Date')
        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
        ), 'Created At')
        ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
        ), 'Updated At')
        ->setComment('Special Price Update Recore');
    $installer->getConnection()->createTable($table);
}

if (!$installer->tableExists($installer->getTable('imaginato_specialprice/record_product'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_specialprice/record_product'))
        ->addColumn('record_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Record Id')
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Product Id')
        ->addIndex($installer->getIdxName('imaginato_specialprice/record_product', array('product_id')),
            array('product_id'))
        ->addForeignKey(
            $installer->getFkName(
                'imaginato_specialprice/record_product',
                'record_id',
                'imaginato_specialprice/record',
                'record_id'
            ),
            'record_id', $installer->getTable('imaginato_specialprice/record'), 'record_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName(
                'imaginato_specialprice/record_product',
                'product_id',
                'catalog/product',
                'entity_id'
            ),
            'product_id', $installer->getTable('catalog/product'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Special Price Update Recore Product');
    $installer->getConnection()->createTable($table);
}

if (!$installer->tableExists($installer->getTable('imaginato_specialprice/record_website'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_specialprice/record_website'))
        ->addColumn('record_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Record Id')
        ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Website Id')
        ->addIndex($installer->getIdxName('imaginato_specialprice/record_website', array('website_id')),
            array('website_id'))
        ->addForeignKey(
            $installer->getFkName(
                'imaginato_specialprice/record_website',
                'record_id',
                'imaginato_specialprice/record',
                'record_id'
            ),
            'record_id', $installer->getTable('imaginato_specialprice/record'), 'record_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey(
            $installer->getFkName(
                'imaginato_specialprice/record_website',
                'website_id',
                'core/website',
                'website_id'
            ),
            'website_id', $installer->getTable('core/website'), 'website_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Special Price Update Recore Website');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();