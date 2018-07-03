<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('imaginato_contacts/contacts')} (
  entity_id int(10) unsigned NOT NULL auto_increment,
  name varchar(200) NOT NULL default '',
  store_id int(2) NOT NULL default 0,
  subject varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  comment text NOT NULL default '',
  created_at timestamp NULL,
  updated_at timestamp NULL,
  PRIMARY KEY(`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();