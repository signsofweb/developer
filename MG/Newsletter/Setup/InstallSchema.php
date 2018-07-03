<?php

namespace Convert\Newsletter\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Convert\Newsletter\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /* get newsletter subscriber table */
        $table = $setup->getTable('newsletter_subscriber');
        /* add guest name */
        $setup->getConnection()->addColumn(
            $table,
            'name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Guest Name'
            ]
        );

        /* add guest gender */
        $setup->getConnection()->addColumn(
            $table,
            'gender',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Guest gender'
            ]
        );
        /* add guest dob */
        $setup->getConnection()->addColumn(
            $table,
            'dob',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'Guest DOB'
            ]
        );
        /* add guest interests */
        $setup->getConnection()->addColumn(
            $table,
            'interests',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Guest Interests'
            ]
        );
        $setup->endSetup();
    }
}