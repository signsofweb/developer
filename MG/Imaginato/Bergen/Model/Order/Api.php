<?php

class Imaginato_Bergen_Model_Order_Api extends Mage_Sales_Model_Order_Api
{
    /**
     * Retrieve list of orders. Filtration could be applied
     *
     * @param null|object|array $filters
     * @return array
     */
    public function items($filters = null)
    {
        $orders = array();

        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        /** @var $orderCollection Mage_Sales_Model_Mysql4_Order_Collection */
        $orderCollection = Mage::getModel("sales/order")->getCollection();
        $orderCollection->addFieldToFilter('sync_bergen_status', 1);
        $billingFirstnameField = "$billingAliasName.firstname";
        $billingLastnameField = "$billingAliasName.lastname";
        $shippingFirstnameField = "$shippingAliasName.firstname";
        $shippingLastnameField = "$shippingAliasName.lastname";
        $orderCollection->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect('billing_firstname', "{{billing_firstname}}",
                array('billing_firstname' => $billingFirstnameField))
            ->addExpressionFieldToSelect('billing_lastname', "{{billing_lastname}}",
                array('billing_lastname' => $billingLastnameField))
            ->addExpressionFieldToSelect('shipping_firstname', "{{shipping_firstname}}",
                array('shipping_firstname' => $shippingFirstnameField))
            ->addExpressionFieldToSelect('shipping_lastname', "{{shipping_lastname}}",
                array('shipping_lastname' => $shippingLastnameField))
            ->addExpressionFieldToSelect('billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                array('billing_firstname' => $billingFirstnameField, 'billing_lastname' => $billingLastnameField))
            ->addExpressionFieldToSelect('shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                array('shipping_firstname' => $shippingFirstnameField, 'shipping_lastname' => $shippingLastnameField)
        );

        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters, $this->_attributesMap['order']);
        try {
            foreach ($filters as $field => $value) {
                $orderCollection->addFieldToFilter($field, $value);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        foreach ($orderCollection as $order) {
            $orders[] = $this->_getAttributes($order, 'order');
        }
        return $orders;
    }
}
