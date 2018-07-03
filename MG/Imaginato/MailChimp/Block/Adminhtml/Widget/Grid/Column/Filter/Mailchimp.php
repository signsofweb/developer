<?php

class Imaginato_MailChimp_Block_Adminhtml_Widget_Grid_Column_Filter_Mailchimp
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    /**
     * Get condition
     *
     * @return array
     */
    public function getCondition()
    {
        $value = trim($this->getValue());
        if ($value == '0') {
            return array('null' => true);
        } else {
            return array('notnull' => true);
        }
    }
}