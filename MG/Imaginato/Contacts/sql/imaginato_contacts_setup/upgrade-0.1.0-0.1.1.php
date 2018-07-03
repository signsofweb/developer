<?php
 
$installer = $this;
 
$installer->startSetup();

$table = $installer->getTable('imaginato_contacts/enquerytype');
 
$installer->getConnection()
    ->addColumn(
        $table,
        'enabled',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => false,
            'default' => 0,
            'comment' => 'Enabled'
        )
    );
$installer->getConnection()
    ->addColumn(
        $table,
        'created_at',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'comment' => 'Created time'
        )
    );
$installer->getConnection()
    ->addColumn(
        $table,
        'updated_at',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'comment' => 'Last updated time'
        )
    );

$table = $installer->getTable('imaginato_contacts/enqueries');
 
$installer->getConnection()
    ->addColumn(
        $table,
        'enabled',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => false,
            'default' => 0,
            'comment' => 'Enabled'
        )
    );
$installer->getConnection()
    ->addColumn(
        $table,
        'created_at',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'comment' => 'Created time'
        )
    );
$installer->getConnection()
    ->addColumn(
        $table,
        'updated_at',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'comment' => 'Last updated time'
        )
    );

$installer->endSetup();