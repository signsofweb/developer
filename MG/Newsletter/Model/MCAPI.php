<?php
/**
 * Ebizmarts_MAgeMonkey Magento JS component
 *
 * @category    Ebizmarts
 * @package     Ebizmarts_MageMonkey
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Convert\Newsletter\Model;

class MCAPI extends \Ebizmarts\MageMonkey\Model\MCAPI
{
    protected $_version     = "3.0";
    protected $_timeout     = 300;
    protected $_chunkSize   = 8192;
    protected $_apiKey      = null;
    protected $_secure      = false;
	
	const CONFIG_PATH  = 'newsletter/subscription_config/';
	const CONFIG_PATH_PROGRAM = 'newsletter/program_config/';
    /**
     * @var \Ebizmarts\MageMonkey\Helper\Data|null
     */
    protected $_helper      = null;
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl|null
     */
    protected $_curl = null;

	protected $request ;
	
	protected $scopeConfig;
	
	protected $jsonHelper;
	
    protected $_customer;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
	protected $_subscriber;
	
    /**
     * MCAPI constructor.
     * @param \Ebizmarts\MageMonkey\Helper\Data $helper
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
		 \Magento\Newsletter\Model\Subscriber $subscriber,
		\Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\RequestInterface $request,
        \Ebizmarts\MageMonkey\Helper\Data $helper,
        \Magento\Framework\HTTP\Adapter\Curl $curl
    ) {
		$this->_customer        = $customer;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->_curl = $curl;
        $this->_apiKey = $helper->getApiKey();
        $this->_secure = false;
		$this->request = $request;
		$this->scopeConfig = $scopeConfig;
		$this->jsonHelper = $jsonHelper;
		 $this->_subscriber= $subscriber;
    }
    
   
   
    public function listCreateMember($listId, $memberData)
    {
    	$_loadData = $this->jsonHelper->jsonDecode($memberData);
    	$_loadData2 = $this->jsonHelper->jsonDecode($memberData);
		$_program_form = $this->request->getParam('program_form');
		$_upgrade_form = $this->request->getParam('upgrade_newletter');
		$_customer_program = $this->request->getParam('customer_program');

		if(isset($_upgrade_form)){
			$general_health = $this->request->getParam('general_health');
	        $weight_management = $this->request->getParam('weight_management');
	        $superfoods = $this->request->getParam('superfoods');
	        $kids_health = $this->request->getParam('kids_health');
	        $e50_health = $this->request->getParam('e50_health');
	        $email = $this->request->getParam('custom_name');
			$interests = array();
			if(isset($general_health) && $general_health == 'on'){
	           	$interests[$this->getConfig(self::CONFIG_PATH.'general_health')] = true;
	        }else{
	        	$interests[$this->getConfig(self::CONFIG_PATH.'general_health')] = false;
	        }
	        if(isset($weight_management) && $weight_management == 'on'){
	           	$interests[$this->getConfig(self::CONFIG_PATH.'weight_management')] = true;
	        }else{
	        	$interests[$this->getConfig(self::CONFIG_PATH.'weight_management')] = false;
	        }
	        if(isset($superfoods) && $superfoods == 'on'){
	           	$interests[$this->getConfig(self::CONFIG_PATH.'superfoods')] = true;
	        }else{
	        	$interests[$this->getConfig(self::CONFIG_PATH.'superfoods')] = false;
	        }
	        if(isset($kids_health) && $kids_health == 'on'){
	           	$interests[$this->getConfig(self::CONFIG_PATH.'kids_health')] = true;
	        }else{
	        	$interests[$this->getConfig(self::CONFIG_PATH.'kids_health')] = false;
	        }
	        if(isset($e50_health) && $e50_health == 'on'){
	           	$interests[$this->getConfig(self::CONFIG_PATH.'e50_health')] = true;
	        }else{
	        	$interests[$this->getConfig(self::CONFIG_PATH.'e50_health')] = false;
	        }
	       
	        $_loadData['interests'] = $interests;
		}elseif(!isset($_program_form) && !isset($_upgrade_form) && !isset($_customer_program)){
			$interests = array();
			$program = array();
			parse_str($this->request->getParam('serializedForm'), $data) ;
			$groups = array('general_health','weight_management','superfoods','kids_health','e50_health');
			foreach($groups as $group){
				if($data && isset($data['interests'][$group])){
					$interests[$this->getConfig(self::CONFIG_PATH.$group)] = true;
				}else{
					$interests[$this->getConfig(self::CONFIG_PATH.$group)] = false;
				}
			}
			$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'steady')] = true;
			$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'intensive')] = true;
			$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'maintennace')] = true;
			$email = $data['email'];
			$_loadData['interests'] = $interests;
			$_loadData2['interests'] = $program;
			if(isset($data['name'])){
				$_loadData['merge_fields']['FNAME']    = $data['name'];
			}
			if(isset($data['gender'])){
				$_loadData['merge_fields']['MMERGE5']   = $data['gender'];
			}
			if(isset($data['dob'])){
				$_loadData['merge_fields']['MMERGE3'] = (int)date('Y',strtotime($data['dob']));
			}
			if(isset($data['post_code'])){
				$_loadData['merge_fields']['MERGE4'] = $data['post_code'];
			}
			
		}elseif(isset($_customer_program)) {
			$steady = $this->request->getParam('ten-week');
			$pro_2_week = $this->request->getParam('maintenance');
        	$pro_4_week = $this->request->getParam('intensive');
	        $email = $this->request->getParam('custom_name');
			$program = array();
			if(isset($steady) && $steady == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'steady')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'steady')] = false;
	        }
	        if(isset($pro_2_week) && $pro_2_week == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'intensive')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'intensive')] = false;
	        }
	        if(isset($pro_4_week) && $pro_4_week == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'maintennace')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'maintennace')] = false;
	        }
	        $_loadData['interests'] = $program;
		}else{
			$intensive = $this->request->getParam('intensive');
	        $steady = $this->request->getParam('steady');
	        $maintennace = $this->request->getParam('maintennace');
	        $email = $this->request->getParam('email');
        	$name = $this->request->getParam('name');
			
	        $program = array();
	        if(isset($intensive) && $intensive == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'intensive')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'intensive')] = false;
	        }
	        if(isset($steady) && $steady == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'steady')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'steady')] = false;
	        }
	        if(isset($maintennace) && $maintennace == 'on'){
	           	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'maintennace')] = true;
	        }else{
	        	$program[$this->getConfig(self::CONFIG_PATH_PROGRAM.'maintennace')] = false;
	        }
			$_loadData['interests'] = $program;
	       	if(isset($data['name'])){
				$_loadData['merge_fields']['FNAME']    = $data['name'];
			}
		}
	
		$_saveData = $this->jsonHelper->jsonEncode($_loadData);
		$_saveData2 = $this->jsonHelper->jsonEncode($_loadData2);
		$this->_helper->log('Create Member');
		 
		$response = array();
		if(isset($_program_form) || isset($_customer_program)){
			$_data = $this->_subscriber->loadByEmail($email)->getProgram();
			if($this->_isSubcribe($email) && $_data != ''){
				$response[] = $this->callServer('PATCH', 'lists', [0=>'e9fd180edd',1=>'members',2=>md5(strtolower($email))], $_saveData);
			}else{
				$response[] = $this->callServer('POST', 'lists', [0=>'e9fd180edd',1=>'members'], $_saveData);
			}
			
		}elseif(isset($_upgrade_form) && $_saveData != ''){

			if($this->_isSubcribe($email)){
				$response[] = $this->callServer('PATCH', 'lists', [0=>$listId,1=>'members',2=>md5(strtolower($email))], $_saveData);

			}else{
				$response[] = $this->callServer('POST', 'lists', [0=>$listId,1=>'members'], $_saveData);
			}
		}else{
			$_data = $this->_subscriber->loadByEmail($email)->getInterests();
			if($this->_isSubcribe($email) && $_data != ''){
				$response[] = $this->callServer('PATCH', 'lists', [0=>'e9fd180edd',1=>'members',2=>md5(strtolower($email))], $_saveData2);
				$response[] = $this->callServer('PATCH', 'lists', [0=>$listId,1=>'members',2=>md5(strtolower($email))], $_saveData);

			}else{
				$response[] = $this->callServer('POST', 'lists', [0=>$listId,1=>'members'], $_saveData);
				$response[] = $this->callServer('POST', 'lists', [0=>'e9fd180edd',1=>'members'], $_saveData2);
			}
		}
        
        $this->_helper->log($response);
        return $response;
    }
	
	public function getConfig($path) {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return $this->scopeConfig->getValue($path, $storeScope);
	}
	
	public function _isSubcribe($customerEmail){
		$checkSubscriber = $this->_subscriber->loadByEmail($customerEmail);
		if ($checkSubscriber->isSubscribed()) {
			return true;
		}
		return false;
	}
	
   
}
