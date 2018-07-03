<?php

namespace Convert\Newsletter\Controller\EditSubcriber;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
class Save extends \Magento\Newsletter\Controller\Manage\Save

{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $storeManager;
    protected $customerRepository;
    protected $subscriberFactory;
    protected $customerSession;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        Validator $formKeyValidator,
        StoreManagerInterface $storeManager,
        CustomerRepository $customerRepository,
        SubscriberFactory $subscriberFactory,
        JsonFactory $resultJsonFactory,
        Subscriber $subscriber
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subscriber = $subscriber;
        $this->customerSession = $customerSession;   
        parent::__construct($context, $customerSession,$formKeyValidator,$storeManager,$customerRepository,$subscriberFactory,$resultJsonFactory);
    }

    
    public function execute()
    {
        $interest_form = $this->getRequest()->getParam('upgrade_newletter');
        $program_form = $this->getRequest()->getParam('customer_program');
        $general_health = $this->getRequest()->getParam('general_health');
        $weight_management = $this->getRequest()->getParam('weight_management');
        $superfoods = $this->getRequest()->getParam('superfoods');
        $kids_health = $this->getRequest()->getParam('kids_health');
        $e50_health = $this->getRequest()->getParam('e50_health');
        $program = $this->getRequest()->getParam('ten-week');
        $pro_2_week = $this->getRequest()->getParam('maintenance');
        $pro_4_week = $this->getRequest()->getParam('intensive');
        $interests = [];
        $programs = [];
        $customerId = $this->customerSession->getCustomer()->getId();
        $email = $this->customerSession->getCustomer()->getEmail();
        $name = $this->customerSession->getCustomer()->getName();

        if ($customerId === null) {
            $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
        } else {

            try {
                if(isset($interest_form)){
                    if (isset($general_health) && $general_health == 'on'){
                        $interests[] = 'General Health';
                    }else{
                        $interests[] = '';
                    }
                    if (isset($weight_management) && $weight_management == 'on'){
                        $interests[] = 'Weight Management';
                    }else{
                        $interests[] = '';
                    }
                    if (isset($superfoods) && $superfoods  == 'on'){
                        $interests[] = 'SuperFoods';
                    }else{
                        $interests[] = '';
                    }
                    if (isset($kids_health) && $kids_health == 'on'){
                        $interests[] = 'Kids Health';
                    }else{
                        $interests[] = '';
                    }
                    if (isset($e50_health) && $e50_health == 'on'){
                        $interests[] = '50+ Health';
                    }else{
                        $interests[] = '';
                    }

                    $interests = implode(', ',$interests);
                    $interests = trim($interests);
                    $customer = $this->customerRepository->getById($customerId);
                    $storeId = $this->storeManager->getStore()->getId();
                    $customer->setStoreId($storeId);
                    $this->customerRepository->save($customer);
                    $checkSubscriber = $this->subscriber->loadByEmail($email);
                    if ($checkSubscriber->isSubscribed()) {
                        try{
                                $subscriber = $this->subscriberFactory->create();
                                $status = $subscriber->subscribe($email, $name, $interests);
                                $subscriber->updateSubscription($customerId)->setInterests($interests)->save();
                                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                                   $this->messageManager->addError(__('The confirmation request has been sent'));
                                } else {
                                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                                }
                                $subscriber->setInterests($interests)->save();
                        }catch (\Exception $e){
                            $this->messageManager->addException($e, __('Some thing wrong.'));
                            return $this->_redirect($this->_redirect->getRefererUrl());

                        }
                    }else{
                        try{
                                $subscriber = $this->subscriberFactory->create();
                                $status = $subscriber->subscribe($email, $name, $interests);
                                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                                   $this->messageManager->addError(__('The confirmation request has been sent'));
                                } else {
                                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                                }
                                $subscriber->setInterests($interests)->save();
                        }catch (\Exception $e){
                            $this->messageManager->addException($e, __('Some thing wrong.'));
                            return $this->_redirect($this->_redirect->getRefererUrl());

                        }
                    }
                }else{

                    if (isset($program) && $program == 'on'){
                        $programs[] = '10+ Week Steady';
                    }
                    if(isset($pro_2_week) && $pro_2_week == 'on'){
                        $programs[] = '2 Week Intensive';
                    }
                    if(isset($pro_4_week) && $pro_4_week == 'on'){
                        $programs[] = '4 Week Maintenance';
                    }
                    $programs = implode(', ',$programs);
                    $programs = trim($programs);
                    $customer = $this->customerRepository->getById($customerId);
                    $storeId = $this->storeManager->getStore()->getId();
                    $customer->setStoreId($storeId);
                    $this->customerRepository->save($customer);
                    
                    $checkSubscriber = $this->subscriber->loadByEmail($email);
                    if ($checkSubscriber->isSubscribed()) {
                        try{
                          $subscriber = $this->subscriberFactory->create();
                          $status = $subscriber->subscribe($email, $name);
                          $subscriber->updateSubscription($customerId)->setProgram($programs)->save();
                          if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                               $this->messageManager->addError(__('The confirmation request has been sent'));
                            } else {
                                $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                               }
                               $subscriber->setProgram($programs)->save();
                        }catch (\Exception $e){
                            echo $e;
                            $this->messageManager->addException($e, __('Some thing wrong.'));
                            return $this->_redirect($this->_redirect->getRefererUrl());
                        }
                    }else{
                        try{
                          $subscriber = $this->subscriberFactory->create();
                          $status = $subscriber->subscribe($email, $name);
                          
                          if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                               $this->messageManager->addError(__('The confirmation request has been sent'));
                            } else {
                                $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                               }
                               $subscriber->setProgram($program)->save();
                        }catch (\Exception $e){
                            $this->messageManager->addException($e, __('Some thing wrong.'));
                            return $this->_redirect($this->_redirect->getRefererUrl());

                        }
                    }
                    
                }
                
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
            }
        }
        $this->_redirect('newsletter/manage/');
    }

}
