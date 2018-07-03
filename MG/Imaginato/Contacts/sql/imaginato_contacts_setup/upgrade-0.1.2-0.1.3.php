<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->getConnection()
	->addColumn(
		$installer->getTable('imaginato_contacts/enqueries'),
		'email',
		array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => null,
            'comment' => 'Email address to receive contact'
        )
	);
 
$installer->endSetup();