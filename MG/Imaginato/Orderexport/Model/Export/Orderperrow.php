<?php

class Imaginato_Orderexport_Model_Export_Orderperrow extends Cleargo_Orderexport_Model_Export_Orderperrow
{
    /**
     * Returns the head column names.
     *
     * @return Array The array containing all column names
     */
    public function getHeadRowValues()
    {
        return array(

            'created_at' =>'Order Date',
            'customer_created_at'=>'Member Registration Date',
            'customer_confirmation'=> 'Member Confirmation Date',
            'customer_no_newsletter'=>'Member not receive promotion message',
            'increment_id' =>'Order Number',
            'invoice_no'=>'Invoice Number',
            'billing_name'=>'Billing Name',
            'shipping_name'=>'Shipping Name',

            'customer_email'=>'Email',
            'customer_mobile'=>'Telephone',

            'cc_type' => 'Credit Card Type',



            'item_status'=>'Item Status',
            'item_sku'=>'Item SKU',
            'evisu_sku'=>'Evisu SKU',
            'item_options'=>'Item Options',
            'item_qty_ordered'=>'Item Qty Ordered',
            'item_qty_invoiced'=>'Item Qty Invoiced',
            'item_qty_shipped'=>'Item Qty Shipped',
            'item_qty_canceled'=>'Item Qty Canceled',
            'item_qty_refunded'=>'Item Qty Refunded',
            'item_original_price'=>'Item Original Price',
            'base_item_original_price'=>'Base Item Original Price',
            'base_product_original_price'=>'Base Product Original Price',


            'billing_company'=>'Billing Company',
            'billing_street'=>'Billing Street',
            'billing_zipcode'=>'Billing Zip',
            'billing_city'=>'Billing City',
            'billing_state'=>'Billing State',
            'billing_state_name'=>'Billing State Name',
            'billing_country_id'=>'Billing Country',
            'billing_country'=>'Billing Country Name',
            'billing_telephone'=>'Billing Phone Number',
            
            'shipping_company'=>'Shipping Company',
            'shipping_street'=>'Shipping Street',
            'shipping_zipcode'=>'Shipping Zip',
            'shipping_city'=>'Shipping City',
            'shipping_state'=>'Shipping State',
            'shipping_state_name'=>'Shipping State Name',
            'shipping_country_id'=>'Shipping Country',
            'shipping_country'=>'Shipping Country Name',
            'shipping_telephone'=>'Shipping Phone Number',


            'shipping_cost' => 'Order Shipping',
            'base_shipping_cost' => 'Base Order Shipping',
            'shipping_cost_on_first_item' => 'Order Shipping (on first item)',
            'base_shipping_cost_on_first_item' => 'Base Order Shipping (on first item)',
            //'shipping_cost_per_line' => 'Order Shipping fee (Per Line Item)',

            'base_discount_price_diff'=>'Discount price(diff): base_product_original_price - base_item_price',




            'item_tax'=>'Item Tax',
            'base_item_tax'=>'Base Item Tax',
            'tax_code'=>'Tax Code',
            'item_discount'=>'Item Discount',
            'base_item_discount'=>'Base Item Discount',

            'gift_card_amount'=>'Gift Card Amount',
            'gift_card_number'=>'Gift Card Number',

            'item_price'=>'Item Price',
            'base_item_price'=>'Base Item Price',

            'item_total'=>'Item Total',
            'base_item_total'=>'Base Item Total',

            'item_count'=>'Order Item Increment',




            'userselect'=>'User Select',
            'shipping_method' => 'Shipping Method',



            'byselfstorename'=> 'Delivery Branch Name',
            'byselfstoreid'=>'Delivery Branch Code',

            'subtotal' => 'Order Subtotal',
            'tax_amount' => 'Order Tax',
            'base_tax_amount' => 'Base Order Tax',

            'discount_amount' => 'Order Discount',
            'base_discount_amount' => 'Base Order Discount',

            'grand_total' => 'Order Grand Total',
            'base_grand_total' => 'Order Base Grand Total',

            'total_paid' => 'Order Paid',
            'base_total_paid' => 'Base Order Paid',

            'total_refunded' => 'Order Refunded',
            'base_total_refunded' => 'Base Order Refunded',

            'total_due' => 'Order Due',
            'base_total_due' => 'Base Order Due',

            'total_qty_ordered' => 'Total Qty Items Ordered',

            'real_grand_total'=>'Grand Total(without redemption)',
            'base_real_grand_total'=>'Base Grand Total(without redemption)',

            "order_currency_code"=>"Order Currency Code",
            "base_order_currency_code"=>"Base Order Currency Code",

            'status' =>'Order Status',
            'status_last_updated' => 'Status Last Update Day',

            'shipment_date'=>'Shipment Date',
            'payment_method' => 'Order Payment Method',
            'coupon_code'=>'Coupon Code',
            'coupon_rule_name'=>'Coupon Rule Name',


            'customer_name' => 'Customer Name',
            'purchased_from' => 'Storeview',

            'item_name'=>'Item Name',
            'product_description'=>'Product Description',
            'product_on_sale'=>'On Sale',
            'base_item_price_subtotal'=>'Base item Price (Subtotal)',
            'base_item_price_total'=>'Base item Price Total',
            'base_item_price_excldiscount'=>'Base item Price (Excl. Discount)'
            
        );
    }

    /**
     * Returns the values which are identical for each row of the given order. These are
     * all the values which are not item specific: order data, shipping address, billing
     * address and order totals.
     *
     * @param Mage_Sales_Model_Order $order The order to get values from
     * @return Array The array containing the non item specific values
     */
    protected function getCommonOrderValues($order)
    {
        $data = parent::getCommonOrderValues($order);
        $_cards  = $order->getGiftCards();
        if ($_cards) {
            $_cards = unserialize($_cards);
            foreach ($_cards as $_card) {
                $gift_amounts[] = $this->formatPrice($_card['a']);
                $gift_codes[] = $_card['c'];
            }
            $gift_card_amount = implode("|",$gift_amounts);
            $gift_card_number = implode("|",$gift_codes);
        }
        $data['gift_card_amount'] = $gift_card_amount;
        $data['gift_card_number'] = $gift_card_number;
        return $data;
    }
}
?>