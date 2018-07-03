<?php

class Imaginato_Orderexport_Model_Export_Chinaexport extends Imaginato_Orderexport_Model_Export_Lineitem
{

	public function getAllowColumns(){ 
         return [
         	'increment_id',
         	'item_sku',
         	'customer_name',
         	'shipping_telephone',
         	'shipping_street',
         	'shipping_city',
         	'shipping_state_name',
         	'shipping_zipcode', 
         	'item_qty_ordered', 
         	'base_item_total'
         ];
    
	}


	public function getHeaderRowValues(){ 
         return [
         	'increment_id'=> 'Order Number',
         	'item_sku'=>'SKU',
         	'customer_name'=>'Customer Name',
         	'shipping_telephone'=>'Shipping Phone Number',
         	'shipping_street'=>'Shipping Street',
         	'shipping_city'=>'Shipping City',
         	'shipping_state_name'=>'Shipping State',
         	'shipping_zipcode'=>'Shipping Zip',
         	'i'=>'',
         	'j'=>'',
         	'k'=>'', 
         	'item_qty_ordered'=>'Item Qty',
         	'm'=>'',
         	'n'=>'',
         	'o'=>'',
         	'p'=>'',
         	'q'=>'',
         	'r'=>'',
         	's'=>'',
         	't'=>'',
         	'u'=>'',
         	'v'=>'',
         	'w'=>'',
         	'x'=>'',
         	'base_item_total'=>'Base Order Paid'
         ];
    }
    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeaderRowValues(), self::DELIMITER, self::ENCLOSURE);
    }
	 protected function writeOrder($order, $fp)
    {
        if(empty($order)){
            return;
        }

        $output_columns = array_keys($this->getHeaderRowValues()); 
        $select_columns = $this->getAllowColumns();  
        $collection = Mage::getModel('imaginato_orderexport/lineitem')->getCollection();
        $collection->getSelect()
            ->where('order_id in (?)',$order)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($select_columns)
            ->order(array('order_id', 'item_id'));  
        foreach ($collection->getData() as $data) { 
            $record_in_sort = array(); 
            foreach ($output_columns as $output) {
            	if(in_array($output, $select_columns)){
            		$record_in_sort[$output] = $data[$output];
            	}else{
            		$record_in_sort[$output] = '';
            	} 
            }
            fputcsv($fp, $record_in_sort, self::DELIMITER, self::ENCLOSURE);
        }
    }
}