<?php
class Imaginato_SpecialPrice_Model_Record extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('imaginato_specialprice/record');
    }

    /**
     * Retrieve array of product id's for record
     *
     * array($productId)
     *
     * @return array
     */
    public function getProductIds()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('product_ids');
        if (is_null($array)) {
            $array = $this->getResource()->getProductIds($this);
            $this->setData('product_ids', $array);
        }
        return $array;
    }


    /**
     * Retrieve array of website id's for record
     *
     * array($WebsiteId)
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('website_ids');
        if (is_null($array)) {
            $array = $this->getResource()->getWebsiteIds($this);
            $this->setData('website_ids', $array);
        }
        return $array;
    }
}
