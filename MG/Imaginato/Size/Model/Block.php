<?php
/**
 * Size chart block model
 *
 * @method Imaginato_Size_Model_Resource_Block _getResource()
 * @method Imaginato_Size_Model_Resource_Block getResource()
 * @method string getTitle()
 * @method Imaginato_Size_Model_Block setTitle(string $value)
 * @method string getIdentifier()
 * @method Imaginato_Size_Model_Block setIdentifier(string $value)
 * @method string getContent()
 * @method Imaginato_Size_Model_Block setContent(string $value)
 * @method string getCreationTime()
 * @method Imaginato_Size_Model_Block setCreationTime(string $value)
 * @method string getUpdateTime()
 * @method Imaginato_Size_Model_Block setUpdateTime(string $value)
 * @method int getIsActive()
 * @method Imaginato_Size_Model_Block setIsActive(int $value)
 *
 * @package     Imaginato_Size
 */

class Imaginato_Size_Model_Block extends Mage_Core_Model_Abstract
{
    /**
     * @var string
     */
    const CACHE_TAG = 'size_block';

    /**
     * @var string
     */
    protected $_cacheTag = 'size_block';

    /**
     * Is model readonly
     *
     * @var bool
     */
    protected $_isReadonly = false;

    /**
     * Retrieve array of product id's for chart
     *
     * array($productId)
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('products');
        if (is_null($array)) {
            $array = $this->getResource()->getRelatedProducts($this);
            $this->setData('products', $array);
        }
        return $array;
    }

    /**
     * Get products ids by chart block id
     *
     * @param int $chartId
     * @return array
     */
    public function getRelatedProductsByChart($chartId)
    {
        if (!$this->hasRelatedProductsChart()) {
            $products = $this->getResource()->getRelatedProductsByChart($chartId);
            $this->setRelatedProductsChart($products);
        }
        return $this->_getData('related_products_chart');
    }

    /**
     * Get chart id by product id
     *
     * @param int $productId
     * @return array
     */
    public function getChartByProductId($productId)
    {
        if (!$this->hasChartByProductId()) {
            $charts = $this->getResource()->getChartByRelatedProductId($productId);
            $this->setChartByProductId($charts);
        }
        return $this->_getData('chart_by_product_id');
    }

    /**
     * Check if rule is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is readonly flag to rule
     *
     * @param bool $value
     *
     * @return Imaginato_Size_Model_Block
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (bool)$value;
        return $this;
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('size/block');
    }

    /**
     * Prevent blocks recursion
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $needle = 'block_id="' . $this->getBlockId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::_beforeSave();
        }
        Mage::throwException(
            Mage::helper('size')->__('The static block content cannot contain  directive with its self.')
        );
    }

    /**
     * Save banner content, bind banner to catalog and sales rules after banner save
     *
     * @return Imaginato_Size_Model_Block|Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        //related_products_chart
        if ($this->hasRelatedProductsChart()) {
            $this->_getResource()->saveRelatedProductsChart(
                $this->getId(),
                $this->getRelatedProductsChart()
            );
        }
        return parent::_afterSave();
    }
}
