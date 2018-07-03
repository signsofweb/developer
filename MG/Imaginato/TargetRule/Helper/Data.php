<?php

class Imaginato_TargetRule_Helper_Data extends Enterprise_TargetRule_Helper_Data
{
    const XML_PATH_MAX_PRODUCT_LIST_RESULT    = 'catalog/enterprise_targetrule/max_product_list_result';
    protected $_max_product_list_result;

    public function __construct()
    {
        $this->_max_product_list_result = $this->get_max_product_list_result();
    }

    public function getMaxProductsListResult($number = 0)
    {
        if ($number == 0 || $number > $this->_max_product_list_result) {
            $number = $this->_max_product_list_result;
        }

        return $number;
    }

    protected function get_max_product_list_result(){
        return Mage::getStoreConfig(self::XML_PATH_MAX_PRODUCT_LIST_RESULT);
    }
}
