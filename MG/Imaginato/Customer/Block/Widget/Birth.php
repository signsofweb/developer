<?php

class Imaginato_Customer_Block_Widget_Birth extends Mage_Customer_Block_Widget_Abstract
{

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('customer/widget/birth.phtml');
    }

    public function isEnabled()
    {
        return $this->showYear()||$this->showMonth();
    }

    public function isRequired()
    {
        return $this->isYearRequired()||$this->isMonthRequired();
    }

    public function showYear()
    {
        return (bool)$this->_getAttribute('bir_year')->getIsVisible();
    }

    public function isYearRequired()
    {
        return (bool)$this->_getAttribute('bir_year')->getIsRequired();
    }

    public function showMonth()
    {
        return (bool)$this->_getAttribute('bir_month')->getIsVisible();
    }

    public function isMonthRequired()
    {
        return (bool)$this->_getAttribute('bir_month')->getIsRequired();
    }

    public function getYearOptions(){
        $current_year = date('Y');
        $options = array();
        for($current_year;$current_year>=1900;$current_year--){
            $options[] = $current_year;
        }
        return $options;
    }

    public function getMonthOptions(){
        $options = array();
        for($i=1;$i<=12;$i++){
            $options[] = $i;
        }
        return $options;
    }
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? $this->__($attribute->getStoreLabel()) : '';
    }
}
