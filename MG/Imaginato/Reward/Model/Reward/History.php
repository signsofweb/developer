<?php

class Imaginato_Reward_Model_Reward_History extends Enterprise_Reward_Model_Reward_History
{
    public function getMessage()
    {
        if (!$this->hasData('message')) {
            $action = Mage::getSingleton('enterprise_reward/reward')->getActionInstance($this->getAction());
            $message = '';
            if ($action !== null) {
                $message = $action->getHistoryMessage($this->getAdditionalData());
            }
            if($comment = $this->_getData('comment')){
                $message .= "({$comment})";
            }
            $this->setData('message', $message);
        }
        return $this->_getData('message');
    }

    public function getIncrementId(){
        return $this->getAdditionalDataByKey('increment_id');
    }
}
