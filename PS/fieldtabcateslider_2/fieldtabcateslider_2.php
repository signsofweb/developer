<?php
/*
*  2015 Fieldthemes
*
*  @author Fieldthemes <fieldthemes@gmail.com>
*/

if (!defined('_PS_VERSION_'))
	exit;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class FieldTabCateSlider_2 extends Module
{

	public function __construct()
	{
		$this->name = 'fieldtabcateslider_2';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Fieldthemes';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Field tab categories_2');
		$this->description = $this->l('Displays product on one categories in the your homepage.');
	}

	public function install()
	{
                $this->_createTab();
                $this->_createConfigs();

		if (!parent::install()
			|| !$this->registerHook('displayHeader')
			|| !$this->registerHook('tabcateslider_2')
		)
			return false;

		return true;
	}

	public function uninstall()
	{
                $this->_deleteTab();
                $this->_deleteConfigs();
		return parent::uninstall();
	}
        
        /* ------------------------------------------------------------- */
        /*  CREATE CONFIGS
        /* ------------------------------------------------------------- */
        private function _createConfigs()
        {
            $languages = $this->context->language->getLanguages();

            foreach ($languages as $language){
                $title[$language['id_lang']] = 'Electronics';
                  $links[$language['id_lang']] = '#';
				  $ctlinks[$language['id_lang']] = '#';
            }
            $arrayDefault = array(2,3);
            $cateDefault = implode(',',$arrayDefault);
            $response = Configuration::updateGlobalValue('FIELD_TABCATEPSL_2_CAT',$cateDefault);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_NBR', 6);
	    $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_PR_AUTO', '');
			$response &= Configuration::updateValue('FIELD_TABCATEPSL_2_ID_PR', 1);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_TITLE', $title);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_LINKS', $links);
			$response &= Configuration::updateValue('FIELD_TABCATEPSL_2_CTLINKS', $ctlinks);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_BANNER', $this->context->link->getMediaLink(_MODULE_DIR_.$this->name.'/img/background_image1.jpg'));
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_ROWITEM', 1);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_MAXITEM', 4);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_MEDIUMITEM', 2);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_MINITEM', 1);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_AUTOSCROLL', 0);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY', 5000);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_PAUSEONHOVER', 0);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_PAGINATION', 0);
            $response &= Configuration::updateValue('FIELD_TABCATEPSL_2_NAVIGATION', 1);
            return $response;
        }
        /* ------------------------------------------------------------- */
        /*  DELETE CONFIGS
        /* ------------------------------------------------------------- */
        private function _deleteConfigs()
        {
            $response = Configuration::deleteByName('FIELD_TABCATEPSL_2_TITLE');
		  $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_LINKS');
		  $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_CTLINKS');
		  $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_BANNER');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_ROWITEM');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_MAXITEM');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_MEDIUMITEM');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_MINITEM');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_AUTOSCROLL');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_PAGINATION');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_NAVIGATION');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_PAUSEONHOVER');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_NBR');
	$response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_PR_AUTO');
	$response &= Configuration::deleteByName('FIELD_TABCATEPSL_2_ID_PR');
            $response &= Configuration::deleteByName('FIELD_TABCATEPSL_CAT');
            return $response;
        }
        
        /* ------------------------------------------------------------- */
        /*  CREATE THE TAB MENU
        /* ------------------------------------------------------------- */
        private function _createTab()
        {
            $response = true;

            // First check for parent tab
            $parentTabID = Tab::getIdFromClassName('AdminFieldMenu');

            if ($parentTabID) {
                $parentTab = new Tab($parentTabID);
            } else {
                $parentTab = new Tab();
                $parentTab->active = 1;
                $parentTab->name = array();
                $parentTab->class_name = "AdminFieldMenu";
                foreach (Language::getLanguages() as $lang){
                    $parentTab->name[$lang['id_lang']] = "Fieldthemes";
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
				$parentTab_2->id_parent = $parentTab_2->id;
				$parentTab_2->module = '';
				$response &= $parentTab_2->add();
			}
			// Created tab
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminFieldTabCateSlider_2";
            $tab->name = array();
            foreach (Language::getLanguages() as $lang){
                $tab->name[$lang['id_lang']] = "Configure tabcateslider_2";
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
            $id_tab = Tab::getIdFromClassName('AdminFieldTabCateSlider_2');
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

	public function getContent()
	{
		$this->context->controller->addJS($this->_path.'views/templates/admin/js/jquery.fieldtabcateslider2.js');
		$languages = $this->context->language->getLanguages();
		$id_shop = (int) Context::getContext()->shop->id;
		$output = '';
		$errors = array();
		if (Tools::isSubmit('submitFieldTabCateSlider_2'))
		{
                        $title = array();

                        foreach ($languages as $language){
                            if (Tools::isSubmit('FIELD_TABCATEPSL_2_TITLE_'.$language['id_lang'])){
                                $title[$language['id_lang']] = Tools::getValue('FIELD_TABCATEPSL_2_TITLE_'.$language['id_lang']);
                            }
                        }
                        if (isset($title) && $title){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_TITLE', $title);
                        }
                        $links = array();
						$ctlinks = array();

                        foreach ($languages as $language){
                            if (Tools::isSubmit('FIELD_TABCATEPSL_2_LINKS_'.$language['id_lang'])){
                                $links[$language['id_lang']] = Tools::getValue('FIELD_TABCATEPSL_2_LINKS_'.$language['id_lang']);
                            }
                        }
						foreach ($languages as $language){
                            if (Tools::isSubmit('FIELD_TABCATEPSL_2_CTLINKS_'.$language['id_lang'])){
                                $ctlinks[$language['id_lang']] = Tools::getValue('FIELD_TABCATEPSL_2_CTLINKS_'.$language['id_lang']);
                            }
                        }
                        if (isset($links) && $links){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_LINKS', $links);
                        }
						if (isset($ctlinks) && $ctlinks){
							 Configuration::updateValue('FIELD_TABCATEPSL_2_CTLINKS', $ctlinks, true);
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_BANNER')){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_BANNER', Tools::getValue('FIELD_TABCATEPSL_2_BANNER'));
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_AUTOSCROLL')){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_AUTOSCROLL', Tools::getValue('FIELD_TABCATEPSL_2_AUTOSCROLL'));
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_PAUSEONHOVER')){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_PAUSEONHOVER', (int)Tools::getValue('FIELD_TABCATEPSL_2_PAUSEONHOVER'));
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_PAGINATION')){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_PAGINATION', (int)Tools::getValue('FIELD_TABCATEPSL_2_PAGINATION'));
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_NAVIGATION')){
                            Configuration::updateValue('FIELD_TABCATEPSL_2_NAVIGATION', (int)Tools::getValue('FIELD_TABCATEPSL_2_NAVIGATION'));
                        }
                        if (Tools::isSubmit('FIELD_TABCATEPSL_2_CAT'))
                                Configuration::updateValue('FIELD_TABCATEPSL_2_CAT', implode(',',Tools::getValue('FIELD_TABCATEPSL_2_CAT')));
			if (Tools::isSubmit('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY') || Tools::isSubmit('FIELD_TABCATEPSL_2_ROWITEM') || Tools::isSubmit('FIELD_TABCATEPSL_2_MAXITEM') || Tools::isSubmit('FIELD_TABCATEPSL_2_MEDIUMITEM') || Tools::isSubmit('FIELD_TABCATEPSL_2_MINITEM') || Tools::isSubmit('FIELD_TABCATEPSL_2_NBR')){
                            if (Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY')) && Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_ROWITEM')) && Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_MAXITEM')) && Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_MEDIUMITEM')) && Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_MINITEM')) && Validate::isInt(Tools::getValue('FIELD_TABCATEPSL_2_NBR'))){
                                Configuration::updateValue('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY', Tools::getValue('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY'));
                                Configuration::updateValue('FIELD_TABCATEPSL_2_ROWITEM', Tools::getValue('FIELD_TABCATEPSL_2_ROWITEM'));
                                Configuration::updateValue('FIELD_TABCATEPSL_2_MAXITEM', Tools::getValue('FIELD_TABCATEPSL_2_MAXITEM'));
                                Configuration::updateValue('FIELD_TABCATEPSL_2_MEDIUMITEM', Tools::getValue('FIELD_TABCATEPSL_2_MEDIUMITEM'));
                                Configuration::updateValue('FIELD_TABCATEPSL_2_MINITEM', Tools::getValue('FIELD_TABCATEPSL_2_MINITEM'));
                                Configuration::updateValue('FIELD_TABCATEPSL_2_NBR', Tools::getValue('FIELD_TABCATEPSL_2_NBR'));
								Configuration::updateValue('FIELD_TABCATEPSL_2_PR_AUTO', Tools::getValue('FIELD_TABCATEPSL_2_PR_AUTO'));
								Configuration::updateValue('FIELD_TABCATEPSL_2_ID_PR', Tools::getValue('FIELD_TABCATEPSL_2_ID_PR'));
                            } else {
                                $errors[] = $this->l('value must be a numeric value!');
                            }
                        }
			if (isset($errors) && count($errors))
				$output = $this->displayError(implode('<br />', $errors));
			else
				$output = $this->displayConfirmation($this->l('Your settings have been updated.'));
		}
		return $output.$this->renderForm();
	}

	/* ------------------------------------------------------------- */
	/*  GET CATEGORIES WITH NICE FORMATTING
	/* ------------------------------------------------------------- */
	private function _getCategories($id_category = 1, $id_shop = false, $recursive = true)
	{
		$id_lang = $this->context->language->id;

		$category = new Category((int) $id_category, (int) $id_lang, (int) $id_shop);

		if (is_null($category->id))
			return;

		if ($recursive){
			$children = Category::getChildren((int) $id_category, (int) $id_lang, true, (int) $id_shop);
			if ($category->level_depth == 0) {
				$depth = $category->level_depth;
			} else {
				$depth = $category->level_depth - 1;
			}

			$spacer = str_repeat('&mdash;', 1 * $depth);
		}

		$this->_categorySelect[] = array(
			'value' =>  (int) $category->id,
			'name' => (isset($spacer) ? $spacer : '') . $category->name
		);

		if (isset($children) && count($children)){
			foreach ($children as $child){
				$this->_getCategories((int) $child['id_category'], (int) $child['id_shop'], true);
			}
		}
	}
        
	public function renderForm()
	{
                $id_default_lang = $this->context->language->id;
                $languages = $this->context->language->getLanguages();
                $id_shop = $this->context->shop->id;
                $root_category = Category::getRootCategory($id_default_lang);
                $this->_getCategories($root_category->id_category, $id_shop);
                
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'description' => $this->l('To add products to your homepage, simply add them to the corresponding product category (default: "Home").'),
				'input' => array(
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_TABCATEPSL_2_TITLE',
                                                'label' => $this->l('Title'),
                                                'desc' => $this->l('This title will appear just before the bestseller block, leave it empty to hide it completely'),
                                                'required' => false,
                                                'lang' => true,
                                            ),
                                     array(
                                                'type' => 'text',
                                                'name' => 'FIELD_TABCATEPSL_2_LINKS',
                                                'label' => $this->l('Links'),
                                                'desc' => $this->l('This title will appear just before the bestseller block, leave it empty to hide it completely'),
                                                'required' => false,
                                                'lang' => true,
                                            ),
											 array(
                    'type' => 'textarea',
                    'label' => $this->l('Custom links'),
                    'name' => 'FIELD_TABCATEPSL_2_CTLINKS',
                    'autoload_rte' => TRUE,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}'
                ),
				  array(
						'type' => 'background_image',
						'name' => 'FIELD_TABCATEPSL_2_BANNER',
						'label' => $this->l('Banner image'),
						'size' => 30,

					),
					array(
						'type' => 'text',
						'label' => $this->l('Number of products to be displayed'),
						'name' => 'FIELD_TABCATEPSL_2_NBR',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Set the number of products that you would like to display on homepage (default: 8).'),
					),
                array(
                    'type' => 'text',
                    'label' => $this->l('Add a product'),
                    'name' => 'FIELD_TABCATEPSL_2_PR_AUTO',
                    'size' => 50,
                ), array(
                    'type' => 'hidden',
                    'label' => $this->l('Add a product'),
                    'name' => 'FIELD_TABCATEPSL_2_ID_PR',
                    'size' => 50,
                ),
                                        array(
                                            'type' => 'select',
                                            'name' => 'FIELD_TABCATEPSL_2_CAT[]',
                                            'label' => $this->l('Select a category'),
                                            'required' => false,
                                            'multiple' => true,
                                            'size' => 10,
                                            'class' => 'fixed-width-xxl',
                                            'lang' => false,
                                            'options' => array(
                                                'query' => $this->_categorySelect,
                                                'id' => 'value',
                                                'name' => 'name'
                                            )
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_TABCATEPSL_2_ROWITEM',
                                                'label' => $this->l('Row item'),
                                                'desc' => $this->l('The number of row item'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_TABCATEPSL_2_MAXITEM',
                                                'label' => $this->l('Max item'),
                                                'desc' => $this->l('The item number is showing on desstop screen'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_TABCATEPSL_2_MEDIUMITEM',
                                                'label' => $this->l('Medium item'),
                                                'desc' => $this->l('The item number is showing on tablet'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                            'type' => 'text',
                                            'name' => 'FIELD_TABCATEPSL_2_MINITEM',
                                            'label' => $this->l('Min item'),
                                            'desc' => $this->l('The item number is showing on mobile'),
                                            'suffix' => 'item',
                                            'class' => 'fixed-width-xs',
                                            'required' => false,
                                            'lang' => false,
                                        ),
                                        array(
                                            'type' => 'switch',
                                            'label' => $this->l('Auto scroll'),
                                            'desc' => $this->l('Scroll the item automatically'),
                                            'name' => 'FIELD_TABCATEPSL_2_AUTOSCROLL',
                                            'required' => false,
                                            'is_bool' => true,
                                            'values' => array(
                                                array(
                                                    'id' => 'autoscroll_on',
                                                    'value' => 1,
                                                    'label' => $this->l('On')
                                                ),
                                                array(
                                                    'id' => 'autoscroll_off',
                                                    'value' => 0,
                                                    'label' => $this->l('Off')
                                                )
                                            )
                                        ),
                                        array(
                                            'type' => 'text',
                                            'name' => 'FIELD_TABCATEPSL_2_AUTOSCROLLDELAY',
                                            'label' => $this->l('Auto scroll delay'),
                                            'desc' => $this->l('Delay between the auto scrolls'),
                                            'suffix' => 'milliseconds',
                                            'required' => false,
                                            'lang' => false,
                                        ),
                                        array(
                                            'type' => 'switch',
                                            'label' => $this->l('Pause on hover'),
                                            'desc' => $this->l('Pause the carousel on mouse hover'),
                                            'name' => 'FIELD_TABCATEPSL_2_PAUSEONHOVER',
                                            'required' => false,
                                            'is_bool' => true,
                                            'values' => array(
                                                array(
                                                    'id' => 'pauseonhover_on',
                                                    'value' => 1,
                                                    'label' => $this->l('On')
                                                ),
                                                array(
                                                    'id' => 'pauseonhover_off',
                                                    'value' => 0,
                                                    'label' => $this->l('Off')
                                                )
                                            )
                                        ),
                                        array(
                                            'type' => 'switch',
                                            'label' => $this->l('Show pagination'),
                                            'name' => 'FIELD_TABCATEPSL_2_PAGINATION',
                                            'required' => false,
                                            'is_bool' => true,
                                            'values' => array(
                                                array(
                                                    'id' => 'pagination_on',
                                                    'value' => 1,
                                                    'label' => $this->l('On')
                                                ),
                                                array(
                                                    'id' => 'pagination_off',
                                                    'value' => 0,
                                                    'label' => $this->l('Off')
                                                )
                                            )
                                        ),
                                        array(
                                            'type' => 'switch',
                                            'label' => $this->l('Show navigation'),
                                            'name' => 'FIELD_TABCATEPSL_2_NAVIGATION',
                                            'required' => false,
                                            'is_bool' => true,
                                            'values' => array(
                                                array(
                                                    'id' => 'navigation_on',
                                                    'value' => 1,
                                                    'label' => $this->l('On')
                                                ),
                                                array(
                                                    'id' => 'navigation_off',
                                                    'value' => 0,
                                                    'label' => $this->l('Off')
                                                )
                                            )
                                        )
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		$helper = new HelperForm();
		$helper->module = $this;
                $helper->name_controller = $this->name;
                $helper->token = Tools::getAdminTokenLite('AdminModules');
                $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

                $helper->default_form_language = $id_default_lang;
                $helper->allow_employee_form_lang = $id_default_lang;

                $helper->title = $this->displayName;
                $helper->show_toolbar = true;
                $helper->toolbar_scroll = true;
                $helper->submit_action = 'submitFieldTabCateSlider_2';

                foreach($languages as $language){
                    $helper->languages[] = array(
                        'id_lang' => $language['id_lang'],
                        'iso_code' => $language['iso_code'],
                        'name' => $language['name'],
                        'is_default' => ($id_default_lang == $language['id_lang'] ? 1 : 0)
                    );
                }

                // Load current values
                  $helper->fields_value['FIELD_TABCATEPSL_2_BANNER'] = Configuration::get('FIELD_TABCATEPSL_2_BANNER');
                $helper->fields_value['FIELD_TABCATEPSL_2_ROWITEM'] = Configuration::get('FIELD_TABCATEPSL_2_ROWITEM');
                $helper->fields_value['FIELD_TABCATEPSL_2_MAXITEM'] = Configuration::get('FIELD_TABCATEPSL_2_MAXITEM');
                $helper->fields_value['FIELD_TABCATEPSL_2_MEDIUMITEM'] = Configuration::get('FIELD_TABCATEPSL_2_MEDIUMITEM');
                $helper->fields_value['FIELD_TABCATEPSL_2_MINITEM'] = Configuration::get('FIELD_TABCATEPSL_2_MINITEM');
                $helper->fields_value['FIELD_TABCATEPSL_2_AUTOSCROLL'] = Configuration::get('FIELD_TABCATEPSL_2_AUTOSCROLL');
                $helper->fields_value['FIELD_TABCATEPSL_2_AUTOSCROLLDELAY'] = Configuration::get('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY');
                $helper->fields_value['FIELD_TABCATEPSL_2_PAUSEONHOVER'] = Configuration::get('FIELD_TABCATEPSL_2_PAUSEONHOVER');
                $helper->fields_value['FIELD_TABCATEPSL_2_PAGINATION'] = Configuration::get('FIELD_TABCATEPSL_2_PAGINATION');
                $helper->fields_value['FIELD_TABCATEPSL_2_NAVIGATION'] = Configuration::get('FIELD_TABCATEPSL_2_NAVIGATION');
                $helper->fields_value['FIELD_TABCATEPSL_2_NBR'] = Configuration::get('FIELD_TABCATEPSL_2_NBR');
				 $helper->fields_value['FIELD_TABCATEPSL_2_PR_AUTO'] = Configuration::get('FIELD_TABCATEPSL_2_PR_AUTO');
				 $helper->fields_value['FIELD_TABCATEPSL_2_ID_PR'] = Configuration::get('FIELD_TABCATEPSL_2_ID_PR');
                $helper->fields_value['FIELD_TABCATEPSL_2_CAT[]'] = explode(',' ,Configuration::get('FIELD_TABCATEPSL_2_CAT'));

                foreach($languages as $language){
                    $helper->fields_value['FIELD_TABCATEPSL_2_TITLE'][$language['id_lang']] = Configuration::get('FIELD_TABCATEPSL_2_TITLE', $language['id_lang']);
                    $helper->fields_value['FIELD_TABCATEPSL_2_LINKS'][$language['id_lang']] = Configuration::get('FIELD_TABCATEPSL_2_LINKS', $language['id_lang']);
					$helper->fields_value['FIELD_TABCATEPSL_2_CTLINKS'][$language['id_lang']] = Configuration::get('FIELD_TABCATEPSL_2_CTLINKS', $language['id_lang']);
                }
		return $helper->generateForm(array($fields_form));
	}
	
	/* ------------------------------------------------------------- */
	/*  PREPARE FOR HOOK
	/* ------------------------------------------------------------- */

	private function _prepHook($params)
	{
		$this->context->controller->addJqueryPlugin('plugins', $this->_path . 'views/js/hook/');
		$id_default_lang = $this->context->language->id;

		$fieldtabcatepsl_2 = array(
			'FIELD_TABCATEPSL_2_TITLE' => Configuration::get('FIELD_TABCATEPSL_2_TITLE', $id_default_lang),
			'FIELD_TABCATEPSL_2_LINKS' => Configuration::get('FIELD_TABCATEPSL_2_LINKS', $id_default_lang),
			'FIELD_TABCATEPSL_2_CTLINKS' => Configuration::get('FIELD_TABCATEPSL_2_CTLINKS', $id_default_lang),
			'FIELD_TABCATEPSL_2_BANNER' => Configuration::get('FIELD_TABCATEPSL_2_BANNER'),
			'FIELD_TABCATEPSL_2_ROWITEM' => Configuration::get('FIELD_TABCATEPSL_2_ROWITEM'),
			'FIELD_TABCATEPSL_2_MAXITEM' => Configuration::get('FIELD_TABCATEPSL_2_MAXITEM'),
			'FIELD_TABCATEPSL_2_MEDIUMITEM' => Configuration::get('FIELD_TABCATEPSL_2_MEDIUMITEM'),
			'FIELD_TABCATEPSL_2_MINITEM' => Configuration::get('FIELD_TABCATEPSL_2_MINITEM'),
			'FIELD_TABCATEPSL_2_AUTOSCROLL' => Configuration::get('FIELD_TABCATEPSL_2_AUTOSCROLL'),
			'FIELD_TABCATEPSL_2_AUTOSCROLLDELAY' => Configuration::get('FIELD_TABCATEPSL_2_AUTOSCROLLDELAY'),
			'FIELD_TABCATEPSL_2_PAUSEONHOVER' => Configuration::get('FIELD_TABCATEPSL_2_PAUSEONHOVER'),
			'FIELD_TABCATEPSL_2_PAGINATION' => Configuration::get('FIELD_TABCATEPSL_2_PAGINATION'),
			'FIELD_TABCATEPSL_2_NAVIGATION' => Configuration::get('FIELD_TABCATEPSL_2_NAVIGATION'),
			'FIELD_TABCATEPSL_2_NBR' => Configuration::get('FIELD_TABCATEPSL_2_NBR'),
		);
		$this->smarty->assign('fieldtabcatepsl_2', $fieldtabcatepsl_2);
	}

	/* ------------------------------------------------------------- */
	/*  getProducts
	/* ------------------------------------------------------------- */
	public function getProducts($params)
	{   
                $nb = (int)Configuration::get('FIELD_TABCATEPSL_2_NBR');
                $arrayCategory = array();
                $catSelected = Configuration::get('FIELD_TABCATEPSL_2_CAT');
                $cateArray = explode(',', $catSelected); 
                $id_lang =(int) Context::getContext()->language->id;
                $id_shop = (int) Context::getContext()->shop->id;
                $arrayProductCate = array();
                foreach($cateArray as $id_category) {
                        $category = new Category((int) $id_category, (int) $id_lang, (int) $id_shop);
                        $categoryProducts = $category->getProducts((int)Context::getContext()->language->id, 1, ($nb ? $nb : 8), 'position');
						
						$assembler = new ProductAssembler($this->context);
						$presenterFactory = new ProductPresenterFactory($this->context);
						$presentationSettings = $presenterFactory->getPresentationSettings();
						$presenter = new ProductListingPresenter(
							new ImageRetriever(
								$this->context->link
							),
							$this->context->link,
							new PriceFormatter(),
							new ProductColorsRetriever(),
							$this->context->getTranslator()
						);
						$products_for_template = [];
						if(is_array($categoryProducts)){
						foreach ($categoryProducts as $rawProduct) {
							$products_for_template[] = $presenter->present(
								$presentationSettings,
								$assembler->assembleProduct($rawProduct),
								$this->context->language
							);
							}
						}
                        if($products_for_template) {
                                $arrayProductCate[] = array('id' => $id_category, 'name'=> $category->name, 'description'=> $category->description, 'product' => $products_for_template);
                        }
                }

		if (!isset($arrayProductCate) || empty($arrayProductCate))
                    return false;
                else {
                    $this->_prepHook($params);
                }
				////////////////////////////////////////////////////////////
			$id_product_ct=Configuration::get('FIELD_TABCATEPSL_2_ID_PR');
			$customProduct = get_object_vars(new Product($id_product_ct, true, $id_lang));
                           $customProduct['id_product'] = $customProduct['id'];

                            $coverImage = Product::getCover($customProduct['id_product']);
                            $customProduct['id_image'] = $coverImage['id_image'];

                            $products_= Product::getProductProperties($id_lang, $customProduct);
			$assembler = new ProductAssembler($this->context);
						$presenterFactory = new ProductPresenterFactory($this->context);
						$presentationSettings = $presenterFactory->getPresentationSettings();
						$presenter = new ProductListingPresenter(
							new ImageRetriever(
								$this->context->link
							),
							$this->context->link,
							new PriceFormatter(),
							new ProductColorsRetriever(),
							$this->context->getTranslator()
						);
						$products_for_template_=array();
						if(isset($products_) && $products_){

							$products_for_template_= $presenter->present(
								$presentationSettings,
								$assembler->assembleProduct($products_),
								$this->context->language
							);

						}				
			////////////////////////////////////////////////////////////
				
			$this->smarty->assign(
				array(
					'product_ct' => $products_for_template_,
					'productCates' => $arrayProductCate,
					'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY')
				)
			);
                    return $this->display(__FILE__, 'fieldtabcateslider_2.tpl');
	}
	/* ------------------------------------------------------------- */
	/*  hookTabcateslider_2
	/* ------------------------------------------------------------- */
	public function hookTabcateslider_2($params)
	{   
		return $this->getProducts($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookDisplayHeader
	/* ------------------------------------------------------------- */
	public function hookDisplayHeader($params)
	{

	}
	/* ------------------------------------------------------------- */
	/*  hookDisplayHome
	/* ------------------------------------------------------------- */
	public function hookDisplayHome($params){
		return $this->hookTabcateslider_2($params);
	}
	
}
