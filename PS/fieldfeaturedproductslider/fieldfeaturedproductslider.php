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
class FieldFeaturedProductSlider extends Module
{
	public function __construct()
	{
		$this->name = 'fieldfeaturedproductslider';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Fieldthemes';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Field featured products');
		$this->description = $this->l('Displays featured products in the your homepage.');
	}

	public function install()
	{
                $this->_createTab();
                $this->_createConfigs();

		if (!parent::install()
			|| !$this->registerHook('displayHeader')
			|| !$this->registerHook('featuredproductslider')
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
                $title[$language['id_lang']] = 'Featured product';
            }
            $response = Configuration::updateValue('FIELD_FEATUREDPSL_NBR', 8);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_CAT', (int)Context::getContext()->shop->getCategory());
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_TITLE', $title);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_VERTICAL', 0);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_COLUMNITEM', 1);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_MAXITEM', 4);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_MEDIUMITEM', 2);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_MINITEM', 1);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_AUTOSCROLL', 0);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_AUTOSCROLLDELAY', 5000);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_PAUSEONHOVER', 0);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_PAGINATION', 0);
            $response &= Configuration::updateValue('FIELD_FEATUREDPSL_NAVIGATION', 1);

            return $response;
        }
        
        /* ------------------------------------------------------------- */
        /*  DELETE CONFIGS
        /* ------------------------------------------------------------- */
        private function _deleteConfigs()
        {
            $response = Configuration::deleteByName('FIELD_FEATUREDPSL_TITLE');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_VERTICAL');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_COLUMNITEM');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_MAXITEM');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_MEDIUMITEM');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_MINITEM');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_AUTOSCROLL');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_PAGINATION');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_NAVIGATION');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_AUTOSCROLLDELAY');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_PAUSEONHOVER');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_NBR');
            $response &= Configuration::deleteByName('FIELD_FEATUREDPSL_CAT');

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
				$parentTab_2->id_parent = $parentTab->id;
				$parentTab_2->module = '';
				$response &= $parentTab_2->add();
			}
			// Created tab
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = "AdminFieldFeaturedProductSlider";
            $tab->name = array();
            foreach (Language::getLanguages() as $lang){
                $tab->name[$lang['id_lang']] = "Configure featured products";
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
            $id_tab = Tab::getIdFromClassName('AdminFieldFeaturedProductSlider');
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
                $languages = $this->context->language->getLanguages();
		$output = '';
		$errors = array();
		if (Tools::isSubmit('submitFieldFeaturedProductSlider'))
		{
                        $title = array();

                        foreach ($languages as $language){
                            if (Tools::isSubmit('FIELD_FEATUREDPSL_TITLE_'.$language['id_lang'])){
                                $title[$language['id_lang']] = Tools::getValue('FIELD_FEATUREDPSL_TITLE_'.$language['id_lang']);
                            }
                        }
                        if (isset($title) && $title){
                            Configuration::updateValue('FIELD_FEATUREDPSL_TITLE', $title);
                        }
                        if (Tools::isSubmit('FIELD_FEATUREDPSL_VERTICAL')){
                            Configuration::updateValue('FIELD_FEATUREDPSL_VERTICAL', Tools::getValue('FIELD_FEATUREDPSL_VERTICAL'));
                        }
                        if (Tools::isSubmit('FIELD_FEATUREDPSL_AUTOSCROLL')){
                            Configuration::updateValue('FIELD_FEATUREDPSL_AUTOSCROLL', Tools::getValue('FIELD_FEATUREDPSL_AUTOSCROLL'));
                        }
                        if (Tools::isSubmit('FIELD_FEATUREDPSL_PAUSEONHOVER')){
                            Configuration::updateValue('FIELD_FEATUREDPSL_PAUSEONHOVER', (int)Tools::getValue('FIELD_FEATUREDPSL_PAUSEONHOVER'));
                        }
                        if (Tools::isSubmit('FIELD_FEATUREDPSL_PAGINATION')){
                            Configuration::updateValue('FIELD_FEATUREDPSL_PAGINATION', (int)Tools::getValue('FIELD_FEATUREDPSL_PAGINATION'));
                        }
                        if (Tools::isSubmit('FIELD_FEATUREDPSL_NAVIGATION')){
                            Configuration::updateValue('FIELD_FEATUREDPSL_NAVIGATION', (int)Tools::getValue('FIELD_FEATUREDPSL_NAVIGATION'));
                        }
                        
			if (Tools::isSubmit('FIELD_FEATUREDPSL_CAT') || Tools::isSubmit('FIELD_FEATUREDPSL_AUTOSCROLLDELAY') || Tools::isSubmit('FIELD_FEATUREDPSL_COLUMNITEM') || Tools::isSubmit('FIELD_FEATUREDPSL_MAXITEM') || Tools::isSubmit('FIELD_FEATUREDPSL_MEDIUMITEM') || Tools::isSubmit('FIELD_FEATUREDPSL_MINITEM') || Tools::isSubmit('FIELD_FEATUREDPSL_NBR')){
                            if (Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_CAT')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_AUTOSCROLLDELAY')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_COLUMNITEM')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_MAXITEM')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_MEDIUMITEM')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_MINITEM')) && Validate::isInt(Tools::getValue('FIELD_FEATUREDPSL_NBR'))){
                                Configuration::updateValue('FIELD_FEATUREDPSL_AUTOSCROLLDELAY', Tools::getValue('FIELD_FEATUREDPSL_AUTOSCROLLDELAY'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_COLUMNITEM', Tools::getValue('FIELD_FEATUREDPSL_COLUMNITEM'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_MAXITEM', Tools::getValue('FIELD_FEATUREDPSL_MAXITEM'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_MEDIUMITEM', Tools::getValue('FIELD_FEATUREDPSL_MEDIUMITEM'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_MINITEM', Tools::getValue('FIELD_FEATUREDPSL_MINITEM'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_NBR', Tools::getValue('FIELD_FEATUREDPSL_NBR'));
                                Configuration::updateValue('FIELD_FEATUREDPSL_CAT', Tools::getValue('FIELD_FEATUREDPSL_CAT'));
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
	/*  getProducts
	/* ------------------------------------------------------------- */
     protected function getProducts()
    {
        $category = new Category((int)Configuration::get('FIELD_FEATUREDPSL_CAT'), (int)Context::getContext()->language->id);

        $searchProvider = new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            $category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();

        $nProducts = (int)Configuration::get('FIELD_FEATUREDPSL_NBR');
        if ($nProducts < 0) {
            $nProducts = 12;
        }

        $query
            ->setResultsPerPage($nProducts)
            ->setPage(1)
        ;
        $query->setSortOrder(new SortOrder('product', 'position', 'asc'));
        $result = $searchProvider->runQuery(
            $context,
            $query
        );

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
		$products_features=$result->getProducts();
		if(is_array($products_features)){
			foreach ($products_features as $rawProduct) {
				$products_for_template[] = $presenter->present(
					$presentationSettings,
					$assembler->assembleProduct($rawProduct),
					$this->context->language
				);
			}
		}
        return $products_for_template;
    }  
	/* ------------------------------------------------------------- */
	/*  hookDisplayHeader
	/* ------------------------------------------------------------- */
	public function hookDisplayHeader($params){

	}
	/* ------------------------------------------------------------- */
	/*  hookFeaturedproductslider
	/* ------------------------------------------------------------- */
	public function hookFeaturedproductslider($params){   
		$this->_prepHook($params);
		$featured_products = $this->getProducts();
		$_bannerLink = $this->context->link->getMediaLink(_MODULE_DIR_.$this->name.'/img/feature_banner.jpg');
		$this->smarty->assign(
				array(
						'products' => $featured_products,
                        'bannerlink' => $_bannerLink,
						'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY')
				)
		);
		if(Configuration::get('FIELD_FEATUREDPSL_VERTICAL'))
			return $this->display(__FILE__, 'fieldfeaturedproductslider_vertical.tpl');
		else
			return $this->display(__FILE__, 'fieldfeaturedproductslider.tpl');
	}
	
	/* ------------------------------------------------------------- */
	/*  hookDisplayHome
	/* ------------------------------------------------------------- */
	public function hookDisplayHome($params){
		return $this->hookFeaturedproductslider($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookRightColumn
	/* ------------------------------------------------------------- */
	public function hookRightColumn($params){
		return $this->hookFeaturedproductslider($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookLeftColumn
	/* ------------------------------------------------------------- */
	public function hookLeftColumn($params){
		return $this->hookFeaturedproductslider($params);
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
                                                'name' => 'FIELD_FEATUREDPSL_TITLE',
                                                'label' => $this->l('Title'),
                                                'desc' => $this->l('This title will appear just before the bestseller block, leave it empty to hide it completely'),
                                                'required' => false,
                                                'lang' => true,
                                            ),
					array(
						'type' => 'text',
						'label' => $this->l('Number of products to be displayed'),
						'name' => 'FIELD_FEATUREDPSL_NBR',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Set the number of products that you would like to display on homepage (default: 8).'),
					),
                                        array(
                                            'type' => 'switch',
                                            'label' => $this->l('Enable vertical mode'),
                                            'desc' => $this->l('Vertical slider'),
                                            'name' => 'FIELD_FEATUREDPSL_VERTICAL',
                                            'required' => false,
                                            'is_bool' => true,
                                            'values' => array(
                                                array(
                                                    'id' => 'vertical_on',
                                                    'value' => 1,
                                                    'label' => $this->l('On')
                                                ),
                                                array(
                                                    'id' => 'vertical_off',
                                                    'value' => 0,
                                                    'label' => $this->l('Off')
                                                )
                                            )
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_FEATUREDPSL_COLUMNITEM',
                                                'label' => $this->l('Item to be displayed'),
                                                'desc' => $this->l('The item number is showing in the column'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                            'type' => 'select',
                                            'name' => 'FIELD_FEATUREDPSL_CAT',
                                            'label' => $this->l('Select a category'),
                                            'required' => false,
                                            'lang' => false,
                                            'options' => array(
                                                'query' => $this->_categorySelect,
                                                'id' => 'value',
                                                'name' => 'name'
                                            )
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_FEATUREDPSL_MAXITEM',
                                                'label' => $this->l('Max item'),
                                                'desc' => $this->l('The item number is showing on desstop screen'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_FEATUREDPSL_MEDIUMITEM',
                                                'label' => $this->l('Medium item'),
                                                'desc' => $this->l('The item number is showing on tablet'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                            'type' => 'text',
                                            'name' => 'FIELD_FEATUREDPSL_MINITEM',
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
                                            'name' => 'FIELD_FEATUREDPSL_AUTOSCROLL',
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
                                            'name' => 'FIELD_FEATUREDPSL_AUTOSCROLLDELAY',
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
                                            'name' => 'FIELD_FEATUREDPSL_PAUSEONHOVER',
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
                                            'name' => 'FIELD_FEATUREDPSL_PAGINATION',
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
                                            'name' => 'FIELD_FEATUREDPSL_NAVIGATION',
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
                $helper->submit_action = 'submitFieldFeaturedProductSlider';

                foreach($languages as $language){
                    $helper->languages[] = array(
                        'id_lang' => $language['id_lang'],
                        'iso_code' => $language['iso_code'],
                        'name' => $language['name'],
                        'is_default' => ($id_default_lang == $language['id_lang'] ? 1 : 0)
                    );
                }

                // Load current values
                $helper->fields_value['FIELD_FEATUREDPSL_VERTICAL'] = Configuration::get('FIELD_FEATUREDPSL_VERTICAL');
                $helper->fields_value['FIELD_FEATUREDPSL_COLUMNITEM'] = Configuration::get('FIELD_FEATUREDPSL_COLUMNITEM');
                $helper->fields_value['FIELD_FEATUREDPSL_MAXITEM'] = Configuration::get('FIELD_FEATUREDPSL_MAXITEM');
                $helper->fields_value['FIELD_FEATUREDPSL_MEDIUMITEM'] = Configuration::get('FIELD_FEATUREDPSL_MEDIUMITEM');
                $helper->fields_value['FIELD_FEATUREDPSL_MINITEM'] = Configuration::get('FIELD_FEATUREDPSL_MINITEM');
                $helper->fields_value['FIELD_FEATUREDPSL_AUTOSCROLL'] = Configuration::get('FIELD_FEATUREDPSL_AUTOSCROLL');
                $helper->fields_value['FIELD_FEATUREDPSL_AUTOSCROLLDELAY'] = Configuration::get('FIELD_FEATUREDPSL_AUTOSCROLLDELAY');
                $helper->fields_value['FIELD_FEATUREDPSL_PAUSEONHOVER'] = Configuration::get('FIELD_FEATUREDPSL_PAUSEONHOVER');
                $helper->fields_value['FIELD_FEATUREDPSL_PAGINATION'] = Configuration::get('FIELD_FEATUREDPSL_PAGINATION');
                $helper->fields_value['FIELD_FEATUREDPSL_NAVIGATION'] = Configuration::get('FIELD_FEATUREDPSL_NAVIGATION');
                $helper->fields_value['FIELD_FEATUREDPSL_NBR'] = Configuration::get('FIELD_FEATUREDPSL_NBR');
                $helper->fields_value['FIELD_FEATUREDPSL_CAT'] = Configuration::get('FIELD_FEATUREDPSL_CAT');

                foreach($languages as $language){
                    $helper->fields_value['FIELD_FEATUREDPSL_TITLE'][$language['id_lang']] = Configuration::get('FIELD_FEATUREDPSL_TITLE', $language['id_lang']);
                }

		return $helper->generateForm(array($fields_form));
	}
        
        /* ------------------------------------------------------------- */
        /*  PREPARE FOR HOOK
        /* ------------------------------------------------------------- */

        private function _prepHook($params)
        {	
			//$this->context->controller->addJS($this->_path.'views/js/hook/jquery.featuredpslowlcarousel.js');
            $id_default_lang = $this->context->language->id;

            $fieldfeaturedpsl = array(
                'FIELD_FEATUREDPSL_TITLE' => Configuration::get('FIELD_FEATUREDPSL_TITLE', $id_default_lang),
                'FIELD_FEATUREDPSL_COLUMNITEM' => Configuration::get('FIELD_FEATUREDPSL_COLUMNITEM'),
                'FIELD_FEATUREDPSL_MAXITEM' => Configuration::get('FIELD_FEATUREDPSL_MAXITEM'),
                'FIELD_FEATUREDPSL_MEDIUMITEM' => Configuration::get('FIELD_FEATUREDPSL_MEDIUMITEM'),
                'FIELD_FEATUREDPSL_MINITEM' => Configuration::get('FIELD_FEATUREDPSL_MINITEM'),
                'FIELD_FEATUREDPSL_AUTOSCROLL' => Configuration::get('FIELD_FEATUREDPSL_AUTOSCROLL'),
                'FIELD_FEATUREDPSL_AUTOSCROLLDELAY' => Configuration::get('FIELD_FEATUREDPSL_AUTOSCROLLDELAY'),
                'FIELD_FEATUREDPSL_PAUSEONHOVER' => Configuration::get('FIELD_FEATUREDPSL_PAUSEONHOVER'),
                'FIELD_FEATUREDPSL_PAGINATION' => Configuration::get('FIELD_FEATUREDPSL_PAGINATION'),
                'FIELD_FEATUREDPSL_NAVIGATION' => Configuration::get('FIELD_FEATUREDPSL_NAVIGATION'),
                'FIELD_FEATUREDPSL_NBR' => Configuration::get('FIELD_FEATUREDPSL_NBR'),
            );

            $this->smarty->assign('fieldfeaturedpsl', $fieldfeaturedpsl);
        }

}
