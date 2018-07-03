<?php

class Imaginato_MailChimp_Block_Adminhtml_Newsletter_Subscriber_Grid
    extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    /**
     * Prepare columns
     * 
     * @return self
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('imaginato_mailchimp');

        if($helper->getGroupField(0)){
            $this->addColumnAfter(
                'cycle_group', array(
                'header' => Mage::helper('newsletter')->__('Mailchimp Cycle Group'),
                'index' => 'mailchimp_sync_cycle_group',
                'type' => 'options',
                'options'   => $helper->_getCycleGroupOptions()
            ), 'lastname'
            );
        }

        if($helper->getContentField(0)){
            $this->addColumnAfter(
                'content_group', array(
                'header' => Mage::helper('newsletter')->__('Mailchimp Content Group'),
                'index' => 'mailchimp_sync_content_group',
                'filter' => 'imaginato_mailchimp/system_group_template_grid_filter',
                'renderer' => 'imaginato_mailchimp/system_group_template_grid_renderer',
                'options'   => $helper->_getContentGroupOptions()
            ), 'cycle_group'
            );
        }
        return parent::_prepareColumns();
    }
}