<?php

class Imaginato_Orderexport_Model_Export_Lineitem extends Imaginato_Orderexport_Model_Export_Orderperrow
{

    /**
     * Concrete implementation of abstract method to export given orders to csv file in var/export.
     *
     * @param $orders List of orders of type Mage_Sales_Model_Order or order ids to export.
     * @return String The name of the written csv file in var/export
     */
    public function exportOrders($orders)
    {
        foreach($orders as $key => $_orderId){
            $order = Mage::getModel('sales/order')->load($_orderId);
            if($order->getRelationChildId()){
                unset($orders[$key]);
            }
        }
        $fileName = 'order_export_' . date("Ymd_His") . '.csv';
        $fp = fopen(Mage::getBaseDir('export') . '/' . $fileName, 'w');

        fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        $this->writeHeadRow($fp);

        $limit_num = 10000;
        $order_count = count($orders);
        for ($i = 0; $i < $order_count; $i += $limit_num) {
            $select_orders = array_slice($orders, $i, $limit_num);
            $this->writeOrder($select_orders, $fp);
        }

        fclose($fp);
        return $fileName;
    }

    /**
     * Writes the row(s) for the given order in the csv file.
     * A row is added to the csv file for each ordered item.
     *
     * @param Mage_Sales_Model_Order $order The order to write csv of
     * @param $fp The file handle of the csv file
     */
    protected function writeOrder($order, $fp)
    {
        if(empty($order)){
            return;
        }

        $output_columns = array_keys($this->getOutputHeadRowValues());

        $select_columns = $output_columns;
        if(in_array('base_product_original_price',$select_columns) || in_array('base_discount_price_diff',$select_columns)){
            $select_columns[] = 'product_id';
            $select_columns[] = 'stock_id';
            $select_columns[] = 'customer_group_id';
            $select_columns[] = 'website_id';
        }
        if($key = array_search('base_item_price_subtotal', $select_columns)){
            array_splice($select_columns, $key, 1);
            $select_columns[] = 'base_item_price';
            $select_columns[] = 'item_qty_ordered';
        }
        if($key = array_search('base_item_price_total', $select_columns)){
            array_splice($select_columns, $key, 1);
            $select_columns[] = 'base_item_price';
            $select_columns[] = 'item_qty_ordered';
            $select_columns[] = 'base_shipping_cost';
            $select_columns[] = 'base_item_tax';
            $select_columns[] = 'base_item_discount';
        }
        if($key = array_search('base_item_price_excldiscount', $select_columns)){
            array_splice($select_columns, $key, 1);
            $select_columns[] = 'base_item_price';
            $select_columns[] = 'item_qty_ordered';
            $select_columns[] = 'base_item_discount';
        }

        $collection = Mage::getModel('imaginato_orderexport/lineitem')->getCollection();
        $collection->getSelect()
            ->where('order_id in (?)',$order)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($select_columns)
            ->order(array('order_id', 'item_id'));

        // newsletter/subscriber
        if(in_array('customer_newsletter',$output_columns) || in_array('customer_no_newsletter',$output_columns)){
            $collection->getSelect()->joinLeft(
                array('subscriber' => $collection->getTable('newsletter/subscriber')),
                'subscriber.subscriber_email = main_table.customer_email',
                array('subscriber_status')
            );
        }
        // product_price
        if(in_array('base_product_original_price',$output_columns) || in_array('base_discount_price_diff',$output_columns)){
            $collection->getSelect()->joinLeft(
                array('product_price' => $collection->getTable('catalog/product_index_price')),
                'product_price.entity_id = main_table.product_id and product_price.stock_id = main_table.stock_id and main_table.customer_group_id=product_price.customer_group_id and main_table.website_id=product_price.website_id',
                array('select_base_product_original_price'=>'price')
            );
        }

        foreach ($collection->getData() as $data) {
            // newsletter/subscriber
            if(in_array('customer_newsletter',$output_columns) || in_array('customer_no_newsletter',$output_columns)){
                if ($data['subscriber_status'] == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                    $isSub = true;
                }
                $data['customer_newsletter'] = ($isSub ? 'Y' : 'N');
                $data['customer_no_newsletter'] = ($isSub ? '' : 'Y');
            }
            // product_price
            if(in_array('base_product_original_price',$output_columns)){
                $data['base_product_original_price'] = $data['select_base_product_original_price'];
            }
            if(in_array('base_discount_price_diff',$output_columns)){
                $base_product_original_price = $data['select_base_product_original_price'];
                $base_item_price = $data['base_item_price'];
                $data['base_discount_price_diff'] = ($base_product_original_price - $base_item_price) > 0 ? $base_product_original_price - $base_item_price : 0;
            }
            // country
            if(in_array('billing_country',$output_columns) && $data['billing_country_id']){
                $data['billing_country'] = Mage::app()->getLocale()->getCountryTranslation($data['billing_country_id']);
            }
            if(in_array('shipping_country',$output_columns) && $data['shipping_country_id']){
                $data['shipping_country'] = Mage::app()->getLocale()->getCountryTranslation($data['shipping_country_id']);
            }
            if(in_array('payment_method',$output_columns) && $data['payment_method']){
                $title = Mage::getStoreConfig('payment/'.$data['payment_method'].'/title');
                if($title){
                    $data['payment_method'] = $title;
                }
            }

            if(in_array('product_on_sale',$output_columns) && isset($data['product_on_sale'])){
                if($data['product_on_sale'] == '0'){
                    $data['product_on_sale'] = 'NO';
                }else{
                    $data['product_on_sale'] = 'YES';
                }
            }

            if(in_array('base_item_price_subtotal',$output_columns)){
                $data['base_item_price_subtotal'] = $data['base_item_price']*$data['item_qty_ordered'];
            }
            if(in_array('base_item_price_total',$output_columns)){
                $data['base_item_price_total'] = $data['base_item_price']*$data['item_qty_ordered']+$data['base_shipping_cost']+$data['base_item_tax']-$data['base_item_discount'];
            }
            if(in_array('base_item_price_excldiscount',$output_columns)){
                $data['base_item_price_excldiscount'] = $data['base_item_price']*$data['item_qty_ordered']-$data['base_item_discount'];
            }

            $record_in_sort = array();
            foreach ($output_columns as $output) {
                $record_in_sort[$output] = $data[$output];
            }
            fputcsv($fp, $record_in_sort, self::DELIMITER, self::ENCLOSURE);
        }
    }
}

?>