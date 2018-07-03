<?php
if(!defined('_PS_VERSION_'))
exit;
include_once(_PS_MODULE_DIR_.'fieldtestimonials/defined.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/libs/Params.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/classes/FieldTestimonial.php');
class Fieldtestimonials extends Module {
	public $bootstrap = true ;
	private $_html = '';
	private $_configs = '';
	protected $params = null;
	const INSTALL_SQL_FILE = 'install.sql';
	const UNINSTALL_SQL_FILE = 'uninstall.sql';
	public function __construct(){
		$this->name 	= 'fieldtestimonials';
		$this->tab	= 'FieldThemes';
		$this->version= '1.0';
		$this->author 	= 'FieldThemes';
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->secure_key = Tools::encrypt($this->name);
		parent::__construct();
		$this->displayName = $this->l('Field Testimonials Module');
		$this->description = $this->l('Field Testimonials Module.');
		$this->_params =new FieldParams( $this->name, $this);
	}
	public function initConfigs(){
		return array(
			'test_limit' => 10,
			'type_image' => 'png|jpg|gif',
			'type_video' => 'flv|mp4|avi',
			'size_limit' => 6,
			'captcha' => 1,
			'auto_post' => 0,
			'show_submit' => 0,
			'show_info' => 1,
			'show_button_link' => 0,
            'show_background' => 1
		);
	}

	public function getContent(){
		$this->_html .= '<h2>'.$this->displayName.' and Custom Fields.</h2>';
                $css = "<style>#background-images-thumbnails img {max-width:400px; height:auto;}</style>";
		if (Tools::isSubmit('submitUpdate')){
			if ($this->_postValidation()){
				$configs = $this->initConfigs();
				$res = $this->_params->batchUpdate( $configs );
				if (!$res){
					$this->_html .= $this->displayError($this->l('Configuration could not be updated'));
				}
				else{
					$this->_html .= $this->displayConfirmation($this->l('Configuration updated'));
				}
			}
                        $id_shop = (int)$this->context->shop->id;

			if (isset($_FILES['background']) && isset($_FILES['background']['tmp_name']) && !empty($_FILES['background']['tmp_name'])) {
				$img = dirname(__FILE__).'/img/background_'.$id_shop.'.jpg';
				if (file_exists($img))
					unlink($img);
				
				if ($error = ImageManager::validateUpload($_FILES['background'], Tools::convertBytes(ini_get('upload_max_filesize'))))
					$this->_html .= $error;

				elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['background']['tmp_name'], $tmp_name))
					return false;			

				elseif (!ImageManager::resize($tmp_name, $img))
					$this->_html .= $this->displayError($this->l('An error occurred while attempting to upload the image.'));

				if (isset($tmp_name))
					unlink($tmp_name);

			}
			$this->_clearCache($this->name.'.tpl');
		}
		return $this->_html.$css.$this->initForm();
	}
	protected function initForm()
	{       $rev = date("H").date("i").date("s")."\n";
		$background = "";
		if (file_exists(dirname(__FILE__).'/img/background_'.$this->context->shop->id.'.jpg'))
			$background = '<br/>
                                            <img id="image_desc" style="clear:both;border:1px solid black;" alt="" src="'.$this->_path."/img/background_".$this->context->shop->id.".jpg?".'" />
                                        <br/>';
                $image = dirname(__FILE__)."/img/background_".$this->context->shop->id.'.jpg';
		$image_size = file_exists($image) ? filesize($image) / 1000 : false;
		$configs = $this->initConfigs();
		$params = $this->_params;
		$this->fields_form[0]['form'] = array(
			'legend' => array(
			'title' => $this->l('Global Setting'),
			'icon' => 'icon-cogs'
		),
		'input' => array(
			$params->inputTags('test_limit','Testimonial Limit:','','The number items on a page.'),
			$params->inputTags('type_image','Image type:','','allow upload image type.'),
			$params->inputTags('type_video','Video type:','','allow upload video type.'),
			$params->inputTags('size_limit','Size limit upload:','','Mb .Max size file upload.'),
			$params->switchTags('show_info','Display infomation clients:'),
			$params->switchTags('show_button_link','Display buttons with link redirecting:'),
			$params->switchTags('show_background','Show background image:'),
			$params->fileTags('background','background image:','',$background,$image_size),
		),
		'submit' => array(
			'title' => $this->l('Save'),
		)
		);
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =$this->table;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitUpdate';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $params->getConfigFieldsValues($configs),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper->generateForm($this->fields_form);
	}

	public function _postValidation(){
		$errors = array();
		if(Tools::isSubmit('submitUpdate')){
			if(!Tools::getValue(('test_limit')) || !Validate::isInt(Tools::getValue('test_limit')))
				$errors[] = $this->l('False! Check again with testimonial limit.');
			if(!Tools::getValue('size_limit') || !Validate::isInt(Tools::getValue('size_limit')))
				$errors[] = $this->l('False! Check again with size upload limit.');
		}
		if (count($errors)) {
			$this->_html .= $this->displayError(implode('<br />',$errors));
			return false;
		}
		return true;
	}
	public function getParams(){
		return $this->_params;
	}
	public function install(){
		 if (parent::install() && $this->registerHook('displayHeader') && $this->registerHook('testimonials')) {
			$res = $this->installTable();
			$res &=$this->_createTab();
			$configs = $this->initConfigs();
			$this->_params->batchUpdate( $configs );
			return $res;
		}
		return false;
	}
	public function uninstall(){
		if (parent::uninstall()){
			$res = $this->uninstallTable();
			$res &= $this->_deleteTab();                        
			return $res;
		}
		return false;
	}
	
