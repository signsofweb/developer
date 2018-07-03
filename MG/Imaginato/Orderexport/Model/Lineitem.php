<?php

class Imaginato_Orderexport_Model_Lineitem extends Mage_Core_Model_Abstract
{
    public $outputHeadRowValues;
    public $select;
    public $nowOrder;
    public $itemInc;
    public $regin_code;
    public $store_name;

    public function _construct()
    {
        $this->_init('imaginato_orderexport/lineitem');
    }

    public function getNotExistOrders($order_ids)
    {
        return $this->getCollection()->getNotExistOrders($order_ids);
    }

    public function resave($order_id='')
    {
        if(!$order_id){
            return;
        }
        $this->deleteOrderData(array($order_id));
        $this->insertOrderData(array($order_id));
    }

    public function insertOrderData($orders)
    {
        $this->addColumnSelect();
        $limit_num = 1000;
        $order_count = count($orders);
        for ($i = 0; $i < $order_count; $i += $limit_num) {
            $select_orders = array_slice($orders, $i, $limit_num);
            if ($select_orders) {
                $order_collection = $this->getOrdercollection($select_orders);
                $order_other_order_data = $this->getOtherData($select_orders);
                $order_other_item_data = $this->getOtherItemData($select_orders);
                $item_data = array();
                foreach ($order_collection->getData() as $order) {
                    $order_data = new Varien_Object();
                    $order_data->addData($order);
                    if($order_other_order_data[$order['entity_id']]){
                        $order_data->addData($order_other_order_data[$order['entity_id']]);
                    }
                    if($order_other_item_data[$order['item_id']]){
                        $order_data->addData($order_other_item_data[$order['item_id']]);
                    }
                    $this->setOtherData($order_data);
                    $item_data[] = array_merge(
                        $this->getCommonOrderValues($order_data),
                        $this->getItemValues($order_data),
                        $this->getCustomerValues($order_data)
                    );
                }
                $this->saveItems($item_data);

                //$this->updateLineItemsOnSale($select_orders);
            }
        }
    }

    public function deleteOrderData($order_id)
    {
        if(empty($order_id)){
            return;
        }
        $collection = $this->getCollection()->getItemByOrders($order_id);
        foreach($collection as $val){
            $this->load($val);
            $this->delete();
        }
    }

    public function saveItems($item_datas)
    {
        foreach ($item_datas as $data) {
            $this->_isObjectNew = true;
            $this->addData($data);
            $this->save();
            $this->unsetData();
        }
    }

    /*protected function updateLineItemsOnSale($order_ids)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $specialTable = Mage::helper('warehouse/catalog_product_price_indexer')->getBatchSpecialPriceIndexTable();
        $lineitemTable = Mage::getModel('imaginato_orderexport/lineitem')->getResource()->getMainTable();
        $sql = "UPDATE ".$lineitemTable." AS l INNER JOIN ".$specialTable." AS s ON l.product_id = s.entity_id AND l.website_id = s.website_id AND  l.stock_id = s.stock_id SET l.product_on_sale = 1 WHERE order_id IN(".implode(',', $order_ids).");";
        $write->query($sql);
    }*/

    protected function getOrdercollection($order_ids)
    {

        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->getSelect()->reset('columns');
        $this->select['main_table'][] = 'entity_id';
        $this->select['main_table'][] = 'customer_id';
        $this->select['main_table'][] = 'store_id';
        $this->select['main_table'][] = 'is_virtual';
        $collection->getSelect()->columns($this->select['main_table'], 'main_table');

        $collection->getSelect()->joinLeft(
            array('customer' => $collection->getTable('customer/entity')),
            'customer.entity_id = main_table.customer_id',
            $this->select['customer']
        );

        $collection->getSelect()->joinLeft(
            array('store' => $collection->getTable('core/store')),
            'main_table.store_id = store.store_id',
            $this->select['store']
        );

        $this->select['order_item'][] = 'item_id';
        $this->select['order_item'][] = 'IF(`order_item`.original_price > `order_item`.price, 1, 0) as product_on_sale';
        $collection->getSelect()->joinLeft(
            array('order_item' => $collection->getTable('sales/order_item')),
            'order_item.order_id = main_table.entity_id and order_item.parent_item_id is null',
            $this->select['order_item']
        );

        $collection->getSelect()->joinLeft(
            array('product' => $collection->getTable('catalog/product')),
            'product.sku = order_item.sku',
            array()
        );

        $evisu_sku = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'evisu_sku');
        $attribute_id = $evisu_sku->getData('attribute_id');
        $attribute_table = Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_' . $evisu_sku->getData('backend_type');
        if ($attribute_id && $attribute_table) {
            $collection->getSelect()->joinLeft(
                array('evisu_sku' => $attribute_table),
                'evisu_sku.entity_id = product.entity_id and evisu_sku.attribute_id=' . $attribute_id,
                array('evisu_sku' => 'value')
            );
        }

