<?php
include_once(_PS_MODULE_DIR_.'fieldtestimonials/classes/FieldTestimonial.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/defined.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/fieldtestimonials.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/classes/FieldFileUploader.php');
include_once(_PS_MODULE_DIR_.'fieldtestimonials/libs/Params.php');
class AdminTestimonialController extends ModuleAdminController {
	public $bootstrap = true ;
	protected $position_identifier = 'id_fieldtestimonials';
	public function __construct(){
		$this->table = 'fieldtestimonials';
		$this->className = 'FieldTestimonial';
		$this->name ='testimonials';
		$this->lang = false;
		$this->deleted = false;
		parent::__construct();
		$this->context = Context::getContext();
		$this->_defaultOrderBy = 'position';
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->bulk_actions = array(
			'delete' => array(
			'text' => $this->l('Delete selected'),
			'confirm' => $this->l('Delete selected items?'),
			'icon' => 'icon-trash'
		)
		);
		$this->fields_list = array(
			'id_fieldtestimonials'=> array('title' => $this->l('ID'), 'width' => 20),
			'name_post' => array('title' => $this->l('Name'), 'width' => 'auto',),
			'email' => array('title' => $this->l('Email'), 'width' => 'auto',),
			'company' => array('title' => $this->l('Company'), 'width' => 'auto',),
			'address' => array('title' => $this->l('Address'), 'width' => 'auto',),
			'media_type'=> array('title' => $this->l('Media type'), 'width' => 'auto',),
			'position'=> array('title' => $this->l('Position'),'filter_key' => 'a!position','align' => 'center','position' => 'position' ),
			'date_add'=> array('title' => $this->l('Date add'), 'width' => 'auto','class'=> 'fixed-width-xs'),
			'active'=> array('title' => $this->l('Active'),'align' => 'center','active' => 'status','type' => 'bool','orderby' => false,),
		);
			parent::__construct();
			$this->_defaultFormLanguage = (int)Configuration::get('PS_LANG_DEFAULT');
	}
	 public function renderView(){
		$this->initToolbar();
		return $this->renderList();
	}