private function _createTab()
    {
        $response = true;

        // First check for parent tab
        $parentTabID = Tab::getIdFromClassName('AdminFieldMenu');

        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        }
        else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminFieldMenu";
            foreach (Language::getLanguages() as $lang) {
                $parentTab->name[$lang['id_lang']] = "FIELDTHEMES";
            }
            $parentTab->id_parent = 0;
            $parentTab->module = '';
            $response &= $parentTab->add();
        }

			// Check for parent tab2
			$parentTab_2ID = Tab::getIdFromClassName('AdminFieldMenuSecond');
			if ($parentTab_2ID) {
				$parentTab_2 = new Tab($parentTab_2ID);
			}
			else {
				$parentTab_2 = new Tab();
				$parentTab_2->active = 1;
				$parentTab_2->name = array();
				$parentTab_2->class_name = "AdminFieldMenuSecond";
				foreach (Language::getLanguages() as $lang) {
					$parentTab_2->name[$lang['id_lang']] = "FieldThemes Configure";
				}
				$parentTab_2->id_parent = $parentTab->id;
				$parentTab_2->module = '';
				$response &= $parentTab_2->add();
			}
			// Created tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminTestimonial";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "Manage Testimonial";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();
        return $response;
    }

private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminTestimonial');
		if($id_tab){
			$tab = new Tab($id_tab);
			$tab->delete();
		}
		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminFieldMenuSecond');
		if($parentTab_2ID){
			$tabCount_2 = Tab::getNbTabs($parentTab_2ID);
			if ($tabCount_2 == 0) {
				$parentTab_2 = new Tab($parentTab_2ID);
				$parentTab_2->delete();
			}
		}
        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTabID = Tab::getIdFromClassName('AdminFieldMenu');
		if($parentTabID){
			$tabCount = Tab::getNbTabs($parentTabID);
			if ($tabCount == 0){
				$parentTab = new Tab($parentTabID);
				$parentTab->delete();
			}
		}
        return true;
    }

	public function installTable() {
		if (!file_exists(dirname(__FILE__) . '/install/' . self::INSTALL_SQL_FILE))
			return (false);
		else if (!$sql = file_get_contents(dirname(__FILE__) . '/install/' . self::INSTALL_SQL_FILE))
			return (false);
		$sql = str_replace('ps_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql as $query) {
			if (!empty($query)) {
				if (!Db::getInstance()->Execute(trim($query)))
				return (false);
			}
		}
		return true;
	}

	public function uninstallTable() {
		if (!file_exists(dirname(__FILE__) . '/install/' . self::UNINSTALL_SQL_FILE))
			return (false);
		else if (!$sql = file_get_contents(dirname(__FILE__) . '/install/' . self::UNINSTALL_SQL_FILE))
			return (false);
			$sql = str_replace('ps_', _DB_PREFIX_, $sql);
			$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql as $query) {
			if (!empty($query)) {
				if (!Db::getInstance()->Execute(trim($query)))
				return (false);
			}
		}
		return true;
	}

	public function hookDisplayHeader($params){
		$this->context->controller->addCSS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'css/styleRightColumn.css');
		$this->context->controller->addJS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'js/jquery.cycle.all.js');
		$this->context->controller->addJS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'js/fancybox_media.js');
	}
	public function hookTestimonials($params){
		$testLimit = $this->getParams()->get('testLimit');
		$get_testimonials = FieldTestimonial::getAllTestimonials($testLimit);
        $id_default_lang = $this->context->language->id;
		foreach($get_testimonials as $key=>$get_testimonial){
			$get_testimonial_content = new FieldTestimonial($get_testimonial['id_fieldtestimonials'], $id_default_lang);
			$get_testimonials[$key]['content']=$get_testimonial_content->content;
		}
		$img_types = explode('|', $this->getParams()->get('type_image'));
		$video_types = explode('|', $this->getParams()->get('type_video'));
		$iframeMedia = _MODULE_DIR_.$this->name.'/process_iframe.php';
		$mediaUrl = _MODULE_DIR_.$this->name.'/img/';
		$video_post = _MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'img/video.jpg';
		$video_youtube = _MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'img/youtube.jpg';
		$video_vimeo = _MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'img/vimeo.jpg';
                $background = "";
                $show_background = $this->getParams()->get('show_background');
		$show_submit = $this->getParams()->get('show_submit');
		$show_info = $this->getParams()->get('show_info');
		$show_button_link = $this->getParams()->get('show_button_link');
                $rev = date("H").date("i").date("s")."\n";
                if (file_exists(dirname(__FILE__).'/img/background_'.$this->context->shop->id.'.jpg'))
                        $background = "img/background_".$this->context->shop->id.".jpg?".$rev;
		$conf_testimonials = array(
		'arr_img_type' => $img_types,
		'video_types' => $video_types,
		'mediaUrl' => $mediaUrl,
		'iframe' => $iframeMedia,
		'video_post' => $video_post,
		'video_youtube'=> $video_youtube,
		'video_vimeo'=> $video_vimeo,
		'iframeMedia'=> $iframeMedia,
		'show_submit'=> $show_submit,
		'show_info'=> $show_info,
		'show_button_link'=> $show_button_link,
        'show_background'=> $show_background
		);
		$this->context->smarty->assign(array(
		'testimonials' => $get_testimonials,
        'background'=> $background,
		'conf_testimonials' => $conf_testimonials
		));
		return $this->display(__FILE__, _FIELD_TESTIMONIAL_VIEW_FRONT_.'testimonials_random.tpl');
	}
	public function hookLeftColumn($params){
		return $this->hookTestimonials($params);
	}
}
