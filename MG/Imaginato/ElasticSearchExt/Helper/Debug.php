<?php

class Imaginato_ElasticSearchExt_Helper_Debug{
    public function enable(){
        return Mage::getStoreConfig('elasticsearch_advanced_search_settings/debug_log/enable');
    }
    public function search_min_size(){
        return (int)Mage::getStoreConfig('elasticsearch_advanced_search_settings/debug_log/search_min_size');
    }
    public function refresh_max_size(){
        return (int)Mage::getStoreConfig('elasticsearch_advanced_search_settings/debug_log/refresh_max_size');
    }
    public function addSlackLog($robot_name='Robot',$slack_message){
        $slack_message = date('Y-m-d H:i:s').'\r\n'.$slack_message;
        $_httpClient = new Varien_Http_Client();
        $_httpClient->setUri('https://hooks.slack.com/services/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')
            ->setConfig(array('timeout' => 30))
            ->setParameterPost('payload', '{"channel": "#_evisu-md", "username": "'.$robot_name.'", "text": ":bell:'.$slack_message.'", "icon_emoji": ":robot_face:"}')
            ->request(Varien_Http_Client::POST)
            ->getBody();
    }
}