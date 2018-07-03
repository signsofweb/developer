<?php
@session_start();
include_once(_PS_MODULE_DIR_.'fieldtestimonials/defined.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/fieldtestimonials.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/classes/FieldTestimonial.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/classes/FieldFileUploader.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/libs/Params.php');
class FieldtestimonialsViewsModuleFrontController extends ModuleFrontController {
	public $errors = array();
	public $success;
	public $identifier;
	public function __construct(){
		parent::__construct();
		$this->context = Context::getContext();
		$this->name ='fieldtestimonials';
		$this->identifier = 'id_fieldtestimonials';
		smartyRegisterFunction($this->context->smarty, 'function', 'testimonialpaginationlink', array('FieldtestimonialsViewsModuleFrontController', 'getTestimonialPaginationLink'));
		}

	public function initContent() {
		$this->display_column_left = true;
		$this->display_column_right = true;
		parent::initContent();
		if(Tools::getValue('process') == 'view' || Tools::getValue('process') == 'read_more'){
		$this->listAllTestimoninals();
		}
		elseif(Tools::getValue('process') == 'form_submit'){
		$this->formTestimoninals();
		}
	}

	public static function getTestimonialPaginationLink($params, &$smarty){
		$id = Tools::getValue('id');
		if (!isset($params['p']))
			$p = 1;
		else
			$p = $params['p'];
		if (!isset($params['n']))
			$n = 10;
		else
			$n = $params['n'];
		return Context::getContext()->link->getModuleLink(
			'fieldtestimonials',
			'views',
		array(
			'process' => 'view',
			'id' =>$id,
			'p' => $p,
			'n' => $n,
			)
			);
	}
	public function listAllTestimoninals(){
		$this->addCSS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'css/style.css');
		$this->name = 'testimonials';
		$this->_configs ='';
		$this->addJqueryPlugin('fancybox');
		$image_type = explode('|', $this->module->getParams()->get('type_image'));
		$video_type = explode('|', $this->module->getParams()->get('type_video'));
		$p = Tools::getValue('p',1);
		$n = Tools::getValue('n', $this->module->getParams()->get('test_limit'));
		$id = Tools::getValue('id', 0);
		if ($id == 0){
			$alltestimoninals = FieldTestimonial::getAllTestimonials();
			$testimoninals = FieldTestimonial::getAllTestimonials($p, $n);
			$max_page = floor(sizeof($alltestimoninals) / ((int)(Tools::getValue('n') > 0) ? (int)(Tools::getValue('n')) : 10));
			$this->context->smarty->assign(array('alltestimoninals'=> $alltestimoninals));
		}
		else {
			$testimoninals = FieldTestimonial::getAllTestimonials(1,false,$id,false);//view all and curent testimonial
			$other_testimoninals = FieldTestimonial::getAllTestimonials(1,false,false,$id); // view all other testimonial
			$page_other_testimonials = FieldTestimonial::getAllTestimonials($p,$n,false,$id); // item on page
			$this->context->smarty->assign(array('other_testimoninals'=> $other_testimoninals));
			$max_page = floor(sizeof($other_testimoninals) / ((int)(Tools::getValue('n') > 0) ? (int)(Tools::getValue('n')) : 10));
			$this->context->smarty->assign(array('other_testimoninals'=> $other_testimoninals,'page_other_testimonials'=>$page_other_testimonials));
		 }
		$this->context->smarty->assign(array(
			'page' => ((int)(Tools::getValue('p')) > 0 ? (int)(Tools::getValue('p')) : 1),
			'nbpagination' => ((int)(Tools::getValue('n') > 0) ? (int)(Tools::getValue('n')) : $n),
			'nArray'=> array(10,20,50),
			'max_page'=> $max_page,
			'testimoninals' => $testimoninals,
			'id' => $id,
			'image_type'=> $image_type,
			'video_type'=> $video_type,
			'name'=> $this->name,
		));
		$this->setTemplate('module:fieldtestimonials/views/templates/front/all_testimonials.tpl');		
	}

	public function formTestimoninals(){
		$this->addCSS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'css/style.css');
		$this->addJS(_PS_JS_DIR_.'validate.js');
		$this->addJS(_MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'js/validate_fields.js');
		$tm_captcha = (int)$this->module->getParams()->get('captcha');
		$captcha_code = _MODULE_DIR_.'fieldtestimonials/captcha.php';
		$loader_image = $video_vimeo = _MODULE_DIR_._FIELD_TESTIMONIAL_FRONT_URL_.'img/loading.gif';
		$this->context->smarty->assign(array(
			'captcha' => $tm_captcha,
			'captcha_code' => $captcha_code,
			'name_post' => html_entity_decode(Tools::getValue('name_post')),
			'company'=> html_entity_decode(Tools::getValue('company')),
			'address'=> html_entity_decode(Tools::getValue('address')),
			'media_link'=> html_entity_decode(Tools::getValue('media_link')),
			'content'=> html_entity_decode(Tools::getValue('content')),
			'email' => html_entity_decode(Tools::getValue('email')),
			'errors' => $this->errors,
			'success' => $this->success,
			'loader_image' => $loader_image,
		));
		$this->setTemplate('module:fieldtestimonials/views/templates/front/form_submit.tpl');		
	}

	public function postProcess(){
		if(Tools::isSubmit('submitNewTestimonial')){
			$this->postValidation();
		if(($_FILES['media']['name']) != null){
			$upload = new FieldFileUploader($this->module, $_FILES['media']);
			$res = $upload->handleUpload();
		if(!empty($upload->errors)){
		if(is_array($upload->errors))
			$this->errors = array_merge($this->errors, $upload->errors);
		else
			$this->errors[] = $upload->errors;
		}
		}
		if(!count($this->errors)){
			$obj = new FieldTestimonial();
			$obj->name_post = Tools::getValue('name_post');
			$obj->email = Tools::getValue('email');
			$obj->company = Tools::getValue('company');
			$obj->address = Tools::getValue('address');
			$obj->media_link= Tools::getValue('media_link');
			$media = new FieldTestimonial();
			$obj->media_link_id = $media->getIdFromLinkInput(Tools::getValue('media_link'));
			if(isset($res)&&$res != null){
				$obj->media = $res['name'];
				$obj->media_type= $res['type'];
			}else{
			if($obj->media_link != null){
				$obj->media = '';
				$obj->media_type= $media->getTypevideo(Tools::getValue('media_link'));
			}else{
				$obj->media = '';
				$obj->media_type= '';
				}
			}
				$obj->content = Tools::getValue('content');
			if((int)$this->module->getParams()->get('auto_post') ==1){
				$obj->active = 1;
			}else {
				$obj->active = 0;
			}
			$save_value = $obj->add();
			if(!$save_value)
				$this->errors[] = Tools::displayError('Your testimonial could not be insert. Please, check all again!');
			else
				$this->success = Tools::displayError('Send successfully.');
		}
		}
	}
	public function postValidation(){
		$this->validateRules('FieldTestimonial');
		if(Tools::isSubmit('submitNewTestimonial')){
			if(Tools::getValue('media_link')){
				$link =explode('/',Tools::getValue('media_link'));
			if($link[2] == 'www.youtube.com' || $link[2] == 'vimeo.com'){
			return true ;
			}
			else{
				$this->errors[] = Tools::displayError('Media link require link youtube or vimeo');
				return false;
			}
			}
			$captcha = $_SESSION['fieldtestimonials_captcha'];
			if((int)$this->module->getParams()->get('captcha')){
				if( !strtolower(Tools::getValue('captcha')) || strtolower(Tools::getValue('captcha')) != strtolower($captcha))
					$this->errors[] = Tools::displayError('Captcha is incorrect');
			}
		}
	}
	public function validateRules($class_name = false){
		if(!$class_name)
			$class_name = $this->className;
		if (!empty($class_name))
			$rules = call_user_func(array($class_name, 'getValidationRules'), $class_name);
		if (isset($rules) && count($rules) && (count($rules['requiredLang']) || count($rules['sizeLang']) || count($rules['validateLang']))){
			$default_language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
			$languages = Language::getLanguages(false);
		}
		/* Checking for required fields */
		if (isset($rules['required']) && is_array($rules['required']))
			foreach ($rules['required'] as $field){
				$field_name = call_user_func(array($class_name, 'displayFieldName'), $field, $class_name);
				if (($value = Tools::getValue($field)) == false && (string)$value != '0')
					if (!Tools::getValue($this->identifier) || ($field != 'passwd' && $field != 'no-picture'))
						switch ($field_name)
						{
							case 'name_post':
								$this->errors[] = Tools::displayError('The name_post field is required.');
								break;
							case 'email':
								$this->errors[] = Tools::displayError('The email field is required.');
								break;
							case 'address':
								$this->errors[] = Tools::displayError('The address field is required.');
								break;
							case 'content':
								$this->errors[] = Tools::displayError('The content field is required.');
								break;
							default:
								$this->errors[] = sprintf(
								Tools::displayError('The %s field is required.'),
								call_user_func(array($class_name, 'displayFieldName'), $field, $class_name));
								break;
						}
			}
		/* Checking for maximum fields sizes */
		if (isset($rules['size']) && is_array($rules['size']))
			foreach ($rules['size'] as $field => $max_length)
		if (Tools::getValue($field) !== false && Tools::strlen(Tools::getValue($field)) > $max_length)
			$this->errors[] = sprintf(
			Tools::displayError('The %1$s field is too long (%2$d chars max).'),
			call_user_func(array($class_name, 'displayFieldName'), $field, $class_name),
			$max_length
		);
		/* Checking for maximum multilingual fields size */
		if (isset($rules['sizeLang']) && is_array($rules['sizeLang']))
			foreach ($rules['sizeLang'] as $field_lang => $max_length)
			foreach ($languages as $language)
		{
			$field_lang_value = Tools::getValue($field_lang.'_'.$language['id_lang']);
		if ($field_lang_value !== false && Tools::strlen($field_lang_value) > $max_length)
			$this->errors[] = sprintf(
			Tools::displayError('The field %1$s (%2$s) is too long (%3$d chars max, html chars including).'),
			call_user_func(array($class_name, 'displayFieldName'), $field_lang, $class_name),
			$language['name'],
			$max_length
		);
		}
		/* Checking for fields validity */
		if (isset($rules['validate']) && is_array($rules['validate']))
			foreach ($rules['validate'] as $field => $function)
		if (($value = Tools::getValue($field)) !== false && ($field != 'passwd'))
		if (!Validate::$function($value) && !empty($value))
			$this->errors[] = sprintf(
			Tools::displayError('The %s field is invalid.'),
			call_user_func(array($class_name, 'displayFieldName'), $field, $class_name)
			);
		/* Checking for multilingual fields validity */
	}
}
