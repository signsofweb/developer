<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run("
alter table {$this->getTable('cs_enquery_type')} MODIFY column short_order smallint(2) NOT NULL default 0;
alter table {$this->getTable('cs_enqueries')} MODIFY column short_order smallint(2) NOT NULL default 0;");
 
$installer->endSetup();