<?php

class Imaginato_MailChimp_Model_Observer
{

    public function alterNewsletterGrid($observer)
    {

        $block = $observer->getEvent()->getBlock();
        if (!isset($block)) {
            return $this;
        }
        if (!$this->mailchimpHelper()->isMailChimpEnabled(0)) {
            return $this;
        }

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter) {
            $subscriber = Mage::registry('subscriber');
            if ($customer_cycle_group = $subscriber->getData('mailchimp_sync_cycle_group')) {
                $block->getForm()->getElement('base_fieldset')->addField('mailchimp_sync_cycle_group', 'label',
                    array(
                        'label' => Mage::helper('customer')->__('Mailchimp Cycle Group:'),
                        'value' => ($this->imaginatoMailchimpHelper()->_getCycleGroupOptions()[$customer_cycle_group])
                    )
                );
            }
            if ($customer_content_group = $subscriber->getData('mailchimp_sync_content_group')) {
                $contentGroup = $this->imaginatoMailchimpHelper()->_getContentGroupOptions();
                $contents = array();
                foreach (explode(',', $customer_content_group) as $content_id) {
                    $contents[] = $contentGroup[$content_id];
                }
                $block->getForm()->getElement('base_fieldset')->addField('mailchimp_sync_content_group', 'label',
                    array(
                        'label' => Mage::helper('customer')->__('Mailchimp Content Group:'),
                        'value' => implode('|', $contents)
                    )
                );
            }
        }

