<?php

class Imaginato_ImportExport_Model_Product_Attribute_Source_CommissioningCategory extends EbayEnterprise_Affiliate_Model_Product_Attribute_Source_CommissioningCategory
{
    private static $_allOptions;

    public function getAllOptions()
    {
        if(!self::$_allOptions){
            self::$_allOptions = parent::getAllOptions();
        }
        return self::$_allOptions;
    }
}