        $description = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'description');
        $description_attribute_id = $description->getData('attribute_id');
        $description_attribute_table = Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_' . $description->getData('backend_type');
        if ($description_attribute_id && $description_attribute_table) {
            $collection->getSelect()->joinLeft(
                array('t' => $description_attribute_table),
                't.entity_id = product.entity_id and t.attribute_id=' . $description_attribute_id. ' and  t.entity_type_id = product.entity_type_id AND t.store_id = 0',
                array('product_description' => 'value')
            );
        }

        $collection->getSelect()->joinLeft(
            array('payment' => $collection->getTable('sales/order_payment')),
            'payment.parent_id = main_table.entity_id',
            $this->select['payment']
        );

        $collection->getSelect()->joinLeft(
            array('billing' => $collection->getTable('sales/order_address')),
            'billing.parent_id = main_table.entity_id and billing.address_type=\'billing\'',
            $this->select['billing']
        );

        $collection->getSelect()->joinLeft(
            array('shipping' => $collection->getTable('sales/order_address')),
            'shipping.parent_id = main_table.entity_id and shipping.address_type=\'shipping\'',
            $this->select['shipping']
        );
        $collection->addFieldToFilter('entity_id', array('in' => $order_ids));
        return $collection;
    }

    protected function getOtherData($orders)
    {
        // status_last_updated
        $history = Mage::getModel('sales/order_status_history')->getCollection()
            ->addFieldToFilter('parent_id', array('in' => $orders));
        $history->getSelect()
            ->reset('columns')
            ->columns(array('parent_id', 'status_last_updated' => 'max(created_at)'))
            ->group('parent_id');
        foreach ($history->getData() as $val) {
            $orders_data[$val['parent_id']]['status_last_updated'] = $val['status_last_updated'];
        }
        // tax_code
        $order_tax = Mage::getModel('tax/sales_order_tax')->getCollection()
            ->addFieldToFilter('order_id', array('in' => $orders));
        $order_tax->getSelect()
            ->reset('columns')
            ->columns(array('order_id', 'code'))
            ->order('process');
        $tax_data = array();
        foreach ($order_tax->getData() as $tax) {
            $tax_data[$tax['order_id']][] = $tax['code'];
        }

        foreach ($orders as $order_id) {
            $orders_data[$order_id]['tax_code'] = isset($tax_data[$order_id]) ? implode(',', $tax_data[$order_id]) : '';
        }
        return $orders_data;
    }

    protected function getOtherItemData($orders)
    {
        // item_status
        $order_parent_item = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', array('in' => $orders))
            ->addFieldToFilter('parent_item_id', array('notnull' => ''));
        $order_parent_item->getSelect()
            ->reset('columns')
            ->columns(array('parent_item_id', 'qty_backordered' => 'sum(qty_backordered)'))
            ->group('parent_item_id');
        $parent_qty_backordered = array();
        foreach ($order_parent_item->getData() as $val) {
            $parent_qty_backordered[$val['parent_item_id']] = $val['qty_backordered'];
        }

        $order_item = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', array('in' => $orders))
            ->addFieldToFilter('parent_item_id', array('null' => ''));
        $order_item->getSelect()
            ->reset('columns')
            ->columns(array('item_id', 'qty_backordered', 'qty_canceled', 'qty_invoiced', 'qty_ordered', 'qty_refunded', 'qty_shipped'));
        foreach ($order_item->getData() as $val) {
            $item_status = $this->dealItemStatus($val, $parent_qty_backordered[$val['item_id']]);
            $item_data[$val['item_id']]['item_status'] = $item_status;
        }
        // invoice_no
        $order_item = Mage::getModel('sales/order_invoice_item')->getCollection()
            ->addFieldToFilter('invoice.order_id', array('in' => $orders));
        $order_item->getSelect()->reset('columns')->columns(array('order_item_id'));
        $order_item->getSelect()->joinLeft(
            array('invoice' => $order_item->getTable('sales/invoice')),
            'main_table.parent_id = invoice.entity_id',
            array('invoice.increment_id')
        );

        foreach ($order_item->getData() as $val) {
            if ($val['increment_id']) {
                $item_data[$val['order_item_id']]['invoice_no'] = $val['increment_id'];
            }
        }

        // shipment time
        $order_item = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('main_table.order_id', array('in' => $orders))
            ->addFieldToFilter('main_table.parent_item_id', array('null' => ''));
        $order_item->getSelect()->reset('columns')->columns(array('item_id'));
        $order_item->getSelect()->joinLeft(
            array('shipment_item' => $order_item->getTable('sales/shipment_item')),
            'shipment_item.order_item_id = main_table.item_id',
            $this->select['shipment_item']
        );
        $order_item->getSelect()->joinLeft(
            array('shipment' => $order_item->getTable('sales/shipment')),
            'shipment.entity_id = shipment_item.parent_id',
            $this->select['shipment']
        );
        foreach ($order_item->getData() as $val) {
            $item_data[$val['item_id']]['shipment:created_at'] = $val['shipment:created_at'];
            $item_data[$val['item_id']]['shipment_item:entity_id'] = $val['shipment_item:entity_id'];
        }


        return $item_data;
    }

    protected function setOtherData($order)
    {
        if ($this->nowOrder == $order->getData('entity_id')) {
            $this->itemInc++;
        } else {
            $this->nowOrder = $order->getData('entity_id');
            $this->itemInc = 1;
        }
    }

    protected function getCommonOrderValues($order)
    {
        $data = array();
        $_cards = $order->getData('main_table:gift_cards');
        if ($_cards) {
            $_cards = unserialize($_cards);
            foreach ($_cards as $_card) {
                $gift_amounts[] = $_card['a'];
                $gift_codes[] = $_card['c'];
            }
            $data['gift_card_amount'] = implode("|", $gift_amounts);
            $data['gift_card_number'] = implode("|", $gift_codes);
        }

        if (!$order->getIsVirtual()) {
            $data['userselect'] = '';
            $data['shipping_name'] = $this->getName($order->getData("shipping:firstname"), $order->getData("shipping:lastname"), $order->getData("shipping:prefix"), $order->getData("shipping:middlename"), $order->getData("shipping:suffix"));
            $data['shipping_company'] = $order->getData("shipping:company");
            $data['shipping_street'] = $this->dealStreet($order->getData("shipping:street"));
            $data['shipping_zipcode'] = $order->getData("shipping:postcode");
            $data['shipping_city'] = $order->getData("shipping:city");
            $data['shipping_state'] = $order->getData('shipping:region_id') ? $this->getRegionCode($order->getData('shipping:region_id')) : $order->getData('shipping:region');
            $data['shipping_state_name'] = $order->getData('shipping:region');
            $data['shipping_country_id'] = $order->getData('shipping:country_id');
            $data['shipping_country'] = Mage::app()->getLocale()->getCountryTranslation($order->getData('shipping:country_id'));
            $data['shipping_telephone'] = $order->getData("shipping:telephone");
            $data['byselfstoreid'] = '';
            $data['byselfstorename'] = '';
        }

        return array(
            'order_id' => $order->getData('entity_id'),
            'coupon_code' => $order->getData('main_table:coupon_code'),
            'coupon_rule_name' => $order->getData('main_table:coupon_rule_name'),
            'increment_id' => $order->getData('main_table:increment_id'),
            'created_at' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime($order->getData('main_table:created_at')))),
            'status' => $order->getData('main_table:status'),
            'status_last_updated' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime($order->getData('status_last_updated')))),
            'purchased_from' => $this->dealStoreName($order),
            'payment_method' => $order->getData('payment:method'),
            'cc_type' => $order->getData('payment:cc_type'),
            'shipping_method' => $order->getData('main_table:shipping_method'),
            'subtotal' => $order->getData('main_table:subtotal'),
            'tax_amount' => $order->getData('main_table:tax_amount'),
            'base_tax_amount' => $order->getData('main_table:base_tax_amount'),
            'tax_code' => $order->getData('tax_code'),
            'order_currency_code' => $order->getData('main_table:order_currency_code'),
            'base_order_currency_code' => $order->getData('main_table:base_currency_code'),
            'shipping_cost' => $order->getData('main_table:shipping_amount'),
            'base_shipping_cost' => $order->getData('main_table:base_shipping_amount'),
            'discount_amount' => $order->getData('main_table:discount_amount'),
            'base_discount_amount' => $order->getData('main_table:base_discount_amount'),
            'grand_total' => $order->getData('main_table:grand_total'),
            'base_grand_total' => $order->getData('main_table:base_grand_total'),
            'total_paid' => $order->getData('main_table:total_paid'),
            'base_total_paid' => $order->getData('main_table:base_total_paid'),
            'total_refunded' => $order->getData('main_table:total_refunded'),
            'base_total_refunded' => $order->getData('main_table:base_total_refunded'),
            'total_due' => max(Mage::app()->getStore($order->getData('store_id'))->roundPrice($order->getData('main_table:grand_total') - $order->getData('main_table:total_paid')), 0),
            'base_total_due' => max(Mage::app()->getStore($order->getData('store_id'))->roundPrice($order->getData('main_table:base_grand_total') - $order->getData('main_table:base_total_paid')), 0),
            'total_qty_ordered' => $order->getData('order_item:qty_ordered'),
            'real_grand_total' => $order->getData('main_table:subtotal') + $order->getData('main_table:shipping_amount') + $order->getData('main_table:discount_amount'),
            'base_real_grand_total' => $order->getData('main_table:base_subtotal') + $order->getData('main_table:base_shipping_amount') + $order->getData('main_table:base_discount_amount'),
            'customer_name' => $this->getName($order->getData('main_table:customer_firstname'), $order->getData('main_table:customer_lastname'), $order->getData('main_table:customer_prefix'), $order->getData('main_table:customer_middlename'), $order->getData('main_table:customer_suffix')),
            'customer_email' => $order->getData('main_table:customer_email'),
            'customer_group_id' => $order->getData('main_table:customer_group_id'),
            'website_id' => $order->getData('store:website_id'),
            'billing_name' => $this->getName($order->getData("billing:firstname"), $order->getData("billing:lastname"), $order->getData("billing:prefix"), $order->getData("billing:middlename"), $order->getData("billing:suffix")),
            'billing_company' => $order->getData("billing:company"),
            'billing_street' => $this->dealStreet($order->getData("billing:street")),
            'billing_zipcode' => $order->getData("billing:postcode"),
            'billing_city' => $order->getData("billing:city"),
            'billing_state' => $order->getData('billing:region_id') ? $this->getRegionCode($order->getData('billing:region_id')) : $order->getData('billing:region'),
            'billing_state_name' => $order->getData('billing:region'),
            'billing_country_id' => $order->getData('billing:country_id'),
            'billing_country' => Mage::app()->getLocale()->getCountryTranslation($order->getData('billing:country_id')),
            'billing_telephone' => $order->getData("billing:telephone"),
            'invoice_no' => $order->getData('invoice_no')
        ) + $data;
    }

    protected function getItemValues($order)
    {

        //$shipment_date
        $created_at = $order->getData('shipment:created_at');
        $shipDate = '';
        if ($order->getData('shipment_item:entity_id')) {
            $shipDate = Mage::getModel('core/date')->timestamp(strtotime($created_at));
            $shipDate = date('Y-m-d', $shipDate);
        }

        $base_product_original_price = $order->getData('product_price:price');
        $base_item_price = $order->getData('order_item:base_price');
        $base_price_diff = ($base_product_original_price - $base_item_price) > 0 ? $base_product_original_price - $base_item_price : 0;
        return array(
            'item_id' => $order->getData('order_item:item_id'),
            'product_id' => $order->getData('order_item:product_id'),
            'stock_id' => $order->getData('order_item:stock_id'),
            'item_count' => $this->itemInc,
            'item_name' => $order->getData('name'),
            'item_status' => $order->getData('item_status'),
            'evisu_sku' => $order->getData('evisu_sku'),
            'item_sku' => $order->getData('order_item:sku'),
            'item_options' => $this->dealOptions($order->getData('order_item:product_options')),

            'item_original_price' => $order->getData('order_item:original_price') ? $order->getData('order_item:original_price') : $order->getData('order_item:price'),
            'base_item_original_price' => $order->getData('order_item:base_original_price'),

            'base_product_original_price' => $base_product_original_price,

            'item_price' => $order->getData('order_item:price'),
            'base_item_price' => $base_item_price,
            'base_discount_price_diff' => $base_price_diff,

            'item_qty_ordered' => (int)$order->getData('order_item:qty_ordered'),
            'item_qty_invoiced' => (int)$order->getData('order_item:qty_invoiced'),
            'item_qty_shipped' => (int)$order->getData('order_item:qty_shipped'),
            'item_qty_canceled' => (int)$order->getData('order_item:qty_canceled'),
            'item_qty_refunded' => (int)$order->getData('order_item:qty_refunded'),
            'item_tax' => $order->getData('order_item:tax_amount'),
            'base_item_tax' => $order->getData('order_item:base_tax_amount'),
            'item_discount' => $order->getData('order_item:discount_amount'),
            'base_item_discount' => $order->getData('order_item:base_discount_amount'),
            'item_total' => ($order->getData('order_item:row_total') - $order->getData('order_item:discount_amount') + $order->getData('order_item:tax_amount') + $order->getData('order_item:weee_tax_applied_row_amount')),
            'base_item_total' => ($order->getData('order_item:base_row_total') - $order->getData('order_item:base_discount_amount') + $order->getData('order_item:base_tax_amount') + $order->getData('order_item:base_weee_tax_applied_row_amnt')),

            'shipping_cost_on_first_item' => ($this->itemInc == 1) ? $order->getData('main_table:shipping_amount') : 0,
            'base_shipping_cost_on_first_item' => ($this->itemInc == 1) ? $order->getData('main_table:base_shipping_amount') : 0,
            'shipment_date' => $shipDate,
            'product_description'=>$order->getData('product_description'),
            'product_on_sale'=>$order->getData('product_on_sale'),

        );
    }

    protected function getCustomerValues($order)
    {
        $data = array();
        if ($order->getCustomerId()) {
            $isSub = false;
            if ($order->getData('subscriber:subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                $isSub = true;
            }

            $data = array(
                'customer_created_at' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime($order->getData('customer:created_at')))),
                'customer_confirmation' => '',
                'customer_mobile' => '',
                'customer_newsletter' => ($isSub ? 'Y' : 'N'),
                'customer_no_newsletter' => ($isSub ? '' : 'Y')
            );
        }
        return $data;

    }

    protected function addColumnSelect()
    {
        $column_select_data = $this->getColumnData();
        foreach ($column_select_data as $column) {
            foreach ($column as $data) {
                $this->addSelectData($data['table'], $data['column']);
            }
        }
    }

    protected function addSelectData($table, $column)
    {
        if (empty($this->select[$table])) {
            $this->select[$table] = array();
        }
        if (is_array($column)) {
            foreach ($column as $val) {
                if (!in_array($column, $this->select[$table])) {
                    $this->select[$table][$table . ':' . $val] = $val;
                }
            }
        } else {
            if (!in_array($column, $this->select[$table])) {
                $this->select[$table][$table . ':' . $column] = $column;
            }
        }
    }

    protected function getColumnData()
    {
        // order
        $selectData['created_at'] = array(array('table' => 'main_table', 'column' => 'created_at'));
        $selectData['increment_id'] = array(array('table' => 'main_table', 'column' => 'increment_id'));
        $selectData['customer_name'] = array(array('table' => 'main_table', 'column' => array('customer_firstname', 'customer_lastname', 'customer_prefix', 'customer_middlename', 'customer_suffix')));
        $selectData['customer_email'] = array(array('table' => 'main_table', 'column' => 'customer_email'));
        $selectData['status'] = array(array('table' => 'main_table', 'column' => 'status'));

        $selectData['shipping_cost'] = array(array('table' => 'main_table', 'column' => 'shipping_amount'));
        $selectData['base_shipping_cost'] = array(array('table' => 'main_table', 'column' => 'base_shipping_amount'));
        $selectData['shipping_method'] = array(array('table' => 'main_table', 'column' => 'shipping_method'));

        $selectData['subtotal'] = array(array('table' => 'main_table', 'column' => 'subtotal'));

        $selectData['tax_amount'] = array(array('table' => 'main_table', 'column' => 'tax_amount'));
        $selectData['base_tax_amount'] = array(array('table' => 'main_table', 'column' => 'base_tax_amount'));

        $selectData['discount_amount'] = array(array('table' => 'main_table', 'column' => 'discount_amount'));
        $selectData['base_discount_amount'] = array(array('table' => 'main_table', 'column' => 'base_discount_amount'));

        $selectData['grand_total'] = array(array('table' => 'main_table', 'column' => 'grand_total'));
        $selectData['base_grand_total'] = array(array('table' => 'main_table', 'column' => 'base_grand_total'));

        $selectData['total_paid'] = array(array('table' => 'main_table', 'column' => 'total_paid'));
        $selectData['base_total_paid'] = array(array('table' => 'main_table', 'column' => 'base_total_paid'));

        $selectData['total_refunded'] = array(array('table' => 'main_table', 'column' => 'total_refunded'));
        $selectData['base_total_refunded'] = array(array('table' => 'main_table', 'column' => 'base_total_refunded'));

        $selectData['total_due'] = array(array('table' => 'main_table', 'column' => array('grand_total', 'total_paid')));
        $selectData['base_total_due'] = array(array('table' => 'main_table', 'column' => array('base_grand_total', 'base_total_paid')));

        $selectData['real_grand_total'] = array(array('table' => 'main_table', 'column' => array('subtotal', 'shipping_amount', 'discount_amount')));
        $selectData['base_real_grand_total'] = array(array('table' => 'main_table', 'column' => array('base_subtotal', 'base_shipping_amount', 'base_discount_amount')));

        $selectData['order_currency_code'] = array(array('table' => 'main_table', 'column' => 'order_currency_code'));

        $selectData['base_order_currency_code'] = array(array('table' => 'main_table', 'column' => 'base_currency_code'));

        $selectData['gift_card_amount'] = array(array('table' => 'main_table', 'column' => 'gift_cards'));
        $selectData['gift_card_number'] = array(array('table' => 'main_table', 'column' => 'gift_cards'));

        $selectData['coupon_code'] = array(array('table' => 'main_table', 'column' => 'coupon_code'));
        $selectData['coupon_rule_name'] = array(array('table' => 'main_table', 'column' => 'coupon_rule_name'));

        // order_item
        $selectData['item_name'] = array(array('table' => 'order_item', 'column' => 'name'));
        $selectData['item_sku'] = array(array('table' => 'order_item', 'column' => array('product_type', 'product_options', 'sku')));
        $selectData['item_options'] = array(array('table' => 'order_item', 'column' => 'product_options'));
        $selectData['item_qty_ordered'] = array(array('table' => 'order_item', 'column' => 'qty_ordered'));
        $selectData['item_qty_invoiced'] = array(array('table' => 'order_item', 'column' => 'qty_invoiced'));
        $selectData['item_qty_shipped'] = array(array('table' => 'order_item', 'column' => 'qty_shipped'));
        $selectData['item_qty_canceled'] = array(array('table' => 'order_item', 'column' => 'qty_canceled'));
        $selectData['item_qty_refunded'] = array(array('table' => 'order_item', 'column' => 'qty_refunded'));

        $selectData['item_original_price'] = array(array('table' => 'order_item', 'column' => array('original_price', 'price')));
        $selectData['base_item_original_price'] = array(array('table' => 'order_item', 'column' => 'base_original_price'));
        $selectData['base_product_original_price'] = array(array('table' => 'order_item', 'column' => 'item_id'), array('table' => 'main_table', 'column' => 'customer_group_id'), array('table' => 'store', 'column' => 'website_id'), array('table' => 'order_item', 'column' => array('product_id','stock_id')));

        $selectData['shipping_cost_on_first_item'] = array(array('table' => 'main_table', 'column' => 'shipping_amount'));
        $selectData['base_shipping_cost_on_first_item'] = array(array('table' => 'main_table', 'column' => 'base_shipping_amount'));

        $selectData['base_discount_price_diff'] = array(array('table' => 'product_price', 'column' => 'price'), array('table' => 'order_item', 'column' => 'base_price'));

        $selectData['item_tax'] = array(array('table' => 'order_item', 'column' => 'tax_amount'));
        $selectData['base_item_tax'] = array(array('table' => 'order_item', 'column' => 'base_tax_amount'));

        $selectData['item_discount'] = array(array('table' => 'order_item', 'column' => 'discount_amount'));
        $selectData['base_item_discount'] = array(array('table' => 'order_item', 'column' => 'base_discount_amount'));

        $selectData['item_price'] = array(array('table' => 'order_item', 'column' => 'price'));
        $selectData['base_item_price'] = array(array('table' => 'order_item', 'column' => 'base_price'));

        $selectData['item_total'] = array(array('table' => 'order_item', 'column' => array('row_total', 'discount_amount', 'tax_amount', 'weee_tax_applied_row_amount')));
        $selectData['base_item_total'] = array(array('table' => 'order_item', 'column' => array('base_row_total', 'base_discount_amount', 'base_tax_amount', 'base_weee_tax_applied_row_amnt')));

        $selectData['total_qty_ordered'] = array(array('table' => 'order_item', 'column' => 'qty_ordered'));

        $selectData['shipment_date'] = array(array('table' => 'order_item', 'column' => 'item_id'), array('table' => 'shipment', 'column' => 'created_at'), array('table' => 'shipment_item', 'column' => 'entity_id'));

        // payment
        $selectData['payment_method'] = array(array('table' => 'payment', 'column' => 'method'));
        $selectData['cc_type'] = array(array('table' => 'payment', 'column' => 'cc_type'));

        // customer
        $selectData['customer_created_at'] = array(array('table' => 'customer', 'column' => 'created_at'));
        $selectData['customer_newsletter'] = array(array('table' => 'customer', 'column' => 'entity_id'), array('table' => 'subscriber', 'column' => 'subscriber_status'));
        $selectData['customer_no_newsletter'] = array(array('table' => 'customer', 'column' => 'entity_id'), array('table' => 'subscriber', 'column' => 'subscriber_status'));

        // billing_address
        $selectData['billing_name'] = array(array('table' => 'billing', 'column' => array('firstname', 'lastname', 'prefix', 'middlename', 'suffix')));
        $selectData['billing_company'] = array(array('table' => 'billing', 'column' => 'company'));
        $selectData['billing_street'] = array(array('table' => 'billing', 'column' => 'street'));
        $selectData['billing_zipcode'] = array(array('table' => 'billing', 'column' => 'postcode'));
        $selectData['billing_city'] = array(array('table' => 'billing', 'column' => 'city'));
        $selectData['billing_state'] = array(array('table' => 'billing', 'column' => array('region', 'region_id')));
        $selectData['billing_state_name'] = array(array('table' => 'billing', 'column' => 'region'));
        $selectData['billing_country_id'] = array(array('table' => 'billing', 'column' => 'country_id'));
        $selectData['billing_country'] = array(array('table' => 'billing', 'column' => 'country_id'));
        $selectData['billing_telephone'] = array(array('table' => 'billing', 'column' => 'telephone'));
        // shipping_address
        $selectData['shipping_name'] = array(array('table' => 'shipping', 'column' => array('firstname', 'lastname', 'prefix', 'middlename', 'suffix')));
        $selectData['shipping_company'] = array(array('table' => 'shipping', 'column' => 'company'));
        $selectData['shipping_street'] = array(array('table' => 'shipping', 'column' => 'street'));
        $selectData['shipping_zipcode'] = array(array('table' => 'shipping', 'column' => 'postcode'));
        $selectData['shipping_city'] = array(array('table' => 'shipping', 'column' => 'city'));
        $selectData['shipping_state'] = array(array('table' => 'shipping', 'column' => array('region', 'region_id')));
        $selectData['shipping_state_name'] = array(array('table' => 'shipping', 'column' => 'region'));
        $selectData['shipping_country_id'] = array(array('table' => 'shipping', 'column' => 'country_id'));
        $selectData['shipping_country'] = array(array('table' => 'shipping', 'column' => 'country_id'));
        $selectData['shipping_telephone'] = array(array('table' => 'shipping', 'column' => 'telephone'));

        return $selectData;
    }

    protected function getName($firstname, $lastname, $prefix = '', $middlename = '', $suffix = '')
    {
        $name = '';
        if ($prefix) {
            $name .= $prefix . ' ';
        }
        $name .= $firstname;
        if ($middlename) {
            $name .= ' ' . $middlename;
        }
        $name .= ' ' . $lastname;
        if ($suffix) {
            $name .= ' ' . $suffix;
        }
        return $name;
    }

    protected function dealStreet($street)
    {
        $arr = is_array($street) ? $street : explode("\n", $street);
        $street1 = $arr[0] ? $arr[0] : '';
        $street2 = $arr[1] ? ' ' . $arr[1] : '';
        return $street1 . $street2;
    }

    protected function getRegionCode($regin_id)
    {
        if (!$regin_id) {
            return '';
        }
        if (!$this->regin_code[$regin_id]) {
            $this->regin_code[$regin_id] = Mage::getModel('directory/region')->load($regin_id)->getCode();
        }
        return $this->regin_code[$regin_id];
    }

    protected function dealOptions($product_options)
    {
        if (!$product_options) {
            return '';
        }
        $options = unserialize($product_options);
        if (!$options) {
            return '';
        }
        $orderOptions = array();
        if (isset($options['options'])) {
            $orderOptions = array_merge($orderOptions, $options['options']);
        }
        if (isset($options['additional_options'])) {
            $orderOptions = array_merge($orderOptions, $options['additional_options']);
        }
        if (!empty($options['attributes_info'])) {
            $orderOptions = array_merge($orderOptions, $options['attributes_info']);
        }
        $option_str = '';
        foreach ($orderOptions as $_option) {
            if (strlen($option_str) > 0) {
                $option_str .= ', ';
            }
            $option_str .= $_option['label'] . ': ' . $_option['value'];
        }
        return $option_str;
    }

    private function dealItemStatus($item_array, $child_backordered = '0')
    {
        $order_item_model = Mage::getModel('sales/order_item');

        $backordered = (float)$item_array['qty_backordered'];
        if (!$backordered && $child_backordered) {
            $item_array['qty_backordered'] = (float)$child_backordered;
        }
        $order_item_model->addData($item_array);
        return $order_item_model->getStatus();
    }

    private function dealStoreName($order)
    {
        $store_id = $order->getStoreId();
        if(empty($this->store_name[$store_id])){
            $store = Mage::app()->getStore($store_id);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getName()
            );
            $this->store_name[$store_id] =  implode(', ', $name);
        }
        return $this->store_name[$store_id];
    }
}
