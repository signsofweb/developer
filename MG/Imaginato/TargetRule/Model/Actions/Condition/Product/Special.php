<?php

class Imaginato_TargetRule_Model_Actions_Condition_Product_Special
    extends Enterprise_TargetRule_Model_Actions_Condition_Product_Special
{

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = array(
            array(
                'value' => 'enterprise_targetrule/actions_condition_product_special_price',
                'label' => Mage::helper('enterprise_targetrule')->__('Price (percentage)')
            ),
            array(
                'value' => 'imaginato_targetrule/actions_condition_product_special_category',
                'label' => Mage::helper('enterprise_targetrule')->__('Category (level)')
            )
        );

        return array(
            'value' => $conditions,
            'label' => Mage::helper('enterprise_targetrule')->__('Product Special')
        );
    }
}
