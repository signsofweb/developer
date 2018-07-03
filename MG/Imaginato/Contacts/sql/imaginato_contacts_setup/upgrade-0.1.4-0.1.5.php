<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('imaginato_contacts/contacts')}` CHANGE `subject` `subject` int(10) NOT NULL default 0
");
$installer->endSetup();