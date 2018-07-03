<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Log
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'imaginato_security/admin_url'
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('imaginato_security/adminlog'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_security/adminlog'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Log ID')
        ->addColumn('log_type', Varien_Db_Ddl_Table::TYPE_TEXT, 12, array(
        ), 'Log Type')
        ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
            'nullable' => true,
            'default' => null,
        ), 'Session ID')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default' => '0',
        ), 'Customer ID')
        ->addColumn('user_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'User Name')
        ->addColumn('user_email', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'User Email')
        ->addColumn('ip', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'IP')
        ->addColumn('url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'URL')
        ->addColumn('http_referer', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'HTTP Referrer')
        ->addColumn('http_user_agent', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'HTTP User-Agent')
        ->addColumn('http_accept_language', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'HTTP Accept-Language')
        ->addColumn('visit_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Visit Time')
        ->setComment('Log Admin Url Table');
    $installer->getConnection()->createTable($table);
}

$eavConfig = Mage::getSingleton('eav/config');
$store = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
foreach (array('firstname','lastname') as $attributeCode) {
    $attribute = $eavConfig->getAttribute('customer', $attributeCode);
    $attribute->setWebsite($store->getWebsite());
//    $attribute->setData('frontend_class','validate-alphanum-with-spaces');
    $attribute->setData('validate_rules',array(
        'max_text_length'   => 25,
        'min_text_length'   => 1
    ));
    $attribute->save();

    $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
    $attribute->setWebsite($store->getWebsite());
//    $attribute->setData('frontend_class',' validate-alphanum-with-spaces');
    $attribute->setData('validate_rules',array(
        'max_text_length'   => 25,
        'min_text_length'   => 1
    ));
    $attribute->save();
}

$installer->endSetup();
