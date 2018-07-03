<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('skusrule/rule_product_price')} (
  rule_id int(10) unsigned NOT NULL auto_increment,
  name varchar(200) NOT NULL default '',
  skus text NOT NULL default '',
  website_id int(2) unsigned NOT NULL default 0,
  percent decimal(12,4) NOT NULL default 0,
  created_at timestamp NULL,
  updated_at timestamp NULL,
  PRIMARY KEY(`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('skusrule/rule_product_sku')} (
  rule_id int(10) unsigned NOT NULL auto_increment,
  sku varchar(100) NOT NULL default '',
  PRIMARY KEY(`rule_id`, `sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();