        return $observer;
    }

    /**
     * @return Ebizmarts_MailChimp_Helper_Data|Mage_Core_Helper_Abstract
     */
    private function mailchimpHelper()
    {
        return Mage::helper('mailchimp');

    }

    /**
     * @return Imaginato_MailChimp_Helper_Data|Mage_Core_Helper_Abstract
     */
    private function imaginatoMailchimpHelper()
    {
        return Mage::helper('imaginato_mailchimp');
    }

    /**
     * Add column to associate orders gained from MailChimp campaigns and automations.
     *
     * @param  $observer
     * @return mixed
     */
    public function addColumnToSalesOrderGrid($observer)
    {
        $scopeArray = explode('-', $this->mailchimpHelper()->getScopeString());
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid
            && $this->mailchimpHelper()->getMonkeyInGrid($scopeArray[1], $scopeArray[0])
            && ($this->mailchimpHelper()->isAbandonedCartEnabled($scopeArray[1], $scopeArray[0])
                || $this->mailchimpHelper()->isMailChimpEnabled($scopeArray[1], $scopeArray[0]))
        ) {
            $block->addColumnAfter(
                'mailchimp_flag', array(
                'header'                    => $this->mailchimpHelper()->__('MailChimp'),
                'index'                     => 'mailchimp_flag',
                'type'                      => 'options',
                'options'                   => array(
                    '1' => Mage::helper('catalog')->__('Yes'),
                    '0' => Mage::helper('catalog')->__('No'),
                ),
                'align'                     => 'center',
                'filter'                    => 'imaginato_mailchimp/adminhtml_widget_grid_column_filter_mailchimp',
                'filter_condition_callback' => array($this, 'mailchimpColumnFilterCallback'),
                'renderer'                  => 'mailchimp/adminhtml_sales_order_grid_renderer_mailchimp',
                'sortable'                  => false,
                'width'                     => 70,
                'relation'                  => array(
                    'table_alias'    => 'mailchimp_ids_table',
                    'table_name'     => 'sales/order',
                    'fk_field_name'  => 'entity_id',
                    'ref_field_name' => 'entity_id',
                    'field_name'     => 'mailchimp_campaign_id',
                ),
            ), 'created_at'
            );
        }

        return $observer;
    }

    /**
     * Get column filter to collection
     *
     * @param Mage_Sales_Model_Resource_Order_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    public function mailchimpColumnFilterCallback($collection, $column)
    {
        $this->addColumnRelationToCollection($collection, $column);
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }
        return $this;
    }

    /**
     * Add column relation to collection
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return self
     */
    protected function addColumnRelationToCollection($collection, $column)
    {
        if (!$column->getRelation()) {
            return $this;
        }
        $relation = $column->getRelation();
        $fieldAlias = $column->getId();
        $fieldName = $relation['field_name'];
        $fkFieldName = $relation['fk_field_name'];
        $refFieldName = $relation['ref_field_name'];
        $tableAlias = $relation['table_alias'];
        $table = $collection->getTable($relation['table_name']);
        $collection->addFilterToMap($fieldAlias, $tableAlias . '.' . $fieldName);
        $collection->getSelect()->joinLeft(
            array($tableAlias => $table),
            '(main_table.' . $fkFieldName . ' = ' . $tableAlias . '.' . $refFieldName . ')',
            array($fieldAlias => $tableAlias . '.' . $fieldName)
        );
        return $this;
    }

    /**
     * For existing customer on updating/save customer address data
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerAddressSync($observer)
    {
        /** @var Mage_Customer_Model_Address $address */
        $address = $observer->getEvent()->getCustomerAddress();

        $storeId = Mage::app()->getStore()->getId();
        if (!$this->mailchimpHelper()->isMailChimpEnabled($storeId) || !$address->hasDataChanges()) {
            return $this;
        }

        $customerId = $address->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (($customer instanceof Mage_Customer_Model_Customer) && !$customer->isObjectNew()) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if ($subscriber && $subscriber->getId()) {
                //update customer data
                $this->customerSyncMailchimpData($subscriber, true);
            }
        }
    }

    /**
     * @param $subscriber
     * @param bool $updateStatus If set to true, it will force the status update even for those already subscribed.
     */
    protected function customerSyncMailchimpData($subscriber, $updateStatus = false)
    {
        $storeId = $subscriber->getStoreId();
        $listId = $this->mailchimpHelper()->getGeneralList($storeId);
        $newStatus = $this->mailchimpApiSubscriber()->translateMagentoStatusToMailchimpStatus($subscriber->getStatus(), $storeId);
        $forceStatus = ($updateStatus) ? $newStatus : null;
        $api = $this->mailchimpHelper()->getApi($storeId);
        $mergeVars = $this->mailchimpApiSubscriber()->getMergeVars($subscriber);
        $md5HashEmail = md5(strtolower($subscriber->getSubscriberEmail()));
        try {
            $api->lists->members->addOrUpdate(
                $listId, $md5HashEmail, $subscriber->getSubscriberEmail(), $newStatus, null, $forceStatus, $mergeVars,
                null, null, null, null
            );
            $subscriber->setData("mailchimp_sync_delta", Varien_Date::now());
            $subscriber->setData("mailchimp_sync_error", "");
            $subscriber->setData("mailchimp_sync_modified", 0);
        } catch (MailChimp_Error $e) {
            $this->mailchimpHelper()->logError($e->getFriendlyMessage(), $storeId);
        } catch (Exception $e) {
            $this->mailchimpHelper()->logError($e->getMessage(), $storeId);
        }
    }

    /**
     * @return Ebizmarts_MailChimp_Model_Api_Subscribers|Mage_Core_Model_Abstract
     */
    protected function mailchimpApiSubscriber()
    {
        return Mage::getModel('mailchimp/api_subscribers');
    }

    /**
     * For existing customer on save customer data
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerSyncMailchimp($observer)
    {
        $storeId = Mage::app()->getStore()->getId();
        if (!$this->mailchimpHelper()->isMailChimpEnabled($storeId)) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer) && !$customer->isObjectNew()) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if ($subscriber && $subscriber->getId()) {
                //update customer data
                $this->customerSyncMailchimpData($subscriber, true);
            }
        }

        return $this;
    }

    /**
     * For new customer, on registration
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerSaveBefore($observer)
    {
        $storeId = Mage::app()->getStore()->getId();
        if (!$this->mailchimpHelper()->isMailChimpEnabled($storeId)) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer) && $customer->isObjectNew()) {

            //set every new customer for auto subscribe and trigger mailchimp subscription event
            //newsletter_subscriber_save_before
            $customer->setIsSubscribed(true);
        }
        return $this;
    }

    /**
     * On new order, after placing order, subscribe customer
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onNewOrder($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        $storeId = Mage::app()->getStore()->getStoreId();

        if ($this->mailchimpHelper()->isMailChimpEnabled($storeId)) {
            $customer = $order->getCustomer();
            if (($customer instanceof Mage_Customer_Model_Customer) ) {
                //set subscribe
                $customer->setIsSubscribed(true);

                //subscribe and trigger mailchimp subscription event
                //newsletter_subscriber_save_before
                Mage::getModel('newsletter/subscriber')->subscribeCustomer($customer);
            }
        }

        return $this;
    }
}
