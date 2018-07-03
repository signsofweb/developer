<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'), 'sync_bergen_status', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'default' => '0',
        'comment' => 'Sync Bergen Status',
    )
);

$installer->endSetup();

