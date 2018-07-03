<?php

namespace Convert\Newsletter\Controller\ProgramAction;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Newsletter\Model\Subscriber;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $customerAccountManagement;

	protected $resultJsonFactory;

	
    public function __construct(
		Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        CustomerRepository $customerRepository,
        Subscriber $subscriber,
        JsonFactory $resultJsonFactory,
        Customer $customer
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory; 
        $this->storeManager = $storeManager; 
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->subscriber = $subscriber;
        $this->customer = $customer;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customer
        );	
    }
    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(){
        $email = $this->getRequest()->getParam('email');
        $name = $this->getRequest()->getParam('name');
        $intensive = $this->getRequest()->getParam('intensive');
        $steady = $this->getRequest()->getParam('steady');
        $maintennace = $this->getRequest()->getParam('maintennace');
        $checkSubscriber = $this->subscriber->loadByEmail($email);
        $program_item = $checkSubscriber->getProgram();
        $pro = explode(", ",$program_item);
        $program = $pro;
        
        if(isset($intensive) && $intensive == 'on' && !in_array('2 Week Intensive',$pro)){
            $program[] = '2 Week Intensive';
        }
        if(isset($steady) && $steady == 'on' && !in_array('10+ Week Steady',$pro)){
            $program[]= '10+ Week Steady';
        }
        if(isset($maintennace) && $maintennace == 'on' && !in_array('4 Week Maintenance',$pro)){
            $program[] = '4 Week Maintenance';
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $program = implode(', ',$program);
        $program = trim($program);
        $customerId = $this->customerSession->getCustomer()->getId();
        try{
            if(!$checkSubscriber->isSubscribed()){
                $subscriber = $this->subscriberFactory->create();
                $subscriber->setName($name)
                            ->setEmail($email)
                            ->setProgram($program);
                $status = $subscriber->subscribe($email, $name, $program);
                $this->messageManager->addSuccess(__('We saved the subscription.'));
                return $this->_redirect($this->_redirect->getRefererUrl());
            }elseif($customerId != ''){
                $subscriber = $this->subscriberFactory->create();
                $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                $subscriber->updateSubscription($customerId)->setProgram($program)->save();
                return $this->_redirect($this->_redirect->getRefererUrl());
            }else{
                $this->messageManager->addError(__('You are already subscribed.'));
                return $this->_redirect($this->_redirect->getRefererUrl());
            }
        }catch (\Exception $e){
            $this->messageManager->addException($e, __('Some thing wrong.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }
    }

}
