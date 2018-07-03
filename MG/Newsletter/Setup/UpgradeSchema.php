<?php 
namespace Convert\Newsletter\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
            // Get module table
        $table = $setup->getTable('newsletter_subscriber');
        $setup->getConnection()->addColumn(
        $table,
            'program',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Guest Program'
                ]
            );
		$setup->getConnection()->addColumn(
        $table,
            'post_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'post code'
                ]
            );

        $setup->endSetup();
		
    }
} 
?>