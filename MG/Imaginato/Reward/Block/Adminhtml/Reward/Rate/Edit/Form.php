<?php

class Imaginato_Reward_Block_Adminhtml_Reward_Rate_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Getter
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    public function getRate()
    {
        return Mage::registry('current_reward_rate');
    }

    /**
     * Prepare form
     *
     * @return Enterprise_Reward_Block_Adminhtml_Reward_Rate_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'method' => 'post'
        ));
        $form->setFieldNameSuffix('rate');
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('enterprise_reward')->__('Reward Exchange Rate Information')
        ));

        $field = $fieldset->addField('website_id', 'select', array(
            'name'   => 'website_id',
            'title'  => Mage::helper('enterprise_reward')->__('Website'),
            'label'  => Mage::helper('enterprise_reward')->__('Website'),
            'values' => Mage::getModel('enterprise_reward/source_website')->toOptionArray(),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('customer_group_id', 'select', array(
            'name'   => 'customer_group_id',
            'title'  => Mage::helper('enterprise_reward')->__('Customer Group'),
            'label'  => Mage::helper('enterprise_reward')->__('Customer Group'),
            'values' => Mage::getModel('enterprise_reward/source_customer_groups')->toOptionArray()
        ));

        $fieldset->addField('direction', 'select', array(
            'name'   => 'direction',
            'title'  => Mage::helper('enterprise_reward')->__('Direction'),
            'label'  => Mage::helper('enterprise_reward')->__('Direction'),
            'values' => $this->getRate()->getDirectionsOptionArray()
        ));

        $rateRenderer = $this->getLayout()
            ->createBlock('enterprise_reward/adminhtml_reward_rate_edit_form_renderer_rate')
            ->setRate($this->getRate());
        $direction = $this->getRate()->getDirection();
        if ($direction == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY) {
            $fromIndex = 'points';
            $toIndex = 'currency_amount';
        } elseif ($direction == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS) {
            $fromIndex = 'currency_amount';
            $toIndex = 'points';
        } else {
            $fromIndex = 'points';
            $toIndex = 'coupon';
        }
        $fieldset->addField('rate_to_currency', 'note', array(
            'title'             => Mage::helper('enterprise_reward')->__('Rate'),
            'label'             => Mage::helper('enterprise_reward')->__('Rate'),
            'value_index'       => $fromIndex,
            'equal_value_index' => $toIndex
        ))->setRenderer($rateRenderer);

        $form->setUseContainer(true);
        $form->setValues($this->getRate()->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
