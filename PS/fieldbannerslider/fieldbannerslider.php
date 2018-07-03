<?php

// Security
if (!defined('_PS_VERSION_'))
	exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');

// Loading Models
require_once(_PS_MODULE_DIR_ . 'fieldbannerslider/models/nivobannerslider.php');
class Fieldbannerslider extends Module {
    private $_html = '';
    private $_postErrors = array();
        
    public function __construct() {
        $this->name = 'fieldbannerslider';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'fieldthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');

        parent::__construct();

        $this->displayName = $this->l('Field Bannerslider');
        $this->description = $this->l('block config');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	$this->admin_tpl_path 	= _PS_MODULE_DIR_.$this->name.'/views/templates/admin/';
    }

   

    public function install()
	{
        
        // Install SQL
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
        
		// Install Tabs
		$this->_createTab();
		if(!Configuration::updateValue($this->name.'_enable_md', 1) OR
		!Configuration::updateValue($this->name.'_animation_type', 'random')OR
		!Configuration::updateValue($this->name.'_pause_time', 4800) OR
		!Configuration::updateValue($this->name.'_animation_speed', 1000)OR
		!Configuration::updateValue($this->name.'_qty_item', 8) OR
		!Configuration::updateValue($this->name.'_show_arrow', 0) OR
		!Configuration::updateValue($this->name.'_show_caption', 0) OR
		!Configuration::updateValue($this->name.'_show_navigation', 1) OR
		!Configuration::updateValue($this->name.'_start_slide', 0)) {return false;}
		// Set some defaults
		return parent::install() &&
		$this->registerHook('actionObjectBannersliderAddAfter') &&
		$this->registerHook('leftColumn')&&
		$this->registerHook('rightColumn')&&
		$this->_installHookCustomer()&&
		$this->registerHook('bannerslider')&&
		$this->registerHook('displayHeader');    
	}
        
        public function uninstall() {
            
		Configuration::deleteByName('FIELDBANNERSLIDER');

		// Uninstall Tabs
		$this->_deleteTab();
		Configuration::deleteByName($this->name.'_enable_md');
		Configuration::deleteByName($this->name.'_animation_type');
		Configuration::deleteByName($this->name.'_pause_time');
		Configuration::deleteByName($this->name.'_animation_speed');
		Configuration::deleteByName($this->name.'_qty_item');
                Configuration::deleteByName($this->name.'_show_thumbnail');
		Configuration::deleteByName($this->name.'_show_arrow');
		Configuration::deleteByName($this->name.'_show_caption');
                Configuration::deleteByName($this->name.'_show_navigation');
                Configuration::deleteByName($this->name.'_start_slide');
		//uninstall db
                include(dirname(__FILE__).'/sql/uninstall_sql.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		// Uninstall Module
		if (!parent::uninstall())
			return false;
		// !$this->unregisterHook('actionObjectExampleDataAddAfter')
		return true;
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
            $tab->class_name = "AdminFieldbannerslider";
            $tab->name = array();
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = "Manage Bannerslider";
            }
            $tab->id_parent = $parentTab_2->id;
            $tab->module = $this->name;
            $response &= $tab->add();

            return $response;
        }

        /* ------------------------------------------------------------- */
        /*  DELETE THE TAB MENU
        /* ------------------------------------------------------------- */
        private function _deleteTab()
        {
            $id_tab = Tab::getIdFromClassName('AdminFieldbannerslider');
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
	
	private function _postProcess()
	{  
		Configuration::updateValue($this->name.'_enable_md', Tools::getValue('enable_md'));
		Configuration::updateValue($this->name.'_animation_type', Tools::getValue('animation_type'));
		Configuration::updateValue($this->name.'_pause_time', Tools::getValue('pause_time'));
		Configuration::updateValue($this->name.'_animation_speed', Tools::getValue('animation_speed')); 
		Configuration::updateValue($this->name.'_qty_item', Tools::getValue('qty_item'));
		Configuration::updateValue($this->name.'_show_thumbnail', Tools::getValue('show_thumbnail'));
		Configuration::updateValue($this->name.'_show_arrow', Tools::getValue('show_arrow'));
		Configuration::updateValue($this->name.'_show_caption', Tools::getValue('show_caption'));
		Configuration::updateValue($this->name.'_show_navigation', Tools::getValue('show_navigation'));
		Configuration::updateValue($this->name.'_start_slide', Tools::getValue('start_slide'));
		
		
		$this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
	}
	
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->displayName.'</h2>';
		
		if (Tools::isSubmit('submit'))
		{			
			
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
			{
				foreach ($this->_postErrors AS $err)
				{
					$this->_html .= '<div class="alert error">'.$err.'</div>';
				}
			}
		}
		
		$this->_displayForm();
		
		return $this->_html;
	}
        
        
        public function  getAttrFromImage($image = NULL){
            $doc = new DOMDocument();
            $doc->loadHTML($image);
            $imageTags = $doc->getElementsByTagName('img');
            foreach ($imageTags as $tag) {
                if($tag->getAttribute('src')) {
                    return $tag->getAttribute('src'); 
                    break;
                }
            }
            return NULL;
        }
        
        public function getBannerslider() {
		
                        $id_shop = (int)Context::getContext()->shop->id;
						$id_lang = (int)$this->context->language->id;
                        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'fieldbannerslider` ps'; 
						$sql .= ' LEFT JOIN `'. _DB_PREFIX_ . 'fieldbannerslider_lang` psl';
						$sql .= ' ON ps.id_fieldbannerslider = psl.id_fieldbannerslider';
                        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'fieldbannerslider_shop`  pss ';
						$sql .= ' ON ps.id_fieldbannerslider = pss.id_fieldbannerslider';
						$sql .= ' where pss.`id_shop` ='.$id_shop ;
						$sql .= ' AND psl.`id_lang` ='.$id_lang ;
						$sql .=' AND ps.`active` =1';
						$sql .= ' ORDER BY `porder` ASC';
                        $slides = Db::getInstance()->ExecuteS($sql);
                        
                        if(is_array($slides)){
                            $limit = 0;
                            $arraySlides = array();
                            foreach($slides  as $key => $slideArray) {
                                if($limit == Configuration::get($this->name.'_qty_item')) break;
                                $limit ++;
                                 //echo "<pre>"; print_r($slideArray); 
                                $newSlide = array();
                                 foreach($slideArray as $k => $v) {
                                     if($k=='image'){
                                        $v = _PS_BASE_URL_.__PS_BASE_URI__.'modules/fieldbannerslider/images/'.$slideArray['images'];
                                        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                                            $v = str_replace("http://", "https://", $v);
					}
                                     }
                                     $newSlide[$k] = $v;
                                 }
                                 $arraySlides[$key] = $newSlide;
                            }

                        }
						//echo "<Pre>"; print_r($arraySlides);
                        return $arraySlides;
        }
        private function _displayForm()
	{ 
            $this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                  <fieldset>
                    <legend><img src="../img/admin/cog.gif" alt="" class="middle" />' . $this->l('Settings') . '</legend>
                    <label>' . $this->l('Transition Type') . '</label>
                    <div class="margin-form">
                        <select name ="animation_type">';
                        $animationCurrent = Tools::getValue('animation_type');
	                foreach($this->getTransitionTypeArray() as $key => $val){
                            if($animationCurrent == $key) { 
                                $this->_html.='<option value='.$key.' selected="selected" > '.$val.'</option>';
                            }else {
                                 $this->_html.='<option value='.$key.'>'.$val.'</option>';
                            }
                        }
                 $this->_html.='</select>
                    </div>
                    <label>'.$this->l('Pause Time: ').'</label>
                    <div class="margin-form">
                            <input type = "text"  name="pause_time" value ='. (Tools::getValue('pause_time')?Tools::getValue('pause_time'): Configuration::get($this->name.'_pause_time')).' ></input>
                    </div>
                    <label>'.$this->l('Start from slide: ').'</label>
                    <div class="margin-form">
                            <input type = "text"  name="start_slide" value ='.(Tools::getValue('start_slide')?Tools::getValue('start_slide'): Configuration::get($this->name.'_start_slide')).' ></input>
                    </div>
                     <label>'.$this->l('Animation Speed: ').'</label>
                    <div class="margin-form">
                            <input type = "text"  name="animation_speed" value ='.(Tools::getValue('animation_speed')?Tools::getValue('animation_speed'): Configuration::get($this->name.'_animation_speed')).' ></input>
                    </div>
                    <label>'.$this->l('Qty of items: ').'</label>
                    <div class="margin-form">
                            <input type = "text"  name="qty_item" value ='.(Tools::getValue('qty_item')?Tools::getValue('qty_item'): Configuration::get($this->name.'_qty_item')).' ></input>
                    </div>
                    <label>'.$this->l('Show Caption: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_caption', (Tools::getValue('title') ? Tools::getValue('show_caption') : Configuration::get($this->name . '_show_caption')));
                       $this->_html .='
                    </div>
                    <label>'.$this->l('Show Next/Back: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_arrow',  (Tools::getValue('title') ? Tools::getValue('show_arrow') : Configuration::get($this->name . '_show_arrow')));
                       $this->_html .='
                    </div>
                     <label>'.$this->l('Show navigation control: ').'</label>
                    <div class="margin-form">';
                       $this->_html .= $this->getSelectOptionsHtml(array(0=>'No',1=>'Yes'),'show_navigation', (Tools::getValue('title') ? Tools::getValue('show_navigation') : Configuration::get($this->name . '_show_navigation')));
                       $this->_html .='
                    </div>
                    <input type="submit" name="submit" value="'.$this->l('Update').'" class="button" />
                     </fieldset>
		</form>';
	}
        public function getSelectOptionsHtml($options = NULL, $name = NULL, $selected = NULL) {
            $html = "";
            $html .='<select name ='.$name.'>';
            if(count($options)>0) {
                foreach($options as $key => $val) {
                    if(trim($key) == trim($selected)) {
                        $html .='<option value='.$key.' selected="selected">'.$val.'</option>';
                    } else {
                        $html .='<option value='.$key.'>'.$val.'</option>';
                    }
                }
            }
            $html .= '</select>';
            return $html;
        }
        
        public function getTransitionTypeArray() {
            return array(
                "random" => "random",   
                "sliceDown" => "sliceDown",
                "sliceDownLeft" => "sliceDownLeft",
                "sliceUp" => "sliceUp",
                "sliceUpLeft" => "sliceUpLeft",
                "sliceUpDown" => "sliceUpDown",
                "sliceUpDownLeft" => "sliceUpDownLeft",
                "fold" => "fold",
                "fade" => "fade",
                "slideInRight" => "slideInRight",
                "slideInLeft" => "slideInLeft",
                "boxRandom" => "boxRandom",
                "boxRain" => "boxRain",
                "boxRainReverse" => "boxRainReverse",
                "boxRainGrow" => "boxRainGrow",
                "boxRainGrowReverse" => "boxRainGrowReverse",
            );
        }
        
        public function hookDisplayHeader()
        { 
			$this->context->controller->addCSS($this->_path.'views/css/nivo-slider/nivo-slider.css');
			$this->context->controller->addJS($this->_path.'views/js/nivo-slider/jquery.nivo.slider.js');
        }
	  function hookBannerSlider($params)
	{ 
		 $options = array(
                'enable_md' => Configuration::get($this->name.'_enable_md'),
                'animation_type' => Configuration::get($this->name.'_animation_type'),
                'pause_time' => Configuration::get($this->name.'_pause_time'),
                'animation_speed' => Configuration::get($this->name.'_animation_speed'),
                'qty_item' => Configuration::get($this->name.'_qty_item'),
                'show_thumbnail' => Configuration::get($this->name.'_show_thumbnail'),
                'show_caption' => Configuration::get($this->name.'_show_caption'),
                'show_arrow' => Configuration::get($this->name.'_show_arrow'),
                'show_navigation' => Configuration::get($this->name.'_show_navigation'),
                'start_slide' => Configuration::get($this->name.'_start_slide'),

            );

            $slides = $this->getBannerslider();
            
            $this->context->smarty->assign('slideOptions', $options);
            $this->context->smarty->assign('slides', $slides);
            return $this->display(__FILE__, 'bannerslider.tpl');
	}
	 function hookLeftColumn($params)
	{ 
		 $options = array(
                'enable_md' => Configuration::get($this->name.'_enable_md'),
                'animation_type' => Configuration::get($this->name.'_animation_type'),
                'pause_time' => Configuration::get($this->name.'_pause_time'),
                'animation_speed' => Configuration::get($this->name.'_animation_speed'),
                'qty_item' => Configuration::get($this->name.'_qty_item'),
                'show_thumbnail' => Configuration::get($this->name.'_show_thumbnail'),
                'show_caption' => Configuration::get($this->name.'_show_caption'),
                'show_arrow' => Configuration::get($this->name.'_show_arrow'),
                'show_navigation' => Configuration::get($this->name.'_show_navigation'),
                'start_slide' => Configuration::get($this->name.'_start_slide'),

            );

            $slides = $this->getBannerslider();
            
            $this->context->smarty->assign('slideOptions', $options);
            $this->context->smarty->assign('slides', $slides);
            return $this->display(__FILE__, 'bannerslider.tpl');
	}
	
		 function hookRightColumn($params)
	{ 
		 $options = array(
                'enable_md' => Configuration::get($this->name.'_enable_md'),
                'animation_type' => Configuration::get($this->name.'_animation_type'),
                'pause_time' => Configuration::get($this->name.'_pause_time'),
                'animation_speed' => Configuration::get($this->name.'_animation_speed'),
                'qty_item' => Configuration::get($this->name.'_qty_item'),
                'show_thumbnail' => Configuration::get($this->name.'_show_thumbnail'),
                'show_caption' => Configuration::get($this->name.'_show_caption'),
                'show_arrow' => Configuration::get($this->name.'_show_arrow'),
                'show_navigation' => Configuration::get($this->name.'_show_navigation'),
                'start_slide' => Configuration::get($this->name.'_start_slide'),

            );

            $slides = $this->getBannerslider();
            
            $this->context->smarty->assign('slideOptions', $options);
            $this->context->smarty->assign('slides', $slides);
            return $this->display(__FILE__, 'bannerslider.tpl');
	}
        public function hookActionObjectBannersliderAddAfter($params) {
	
		return true;
	}
	
	private function _installHookCustomer(){
		$hooksfield = array(
				'bannerSlider',
			); 
		foreach( $hooksfield as $hook ){
			if( Hook::getIdByName($hook) ){
				
			} else {
				$new_hook = new Hook();
				$new_hook->name = pSQL($hook);
				$new_hook->title = pSQL($hook);
				$new_hook->add();
				$id_hook = $new_hook->id;
			}
		}
		return true;
	}

}