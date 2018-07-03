<?php

class Imaginato_TargetRule_Model_Product
{

    public function cron()
    {
        $rule_resource = Mage::getResourceModel('enterprise_targetrule/rule');
        $rule_resource->cronFlush();
    }
}
