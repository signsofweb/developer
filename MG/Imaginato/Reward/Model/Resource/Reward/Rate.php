<?php

class Imaginato_Reward_Model_Resource_Reward_Rate extends Enterprise_Reward_Model_Resource_Reward_Rate
{

    /**
     * Retrieve rate data bu given params or empty array if rate with such params does not exists
     *
     * @param integer $websiteId
     * @param integer $customerGroupId
     * @param integer $direction
     * @param integer $coupon
     * @return array
     */
    public function get_rate_data($websiteId, $customerGroupId, $direction, $coupon)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('website_id = :website_id')
            ->where('customer_group_id = :customer_group_id')
            ->where('direction = :direction')
            ->where('coupon = :coupon');
        $bind = array(
            ':website_id'        => (int)$websiteId,
            ':customer_group_id' => (int)$customerGroupId,
            ':direction'         => $direction,
            ':coupon'         => $coupon
        );
        $data = $this->_getReadAdapter()->fetchRow($select, $bind);
        if ($data) {
            return $data;
        }

        return array();
    }
}
