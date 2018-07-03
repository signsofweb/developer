<?php

class Imaginato_MailChimp_Block_System_Group_Template_Grid_Filter
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{

    public function getCondition()
    {
        if(is_null($this->getValue())) {
            return null;
        }

        return array('like' => '%'.$this->getValue().'%');
    }
}
