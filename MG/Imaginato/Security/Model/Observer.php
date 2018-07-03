<?php

class Imaginato_Security_Model_Observer
{

    public function checkAdminUrl($observer){
        if(strpos(Mage::app()->getRequest()->getRequestUri(),Mage::app()->getStore(0)->getCode()) == false){
            require_once(Mage::getBaseDir() . DS . 'errors' . DS . '503.php');
            exit();
        }
        return $this;
    }
    /**
     * Log marked actions
     *
     * @param Varien_Event_Observer $observer
     */
    public function controllerPredispatch($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if(empty($user)){
            $user = new Varien_Object();
        }
        $type = 'view: ';
        if($observer->getEvent()->getControllerAction()->getRequest()->isPost()){
            $type .= 'post';
        }else{
            $type .= 'get';
        }
        $this->addViewLog($type,$user);
        return $this;
    }

    /**
     * Log successful admin sign in
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSessionLoginSuccess($observer)
    {
        $this->addViewLog('LoginSuccess',$observer->getUser());
        $this->checkLoginIp();
        return $this;
    }

    /**
     * Log failure of sign in
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSessionLoginFailed($observer)
    {
        $user = new Varien_Object();
        $user->addData(Mage::app()->getRequest()->getPost('login'));
        $this->addViewLog('LoginFailed',$user);
        $this->checkLoginIp();
        return $this;
    }

    protected function addViewLog($type,$user)
    {
        $model = Mage::getModel('imaginato_security/adminlog');
        $model->setData('log_type',$type);
        $model->setData('session_id',Mage::getSingleton('core/session')->getSessionId());
        $model->setData('customer_id',$user->getId());
        $model->setData('user_name',$user->getUsername());
        $model->setData('user_email',$user->getEmail());
        $helper = Mage::helper('core/http');
        $model->setData('ip',$helper->getRemoteAddr());
        $model->setData('url',$helper->getRequestUri(true));
        $model->setData('http_referer',$helper->getHttpReferer(true));
        $model->setData('http_user_agent',$helper->getHttpUserAgent(true));
        $model->setData('http_accept_language',$helper->getHttpAcceptLanguage(true));
        $model->setData('visit_at',Mage::getSingleton('core/date')->gmtDate());
        $model->save();
    }

    protected function checkLoginIp(){
        if(Mage::getStoreConfig('admin/security/login_exception_alert_status')){
            $localeIp = Mage::helper('core/http')->getRemoteAddr();
            $whiteListIp = $this->getCommaseparatedConfig('admin/security/white_list_ips');
            if($whiteListIp && !in_array($localeIp,$whiteListIp)){

                $sender = Mage::getStoreConfig('admin/security/login_exception_alert_email_sender');
                $copyTo = $this->getCommaseparatedConfig('admin/security/login_exception_alert_email_copyto');
                $templateId = Mage::getStoreConfig('admin/security/login_exception_alert_email_template');
                if(empty($sender) || empty($copyTo) || empty($templateId)){
                    return;
                }
                $user = Mage::getSingleton('admin/session')->getUser();
                if(empty($user)){
                    $loginData = Mage::app()->getRequest()->getPost('login');
                    $user = Mage::getModel("admin/user");
                    $user->setData('firstname',$loginData['username']);
                    $user->setData('name',$loginData['username']);
                    $user->setData('username',$loginData['username']);
                    $user->setData('exception_type','login failed');
                }else{
                    $user->setData('exception_type','accessed');
                }
                $user->setData('login_time',Mage::getSingleton('core/date')->gmtDate());
                $user->setData('login_ip',Mage::helper('core/http')->getRemoteAddr());

                $mailer = Mage::getModel('core/email_template_mailer');
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($user->getEmail()?$user->getEmail():$copyTo[0], $user->getUsername());
                if ($copyTo) {
                    foreach ($copyTo as $email) {
                        $emailInfo->addBcc($email);
                    }
                }
                $mailer->addEmailInfo($emailInfo);
                $mailer->setSender($sender);
                $mailer->setStoreId(0);
                $mailer->setTemplateId($templateId);
                $mailer->setTemplateParams(array('user'=>$user));
                $mailer->send();
            }
        }
    }

    protected function getCommaseparatedConfig($configPath)
    {
        $data = Mage::getStoreConfig($configPath);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
}
