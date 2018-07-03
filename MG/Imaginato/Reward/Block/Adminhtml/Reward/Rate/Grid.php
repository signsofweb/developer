<?php

class Imaginato_Reward_Block_Adminhtml_Reward_Rate_Grid extends Enterprise_Reward_Block_Adminhtml_Reward_Rate_Grid
{
    /**
     * Rate text getter
     *
     * @param Varien_Object $row
     * @return string|null
     */
    public function getRateText($row)
    {
        $websiteId = $row->getWebsiteId();
        return Imaginato_Reward_Model_Reward_Rate::getRewardRateText($row,
            0 == $websiteId ? null : Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode()
        );
    }
}
