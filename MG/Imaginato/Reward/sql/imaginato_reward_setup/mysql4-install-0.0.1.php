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
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Imaginato_Reward_Model_Resource_Setup */
$installer->startSetup();


if (!$installer->getConnection()->isTableExists($installer->getTable('imaginato_reward/reward_staging'))) {
    /**
     * Create table 'imaginato_reward/reward_staging'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_reward/reward_staging'))

        ->addColumn('staging_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Staging Id')

        ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'Website Id')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
        ), 'Store Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer Id')

        ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
        ), 'Parent Id')
        ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'default'   => '0',
        ), 'Status')

        ->addColumn('rate_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'nullable'  => false,
        ), 'Rate Data')
        ->addColumn('points_delta', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
            'default'   => '0',
        ), 'Points Delta')

        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
        ), 'Created At')
        ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'nullable'  => true,
        ), 'Comment')

        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging', array('website_id')),
            array('website_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging', array('store_id')),
            array('store_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging', array('customer_id')),
            array('customer_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging', array('parent_id')),
            array('parent_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging', array('status')),
            array('status'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_staging',array('increment_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))

        ->addForeignKey($installer->getFkName('imaginato_reward/reward_staging', 'store_id', 'core/store', 'store_id'),
            'store_id', $installer->getTable('core/store'), 'store_id',
            Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('imaginato_reward/reward_staging', 'website_id', 'core/website', 'website_id'),
            'website_id', $installer->getTable('core/website'), 'website_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('imaginato_reward/reward_staging', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id', $installer->getTable('customer/entity'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('imaginato_reward/reward_staging', 'parent_id', 'sales/order', 'entity_id'),
            'parent_id', $installer->getTable('sales/order'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

        ->setComment('Imaginato Reward Staging');
    $installer->getConnection()->createTable($table);
}
$rateTable = $installer->getTable('enterprise_reward/reward_rate');
if (!$installer->getConnection()->tableColumnExists($rateTable, 'coupon')) {
    $installer->getConnection()->addColumn(
        $rateTable,
        'coupon',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default'   => '0',
            'comment'   => 'Rule Id'
        )
    );
    $installer->getConnection()->dropIndex($rateTable,$installer->getIdxName('enterprise_reward/reward_rate', array('website_id', 'customer_group_id', 'direction'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE));
    $installer->getConnection()->addIndex($rateTable,$installer->getIdxName('enterprise_reward/reward_rate', array('website_id', 'customer_group_id', 'direction', 'coupon'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('website_id', 'customer_group_id', 'direction', 'coupon'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
}


if (!$installer->getConnection()->isTableExists($installer->getTable('imaginato_reward/reward_coupon_history'))) {
    /**
     * Create table 'imaginato_reward/reward_coupon_history'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('imaginato_reward/reward_coupon_history'))

        ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'History Id')

        ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Coupon Id')
        ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Rule Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Customer Id')

        ->addColumn('points', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'Points')

        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
        ), 'Created At')

        ->addIndex($installer->getIdxName('imaginato_reward/reward_coupon_history', array('coupon_id')),
            array('coupon_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_coupon_history', array('rule_id')),
            array('rule_id'))
        ->addIndex($installer->getIdxName('imaginato_reward/reward_coupon_history', array('customer_id')),
            array('customer_id'))

        ->addForeignKey($installer->getFkName('imaginato_reward/reward_coupon_history', 'coupon_id', 'salesrule/coupon', 'coupon_id'),
            'coupon_id', $installer->getTable('salesrule/coupon'), 'coupon_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('imaginato_reward/reward_coupon_history', 'rule_id', 'salesrule/rule', 'rule_id'),
            'rule_id', $installer->getTable('salesrule/rule'), 'rule_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('imaginato_reward/reward_coupon_history', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id', $installer->getTable('customer/entity'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

        ->setComment('Imaginato Reward Staging');
    $installer->getConnection()->createTable($table);
}
$installer->endSetup();
