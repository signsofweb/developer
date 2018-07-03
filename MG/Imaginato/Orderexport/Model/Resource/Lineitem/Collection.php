<?php

class Imaginato_Orderexport_Model_Resource_Lineitem_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('imaginato_orderexport/lineitem');
    }

    public function getNotExistOrders($order_ids)
    {
        $this->_reset();
        $this->addFieldToFilter('order_id',array('in' => $order_ids));
        $this->getSelect()
            ->reset('columns')
            ->columns(array('order_id'))
            ->distinct(true);
        $exist_order = array();
        foreach($this->getData() as $val){
            $exist_order[] = $val['order_id'];
        }
        return array_diff($order_ids,$exist_order);
    }

    public function getItemByOrders($orders)
    {
        $this->_reset();
        $this->addFieldToFilter('order_id',array('in' => $orders));
        $this->getSelect()
            ->reset('columns')
            ->columns(array('item_id'));
        $item_ids = array();
        foreach($this->getData() as $val){
            $item_ids[] = $val['item_id'];
        }
        return $item_ids;
    }
}
