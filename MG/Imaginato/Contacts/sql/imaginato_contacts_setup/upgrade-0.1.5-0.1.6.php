<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('imaginato_contacts/enquerytypestoreview')} (
  enquery_type_id int(10) NOT NULL default 0,
  store_id int(2) NOT NULL default 0,
  PRIMARY KEY(`enquery_type_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('imaginato_contacts/enqueriesstoreview')} (
  enquery_id int(10) NOT NULL default 0,
  store_id int(2) NOT NULL default 0,
  PRIMARY KEY(`enquery_id`, `store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();