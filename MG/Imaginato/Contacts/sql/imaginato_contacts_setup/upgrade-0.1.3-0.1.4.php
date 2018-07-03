<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()
	->addColumn(
		$installer->getTable('imaginato_contacts/contacts'),
		'order_number',
		array(
			'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => '',
            'comment' => 'Customer\'s order number'
		)
	);

$installer->getConnection()
	->addColumn(
		$installer->getTable('imaginato_contacts/contacts'),
		'file',
		array(
			'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => '',
            'comment' => 'Customer\'s contact file'
		)
	);

$installer->endSetup();