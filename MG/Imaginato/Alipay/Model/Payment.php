<?php

class Imaginato_Alipay_Model_Payment extends CosmoCommerce_Alipay_Model_Payment
{
    public function forex_refund($order, $total, $comment = '')
    {

        $baseCurrency = $order->getBaseCurrencyCode();
        if($order->getStore()->getWebsiteId()=='3'||$order->getStore()->getWebsiteId()=='4'){
            $baseCurrency = 'HKD';
        }
        $return_time = new Zend_Date();
        $parameter = array(
            'service'           => 'forex_refund',
            'partner'           => $this->getConfigData('partner_id',$order->getStoreId()),
            '_input_charset'    => 'UTF-8',
            'out_return_no'     => $order->getRealOrderId().$return_time->toString('yyyyMMddHHmmss'),
            'out_trade_no'      => $order->getRealOrderId(),
            'return_rmb_amount' => $total,
            'currency'          => $baseCurrency,
            'gmt_return'        => $return_time->toString('yyyyMMddHHmmss'),
            'reason'            => $comment?$comment:'admin retun',
        );

        $parameter = $this->para_filter($parameter);
        $security_code = trim($this->getConfigData('security_code',$order->getStoreId()));

        $arg = "";
        $sort_array = $this->arg_sort($parameter); //$parameter

        while (list ($key, $val) = each ($sort_array)) {
            $arg.=$key."=".$this->charset_encode($val,$parameter['_input_charset'])."&";
        }

        $prestr = substr($arg,0,count($arg)-2);

        $mysign = $this->sign($prestr.$security_code);

        $fields = array();
        $sort_array = $this->arg_sort($parameter); //$parameter
        while (list ($key, $val) = each ($sort_array)) {
            $fields[$key] = $this->charset_encode($val,'utf-8');
        }
        $fields['sign'] = $mysign;
        $fields['sign_type'] = 'MD5';
        $this->logTrans($fields,'Return Order');

        $httpClient = new Varien_Http_Client();
        $httpClient->setUri($this->getAlipayUrl());
        $httpClient->setParameterPost($fields);
        $response = $httpClient->request('POST')->getBody();
        $xml = simplexml_load_string($response, null, LIBXML_NOERROR);
        if((String)$xml->is_success=='F'){
            Mage::getSingleton('adminhtml/session')->addError(
                'Alipay Return Error:'.(String)$xml->error
            );
            return false;
        }
        return true;
    }

    public function trade_query($order)
    {

        $parameter = array(
            'service'           => 'single_trade_query',
            'partner'           => '2088021479925140',//$this->getConfigData('partner_id',$order->getStoreId()),
            '_input_charset'    => 'UTF-8',
            'out_trade_no'      => '600000961'//$order->getRealOrderId(),
        );

        $parameter = $this->para_filter($parameter);
        $security_code = trim($this->getConfigData('security_code',$order->getStoreId()));

        $arg = "";
        $sort_array = $this->arg_sort($parameter); //$parameter
        while (list ($key, $val) = each ($sort_array)) {
            $arg.=$key."=".$this->charset_encode($val,$parameter['_input_charset'])."&";
        }
        $prestr = substr($arg,0,count($arg)-2);
        $mysign = $this->sign($prestr.$security_code);
        $fields = array();
        $sort_array = $this->arg_sort($parameter); //$parameter
        while (list ($key, $val) = each ($sort_array)) {
            $fields[$key] = $this->charset_encode($val,'utf-8');
        }
        $fields['sign'] = $mysign;
        $fields['sign_type'] = 'MD5';
        $this->logTrans($fields,'Trade Query');

        Mage::log('Trade Query',1,'alipay_trade_query.log');
        Mage::log($fields,1,'alipay_trade_query.log');

        $http = new Varien_Http_Adapter_Curl();
        $config = array('timeout' => 60);
        $http->setConfig($config);
        $http->write(
            Zend_Http_Client::POST,
            $this->getAlipayUrl(),
            '1.1',
            array(),
            http_build_query($fields)
        );
        $response = $http->read();

        Mage::log('Trade Query Result',1,'alipay_trade_query.log');
        Mage::log($response,1,'alipay_trade_query.log');

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = simplexml_load_string(trim($response[1]));

        if((String)$response->is_success=='T' && (String)$response->response->trade->trade_status=='TRADE_FINISHED'){
            return true;
        }
        return false;
    }

    public function reconciliation(){
        $partners = array();
        foreach(Mage::app()->getStores() as $store){
            $store_id =$store->getId();
            $partner       = trim($this->getConfigData('partner_id',   $store_id));
            $security_code = trim($this->getConfigData('security_code',$store_id));
            $partners[$partner]['partner'] = $partner;
            $partners[$partner]['security_code'] = $security_code;
        }
        foreach($partners as $partner_data){
            $this->reconciliation_data($partner_data['partner'],$partner_data['security_code']);
        }
    }
    private function reconciliation_data($partner,$security_code)
    {
        $yesterday = date('Ymd',strtotime("-1day"));
        $parameter = array(
            'service'           => 'forex_compare_file',
            'partner'           => $partner,
            'start_date'        => $yesterday,
            'end_date'          => $yesterday
        );

        $parameter = $this->para_filter($parameter);

        $arg = "";
        $sort_array = $this->arg_sort($parameter); //$parameter

        while (list ($key, $val) = each ($sort_array)) {
            $arg.=$key."=".$this->charset_encode($val,$parameter['_input_charset'])."&";
        }

        $prestr = substr($arg,0,count($arg)-2);

        $mysign = $this->sign($prestr.$security_code);

        $fields = array();
        $sort_array = $this->arg_sort($parameter); //$parameter
        while (list ($key, $val) = each ($sort_array)) {
            $fields[$key] = $this->charset_encode($val,'utf-8');
        }
        $fields['sign'] = $mysign;
        $fields['sign_type'] = 'MD5';

        $httpClient = new Varien_Http_Client();
        $httpClient->setUri($this->getAlipayUrl());
        $httpClient->setParameterPost($fields);
        $response = $httpClient->request('POST')->getBody();
        $out_file = Mage::getBaseDir('export') . "/alipay_reconciliation-{$yesterday}-{$parameter['partner']}.txt";
        file_put_contents($out_file,$response);
        $response_array = explode("\r\n",trim($response));

        $alipay = Mage::getModel('alipay/payment');
        foreach($response_array as $order_str){
            $order_array = explode('|',$order_str);
            if($order_array[5]=='P' && $order_array[7]=='P'){
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($order_array[0]);
                if ($order->getState() == 'new' || $order->getState() == 'new' || $order->getStatus() == 'alipay_wait_buyer_confirm_goods' || $order->getStatus() == 'alipay_wait_buyer_pay') {
                    $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $sendemail=$alipay->getConfigData('sendemail',$order->getStoreId());
                    if($sendemail){
                        $order->sendOrderUpdateEmail(true,Mage::helper('alipay')->__('TRADE_FINISHED'));
                    }
                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_PROCESSING,
                        Mage::helper('alipay')->__('TRADE_FINISHED'));
                    try{
                        $order->save();
                    } catch(Exception $e){

                    }
                }
            }
        }
    }
}