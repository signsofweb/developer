<?php
$installer = $this;

$configTable = $installer->getTable('core/config_data');

$country_path = Innoexts_CustomerLocator_Model_Config::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_COUNTRY_ID;
$region_path = Innoexts_CustomerLocator_Model_Config::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_REGION_ID;
$postcode_path = Innoexts_CustomerLocator_Model_Config::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_POSTCODE;
$city_path = Innoexts_CustomerLocator_Model_Config::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_CITY;

$installer->run("UPDATE `{$configTable}` SET `value` = '' WHERE `path` = '{$country_path}'");
$installer->run("UPDATE `{$configTable}` SET `value` = '' WHERE `path` = '{$region_path}'");
$installer->run("UPDATE `{$configTable}` SET `value` = '' WHERE `path` = '{$postcode_path}'");
$installer->run("UPDATE `{$configTable}` SET `value` = '' WHERE `path` = '{$city_path}'");