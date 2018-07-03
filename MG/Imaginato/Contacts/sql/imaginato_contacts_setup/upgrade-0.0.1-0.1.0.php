<?php
 
$installer = $this;
$connection = $installer->getConnection();
 
$installer->startSetup();

$table = $installer->getTable('imaginato_contacts/contacts');
 
$installer->getConnection()
    ->addColumn(
        $table,
        'order_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default' => null,
            'comment' => 'Customer\'s order id'
        )
    );
$installer->getConnection()
    ->addColumn(
        $table,
        'file',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => null,
            'comment' => 'Upload file'
        )
    );

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('cs_enquery_type')} (
  entity_id int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  short_order int(2) NOT NULL default 0,
  PRIMARY KEY(`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('cs_enqueries')} (
  entity_id int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  short_order int(2) NOT NULL default 0,
  enquery_type_id int(10) NOT NULL default 0,
  PRIMARY KEY(`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
 
$installer->endSetup();