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
/* @var $installer Imaginato_Orderexport_Model_Resource_Setup */
$installer->startSetup();


/**
 * Add index  in 'imaginato_orderexport/lineitem'
 */
$installer->getConnection()
    ->addIndex(
        $installer->getTable('newsletter/subscriber'),
        $installer->getIdxName(
            'newsletter/subscriber',
            array('subscriber_email'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        array('subscriber_email'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
    );

/**
 * Create table 'imaginato_orderexport/lineitem'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('imaginato_orderexport/lineitem'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Item Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Order Id')
    ->addcolumn('created_at', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(), 'Order Date')
    ->addcolumn('customer_created_at', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(), 'Member Registration Date')
    ->addcolumn('customer_confirmation', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Member Confirmation Date')
    ->addcolumn('customer_no_newsletter', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Member not receive promotion message')
    ->addcolumn('increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 25, array(), 'Order Number')
    ->addcolumn('invoice_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Invoice Number')
    ->addcolumn('billing_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Name')
    ->addcolumn('shipping_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Name')
    ->addcolumn('customer_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Email')
    ->addcolumn('customer_mobile', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Telephone')
    ->addcolumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Customer group_id')
    ->addcolumn('website_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Website Id')
    ->addcolumn('purchased_from', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Order Purchased From')
    ->addcolumn('payment_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Order Payment Method')
    ->addcolumn('cc_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Credit Card Type')
    ->addcolumn('customer_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Customer Name')
    ->addcolumn('item_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item Name')
    ->addcolumn('item_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 25, array(), 'Item Status')
    ->addcolumn('product_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item product_id')
    ->addcolumn('stock_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item stock_id')
    ->addcolumn('item_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item SKU')
    ->addcolumn('evisu_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Evisu SKU')
    ->addcolumn('item_options', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item Options')
    ->addcolumn('item_qty_ordered', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Qty Ordered')
    ->addcolumn('item_qty_invoiced', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Qty Invoiced')
    ->addcolumn('item_qty_shipped', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Qty Shipped')
    ->addcolumn('item_qty_canceled', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Qty Canceled')
    ->addcolumn('item_qty_refunded', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Qty Refunded')
    ->addcolumn('item_original_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Original Price')
    ->addcolumn('base_item_original_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Base Item Original Price')
    ->addcolumn('base_product_original_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Base Product Original Price')
    ->addcolumn('billing_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Company')
    ->addcolumn('billing_street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Street')
    ->addcolumn('billing_zipcode', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Zip')
    ->addcolumn('billing_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing City')
    ->addcolumn('billing_state', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing State')
    ->addcolumn('billing_state_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing State Name')
    ->addcolumn('billing_country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Country')
    ->addcolumn('billing_country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Country Name')
    ->addcolumn('billing_telephone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Billing Phone Number')
    ->addcolumn('shipping_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Company')
    ->addcolumn('shipping_street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Street')
    ->addcolumn('shipping_zipcode', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Zip')
    ->addcolumn('shipping_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping City')
    ->addcolumn('shipping_state', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping State')
    ->addcolumn('shipping_state_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping State Name')
    ->addcolumn('shipping_country_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Country')
    ->addcolumn('shipping_country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Country Name')
    ->addcolumn('shipping_telephone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Shipping Phone Number')
    ->addcolumn('shipping_cost', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Shipping')
    ->addcolumn('base_shipping_cost', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Shipping')
    ->addcolumn('shipping_cost_on_first_item', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Order Shipping (on first item)')
    ->addcolumn('base_shipping_cost_on_first_item', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Shipping (on first item)')
    ->addcolumn('base_discount_price_diff', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Discount price(diff): base_product_original_price - base_item_price')
    ->addcolumn('item_tax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Item Tax')
    ->addcolumn('base_item_tax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Base Item Tax')
    ->addcolumn('tax_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Tax Code')
    ->addcolumn('item_discount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item Discount')
    ->addcolumn('base_item_discount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Base Item Discount')
    ->addcolumn('gift_card_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Gift Card Amount')
    ->addcolumn('gift_card_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Gift Card Number')
    ->addcolumn('item_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item Price')
    ->addcolumn('base_item_price', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Base Item Price')
    ->addcolumn('item_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Item Total')
    ->addcolumn('base_item_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Base Item Total')
    ->addcolumn('item_count', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Order Item Increment')
    ->addcolumn('userselect', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'User Select')
    ->addcolumn('shipping_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Shipping Method')
    ->addcolumn('byselfstorename', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Delivery Branch Name')
    ->addcolumn('byselfstoreid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Delivery Branch Code')
    ->addcolumn('subtotal', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Order Subtotal')
    ->addcolumn('tax_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Tax')
    ->addcolumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Tax')
    ->addcolumn('discount_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Discount')
    ->addcolumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Discount')
    ->addcolumn('grand_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Grand Total')
    ->addcolumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Base Grand Total')
    ->addcolumn('total_paid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Order Paid')
    ->addcolumn('base_total_paid', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(),  'Base Order Paid')
    ->addcolumn('total_refunded', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Refunded')
    ->addcolumn('base_total_refunded', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Refunded')
    ->addcolumn('total_due', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Order Due')
    ->addcolumn('base_total_due', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Base Order Due')
    ->addcolumn('total_qty_ordered', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(),  'Total Qty Items Ordered')
    ->addcolumn('real_grand_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Grand Total(without redemption)')
    ->addcolumn('base_real_grand_total', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(), 'Base Grand Total(without redemption)')
    ->addcolumn("order_currency_code", Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), "Order Currency Code")
    ->addcolumn("base_order_currency_code", Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), "Base Order Currency Code")
    ->addcolumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(), 'Order Status')
    ->addcolumn('status_last_updated', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(),  'Status Last Update Day')
    ->addcolumn('shipment_date', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(), 'Shipment Date')

    ->addIndex($installer->getIdxName('imaginato_orderexport/lineitem/order_item', array('order_id')),
        array('order_id'))
    ->setComment('Imaginato Orderexport Lineitem');
$installer->getConnection()->createTable($table);

$installer->endSetup();