	public function initToolbar(){
		if (empty($this->display)){
			$this->toolbar_btn['new'] = array(
				'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
				'desc' => $this->l('Add New')
			);
		}
	}
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)){
            $this->page_header_toolbar_btn = array(
                'options' => array(
                    'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=fieldtestimonials',
                    'desc' => $this->l('Options'),
                    'icon' => 'process-icon-cogs'
                )
            );
        }

        parent::initPageHeaderToolbar();
    }

	public function postProcess(){
		$obj = $this->loadObject(true);
		$_POST['media']=$obj->media;
		if (Tools::isSubmit('forcedeleteImage')|| Tools::getValue('deleteImage'))
		{
                    $this->processForceDeleteImage();
                    if (Tools::isSubmit('forcedeleteImage'))
			Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminTestimonial'));
		}
		if(Tools::getValue('media_link')){
			$link =explode('/',Tools::getValue('media_link'));
			$link_id = new FieldTestimonial();
			if(isset($link[2]) && ($link[2] =='www.youtube.com' || $link[2]=='vimeo.com')){
			$_POST['media_type'] =$link_id->getTypevideo(Tools::getValue('media_link'));
			$_POST['media_link_id'] = $link_id->getIdFromLinkInput(Tools::getValue('media_link'));
			}
			else {
				$this->errors[] ='Media link require link youtube or vimeo';
			}
		}
		if(isset ($_FILES['media']['name']) && ($_FILES['media']['name'])!= null ){
			$img_old = _PS_MODULE_DIR_ . 'fieldtestimonials/img/' . $obj->media;
			if (file_exists($img_old))
			unlink($img_old);
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
		if(isset($res)){
			$_POST['media'] = $res['name'];
			$_POST['media_type'] = $res['type'] ;
			}
		}
		$return = parent::postProcess();
		return $return;
	}

	public function processForceDeleteImage()
	{
		$obj = $this->loadObject(true);
		if (Validate::isLoadedObject($obj))
		$update = new FieldTestimonial($obj->id);
		$update->media = null;
		$update->media_type = null;
		$obj->deleteImage();
		$update->update();
	}
	/////position////////////////////////
	public function ajaxProcessUpdatePositions()
	{
		$way = (int)(Tools::getValue('way'));
		$id_fieldtestimonial = (int)(Tools::getValue('id'));
		$positions = Tools::getValue($this->table);
	foreach ($positions as $position => $value){
		$pos = explode('_', $value);
		if (isset($pos[2]) && (int)$pos[2] === $id_fieldtestimonial){
			if ($fieldtestimonial = new FieldTestimonial((int)$pos[2])){
				if (isset($position) && $fieldtestimonial->updatePosition($way, $position))
					echo 'ok position '.(int)$position.' for carrier '.(int)$pos[1].'\r\n';
				else
					echo '{"hasError" : true, "errors" : "Can not update carrier '.(int)$id_fieldtestimonial.' to position '.(int)$position.' "}';
			}else
					echo '{"hasError" : true, "errors" : "This carrier ('.(int)$id_fieldtestimonial.') can t be loaded"}';
					break;
			}
		}
	}

	public function processPosition(){
	if ($this->tabAccess['edit'] !== '1')
		$this->errors[] = Tools::displayError('You do not have permission to edit this.');
	else if (!Validate::isLoadedObject($object = new Fieldtestimonial((int)Tools::getValue($this->identifier, Tools::getValue('id_fieldtestimonials', 1)))))
		$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').' <b>'.
		$this->table.'</b> '.Tools::displayError('(cannot load object)');
	if (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
		$this->errors[] = Tools::displayError('Failed to update the position.');
	else{
		Tools::redirectAdmin(self::$currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.'&token='.Tools::getAdminTokenLite('AdminTestimonial'));
	}
	}
	////////////////////////////
	public function renderForm($isMainTab = true) {
		global $currentIndex, $cookie;
		$obj = $this->loadObject(true);
		$this->context->controller->addJS(_MODULE_DIR_._FIELD_TESTIMONIAL_ADMIN_URL_.'admin_testimonial.js');
		if (!$this->loadObject(true))
			return;
		if (Validate::isLoadedObject($this->object))
			$this->display = 'edit';
		else
			$this->display = 'add';
			$img_types = explode('|', $this->module->getParams()->get('type_image'));
			$video_types =explode('|', $this->module->getParams()->get('type_video'));
			$media_desc = '';
		if (in_array($obj->media_type,$img_types)){
			$media_desc = '<br/>
			<img id="image_desc" style="clear:both;border:1px solid black;width:100px;" alt="" src="'.__PS_BASE_URI__.'modules/fieldtestimonials/img/'.$obj->media.'" />
			<br/>';
		} elseif (in_array($obj->media_type,$video_types)){
			$media_desc='<video width="320" height="240" controls>
			<source src="'.__PS_BASE_URI__.'modules/fieldtestimonials/img/'.$obj->media.'" type="video/mp4" />
			</video>' ;
		}
		$this->context->smarty->assign('media_desc',$media_desc);
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Submit and Manage Testimonial'),
				'image' => '../img/admin/quick.gif'
			),
			'input' => array(
				array(
				'type' => 'text',
				'label'=> $this->l('Name:'),
				'name' => 'name_post',
				'lang' => false,
				'required' => true,
				'hint' => $this->l('Invalid characters:').' <>;=#{}',
				'desc' => $this->l('This field is one person name. example: Peter, Marry...'),
				'size' => 40
			),
		array(
			'type' => 'text',
			'label'=> $this->l('Email:'),
			'name' => 'email',
			'lang' => false,
			'required' => true,
			'class'=> '',
			'hint' => $this->l('Invalid characters:').' <>;=#{}',
			'desc' => $this->l('This field is an email. ex: peter123@gmail.com.'),
			'size' => 20
		),
		array(
			'type' => 'text',
			'label'=> $this->l('Company:'),
			'name' => 'company',
			'lang' => false,
			'required' => false,
			'class'=> '',
			'hint' => $this->l('Invalid characters:').' <>;=#{}',
			'desc' => $this->l('Your company name enter here.'),
			'size' => 40
		),
		array(
			'type' => 'text',
			'label'=> $this->l('Address:'),
			'name' => 'address',
			'lang' => false,
			'required' => true,
			'class'=> '',
			'hint' => $this->l('Invalid characters:').' <>;=#{}',
			'desc' => $this->l('Maybe your Home address or Company address.'),
			'size' => 40
		),
		array(
			'type' => 'text',
			'label'=> $this->l('Media link:'),
			'name' => 'media_link',
			'lang' => false,
			'required' => false,
			'class'=> '',
			'hint' => $this->l('Invalid characters:').' <>;=#{}',
			'desc' => $this->l('Media link should be one Youtube link.'),
			'size' => 40
		),
		array(
			'type' => 'text',
			'label'=> $this->l('Button link:'),
			'name' => 'button_link',
			'lang' => false,
			'required' => false,
			'size' => 40
		),
		array(
			'type' => 'file',
			'label'=> $this->l('Media posted:'),
			'id' => 'media',
			'name' => 'media',
			'display_image' => true,
			'delete_url' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&deleteImage=1',
			'image' => $media_desc ? $media_desc: false,
			'size' => 100
		),
		array(
			'type' => 'textarea',
			'label'=> $this->l("Content:"),
			'name' => 'content',
			'id' => 'content_area',
			'required' => true,
			'lang' => true,
			'hint' => $this->l('Invalid characters:').' <>;=#{}',
			'desc' => $this->l('Write and edit your contents here!'),
			'rows' => 10,
			'cols' => 40,
			'autoload_rte' => true
		),
		array(
			'type' => 'switch',
			'label' => $this->l('Active:'),
			'name' => 'active',
			'required' => false,
			'class' => 't',
			'is_bool' => true,
			'values' => array(
			array(
			'id' => 'active_on',
			'value' => 1,
			'label' => $this->l('Enabled')
			),
			array(
			'id' => 'active_off',
			'value' => 0,
			'label' => $this->l('Disabled')
			)
		),
			'desc' => $this->l('Display testimonial on Front-end or not.')
		)
		),
			'submit' => array(
			'title' => $this->l('Save'),
				)
				);
		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
			'type' => 'shop',
			'label' => $this->l('Shop Testimonial:'),
			'name' => 'checkBoxShopTestimonial',
			);
		}
			$this->tpl_form_vars = array(
			'active' => $this->object->active,
			'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL')
		);
			return parent::renderForm();
	}
}
