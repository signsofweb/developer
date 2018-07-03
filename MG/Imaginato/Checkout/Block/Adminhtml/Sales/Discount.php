<?php

class Imaginato_Checkout_Block_Adminhtml_Sales_Discount extends Mage_Core_Block_Template
{
    protected $_order;

    protected $_source;

    protected $_discount;

    public function _construct()
    {
        if (Mage::getStoreConfig('imaginato_checkout/general/breakdown')) {
            $this->_template = 'imaginato/sales/order/discount.phtml';
        }

        parent::_construct();
    }

    /**
     * Init discount breakdown
     *
     * @return Imaginato_Checkout_Block_Adminhtml_Sales_Discount
     */
    public function initTotals()
    {
        if (!Mage::getStoreConfig('imaginato_checkout/general/breakdown'))
            return $this;

        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        if (((float)$this->_source->getDiscountAmount()) != 0) {
            $discountTotal = new Varien_Object(array(
                'code'       => 'discount',
                'block_name' => $this->getNameInLayout()
            ));

            $this->_discount = $parent->getTotal($discountTotal->getCode());
            $parent->removeTotal($discountTotal->getCode());
            $parent->addTotal($discountTotal, 'shipping');
        }
        return $this;
    }

    /**
     * Get data (totals) source model
     *
     * @return Mage_Sales_Model_Order|Varien_Object
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Format total value based on order currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->getOrder()->formatPrice($total->getValue());
        }
        return $total->getValue();
    }

    /**
     * @return Innoexts_Warehouse_Model_Sales_Order|Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    public function hasDiscounts()
    {
        $appliedRuleIds = $this->getOrder()->getAppliedRuleIds();
        return (isset($appliedRuleIds) && !empty($appliedRuleIds));
    }

    public function breakdownDiscount()
    {
        $order = $this->getOrder();
        $quoteId = $order->getQuoteId();

        // Start store emulation process
        $storeId = $order->getStoreId();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            /** @var Innoexts_WarehouseOneStepCheckout_Model_Sales_Quote $quote */
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            $quote->setFlagFromOrderAdmin(1);
            $quote->collectTotals();

            $address = $quote->getShippingAddress();
            $fullDescr = $address->getFullDescr();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        $this->getDiscount()->setFullInfo($fullDescr);
        $this->getDiscount()->setTitle('Discount');

        return $this->getDiscount();
    }

    public function getDiscount()
    {
        return $this->_discount;
    }
}
