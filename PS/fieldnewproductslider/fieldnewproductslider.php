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
class FieldNewProductSlider extends Module
{

	public function __construct()
	{
		$this->name = 'fieldnewproductslider';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Fieldthemes';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Field New products');
		$this->description = $this->l('Displays new products in any where of your homepage.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		$success = (parent::install()
			&& $this->registerHook('displayHeader')
			&& $this->registerHook('newproductslider')
		);

                $this->_createTab();
                $this->_createConfigs();

		return $success;
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
                $title[$language['id_lang']] = 'New products';
            }
            $response = Configuration::updateValue('FIELD_NEWPSL_NBR', 6);
            $response &= Configuration::updateValue('FIELD_NEWPSL_DISPLAY', 1);
            $response &= Configuration::updateValue('FIELD_NEWPSL_TITLE', $title);
            $response &= Configuration::updateValue('FIELD_NEWPSL_VERTICAL', 0);
            $response &= Configuration::updateValue('FIELD_NEWPSL_COLUMNITEM', 3);
            $response &= Configuration::updateValue('FIELD_NEWPSL_MAXITEM', 3);
            $response &= Configuration::updateValue('FIELD_NEWPSL_MEDIUMITEM', 2);
            $response &= Configuration::updateValue('FIELD_NEWPSL_MINITEM', 1);
            $response &= Configuration::updateValue('FIELD_NEWPSL_AUTOSCROLL', 0);
            $response &= Configuration::updateValue('FIELD_NEWPSL_AUTOSCROLLDELAY', 5000);
            $response &= Configuration::updateValue('FIELD_NEWPSL_PAUSEONHOVER', 1);
            $response &= Configuration::updateValue('FIELD_NEWPSL_PAGINATION', 1);
            $response &= Configuration::updateValue('FIELD_NEWPSL_NAVIGATION', 1);

            return $response;
        }
        
        /* ------------------------------------------------------------- */
        /*  DELETE CONFIGS
        /* ------------------------------------------------------------- */
        private function _deleteConfigs()
        {
            $response = Configuration::deleteByName('FIELD_NEWPSL_TITLE');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_VERTICAL');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_COLUMNITEM');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_MAXITEM');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_MEDIUMITEM');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_MINITEM');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_AUTOSCROLL');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_PAGINATION');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_NAVIGATION');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_AUTOSCROLLDELAY');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_PAUSEONHOVER');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_NBR');
            $response &= Configuration::deleteByName('FIELD_NEWPSL_DISPLAY');

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
            $tab->class_name = "AdminFieldNewProductSlider";
            $tab->name = array();
            foreach (Language::getLanguages() as $lang){
                $tab->name[$lang['id_lang']] = "Configure new products";
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
            $id_tab = Tab::getIdFromClassName('AdminFieldNewProductSlider');
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
                
		if (Tools::isSubmit('submitFieldNewProductSlider'))
		{
			$title = array();

                        foreach ($languages as $language){
                            if (Tools::isSubmit('FIELD_NEWPSL_TITLE_'.$language['id_lang'])){
                                $title[$language['id_lang']] = Tools::getValue('FIELD_NEWPSL_TITLE_'.$language['id_lang']);
                            }
                        }
                        if (isset($title) && $title){
                            Configuration::updateValue('FIELD_NEWPSL_TITLE', $title);
                        }
                        if (Tools::isSubmit('FIELD_NEWPSL_VERTICAL')){
                            Configuration::updateValue('FIELD_NEWPSL_VERTICAL', Tools::getValue('FIELD_NEWPSL_VERTICAL'));
                        }

                        if (Tools::isSubmit('FIELD_NEWPSL_AUTOSCROLL')){
                            Configuration::updateValue('FIELD_NEWPSL_AUTOSCROLL', Tools::getValue('FIELD_NEWPSL_AUTOSCROLL'));
                        }
                        if (Tools::isSubmit('FIELD_NEWPSL_DISPLAY')){
                            Configuration::updateValue('FIELD_NEWPSL_DISPLAY', (int)Tools::getValue('FIELD_NEWPSL_DISPLAY'));
                        }
                        if (Tools::isSubmit('FIELD_NEWPSL_PAUSEONHOVER')){
                            Configuration::updateValue('FIELD_NEWPSL_PAUSEONHOVER', (int)Tools::getValue('FIELD_NEWPSL_PAUSEONHOVER'));
                        }
                        if (Tools::isSubmit('FIELD_NEWPSL_PAGINATION')){
                            Configuration::updateValue('FIELD_NEWPSL_PAGINATION', (int)Tools::getValue('FIELD_NEWPSL_PAGINATION'));
                        }
                        if (Tools::isSubmit('FIELD_NEWPSL_NAVIGATION')){
                            Configuration::updateValue('FIELD_NEWPSL_NAVIGATION', (int)Tools::getValue('FIELD_NEWPSL_NAVIGATION'));
                        }
                        if (Tools::isSubmit('PS_NB_DAYS_NEW_PRODUCT') || Tools::isSubmit('FIELD_NEWPSL_AUTOSCROLLDELAY') || Tools::isSubmit('FIELD_NEWPSL_MAXITEM') || Tools::isSubmit('FIELD_NEWPSL_MEDIUMITEM') || Tools::isSubmit('FIELD_NEWPSL_MINITEM') || Tools::isSubmit('FIELD_NEWPSL_NBR') || Tools::isSubmit('FIELD_NEWPSL_COLUMNITEM')){
                            if (Validate::isInt(Tools::getValue('PS_NB_DAYS_NEW_PRODUCT')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_AUTOSCROLLDELAY')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_MAXITEM')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_MEDIUMITEM')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_MINITEM')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_NBR')) && Validate::isInt(Tools::getValue('FIELD_NEWPSL_COLUMNITEM'))){
                                Configuration::updateValue('FIELD_NEWPSL_COLUMNITEM', Tools::getValue('FIELD_NEWPSL_COLUMNITEM'));
                                Configuration::updateValue('FIELD_NEWPSL_AUTOSCROLLDELAY', Tools::getValue('FIELD_NEWPSL_AUTOSCROLLDELAY'));
                                Configuration::updateValue('FIELD_NEWPSL_MAXITEM', Tools::getValue('FIELD_NEWPSL_MAXITEM'));
                                Configuration::updateValue('FIELD_NEWPSL_MEDIUMITEM', Tools::getValue('FIELD_NEWPSL_MEDIUMITEM'));
                                Configuration::updateValue('FIELD_NEWPSL_MINITEM', Tools::getValue('FIELD_NEWPSL_MINITEM'));
                                Configuration::updateValue('FIELD_NEWPSL_NBR', Tools::getValue('FIELD_NEWPSL_NBR'));
                                Configuration::updateValue('PS_NB_DAYS_NEW_PRODUCT', Tools::getValue('PS_NB_DAYS_NEW_PRODUCT'));
                            } else {
                                $errors[] = $this->l('value must be a numeric value!');
                            }
                        }
                        if (isset($errors) && count($errors))
                            $output .= $this->displayError(implode('<br />', $errors));
                        else
                            $output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}
	/* ------------------------------------------------------------- */
	/*  getNewProducts
	/* ------------------------------------------------------------- */
	private function getNewProducts()
	{
		if (!Configuration::get('FIELD_NEWPSL_NBR'))
			return;
		$newProducts = false;
		if (Configuration::get('PS_NB_DAYS_NEW_PRODUCT'))
			$newProducts = Product::getNewProducts((int) $this->context->language->id, 0, (int)Configuration::get('FIELD_NEWPSL_NBR'),null,'id_product');
		if (!$newProducts && Configuration::get('FIELD_NEWPSL_DISPLAY'))
			return;								
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
		if(is_array($newProducts)){
        foreach ($newProducts as $rawProduct) {
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
	/*  hookNewproductslider
	/* ------------------------------------------------------------- */
	public function hookNewproductslider($params)
	{
                $this->_prepHook($params);
                $new_products = $this->getNewProducts();				
				$this->smarty->assign(array(
                        'new_products' => $new_products
                ));	
                if(Configuration::get('FIELD_NEWPSL_VERTICAL'))
                    return $this->display(__FILE__, 'fieldnewproductslider_vertical.tpl');
                else
                    return $this->display(__FILE__, 'fieldnewproductslider.tpl');
	}
	/* ------------------------------------------------------------- */
	/*  hookDisplayHome
	/* ------------------------------------------------------------- */
	public function hookDisplayHome($params){
		return $this->hookNewproductslider($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookRightColumn
	/* ------------------------------------------------------------- */
	public function hookRightColumn($params)
	{
		return $this->hookNewproductslider($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookLeftColumn
	/* ------------------------------------------------------------- */
	public function hookLeftColumn($params)
	{
		return $this->hookNewproductslider($params);
	}
	/* ------------------------------------------------------------- */
	/*  hookDisplayHeader
	/* ------------------------------------------------------------- */
	public function hookDisplayHeader($params){
		$this->context->controller->addCSS($this->_path . 'views/css/hook/fieldnewproductslider.css');
	}

	public function displayForm()
	{
                $id_default_lang = $this->context->language->id;
                $languages = $this->context->language->getLanguages();
                
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
                                                'type' => 'text',
                                                'name' => 'FIELD_NEWPSL_TITLE',
                                                'label' => $this->l('Title'),
                                                'desc' => $this->l('This title will appear just before the bestseller block, leave it empty to hide it completely'),
                                                'required' => false,
                                                'lang' => true,
                                            ),
					array(
						'type' => 'text',
						'label' => $this->l('Products to display'),
						'name' => 'FIELD_NEWPSL_NBR',
						'desc' => $this->l('Total number of product to display in this block'),
						'class' => 'fixed-width-xs',
					),
                                        array(
						'type'  => 'text',
						'label' => $this->l('Number of days for which the product is considered \'new\''),
						'name'  => 'PS_NB_DAYS_NEW_PRODUCT',
						'class' => 'fixed-width-xs',
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Always display this block'),
						'name' => 'FIELD_NEWPSL_DISPLAY',
						'desc' => $this->l('Show the block even if no best sellers are available.'),
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
					),array(
                                            'type' => 'switch',
                                            'label' => $this->l('Enable vertical mode'),
                                            'desc' => $this->l('Vertical slider'),
                                            'name' => 'FIELD_NEWPSL_VERTICAL',
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
                                                'name' => 'FIELD_NEWPSL_COLUMNITEM',
                                                'label' => $this->l('Item to be displayed'),
                                                'desc' => $this->l('The item number is showing in the column on vertical mode'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                        ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_NEWPSL_MAXITEM',
                                                'label' => $this->l('Max item'),
                                                'desc' => $this->l('The item number is showing on desstop screen'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                            ),
                                        array(
                                                'type' => 'text',
                                                'name' => 'FIELD_NEWPSL_MEDIUMITEM',
                                                'label' => $this->l('Medium item'),
                                                'desc' => $this->l('The item number is showing on tablet'),
                                                'suffix' => 'item',
                                                'class' => 'fixed-width-xs',
                                                'required' => false,
                                                'lang' => false,
                                            ),
                                            array(
                                                'type' => 'text',
                                                'name' => 'FIELD_NEWPSL_MINITEM',
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
                                                'name' => 'FIELD_NEWPSL_AUTOSCROLL',
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
                                                'name' => 'FIELD_NEWPSL_AUTOSCROLLDELAY',
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
                                                'name' => 'FIELD_NEWPSL_PAUSEONHOVER',
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
                                                'name' => 'FIELD_NEWPSL_PAGINATION',
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
                                                'name' => 'FIELD_NEWPSL_NAVIGATION',
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
                $helper->submit_action = 'submitFieldNewProductSlider';

                foreach($languages as $language){
                    $helper->languages[] = array(
                        'id_lang' => $language['id_lang'],
                        'iso_code' => $language['iso_code'],
                        'name' => $language['name'],
                        'is_default' => ($id_default_lang == $language['id_lang'] ? 1 : 0)
                    );
                }

                // Load current values
                $helper->fields_value['FIELD_NEWPSL_VERTICAL'] = Configuration::get('FIELD_NEWPSL_VERTICAL');
                $helper->fields_value['FIELD_NEWPSL_COLUMNITEM'] = Configuration::get('FIELD_NEWPSL_COLUMNITEM');
                $helper->fields_value['FIELD_NEWPSL_MAXITEM'] = Configuration::get('FIELD_NEWPSL_MAXITEM');
                $helper->fields_value['FIELD_NEWPSL_MEDIUMITEM'] = Configuration::get('FIELD_NEWPSL_MEDIUMITEM');
                $helper->fields_value['FIELD_NEWPSL_MINITEM'] = Configuration::get('FIELD_NEWPSL_MINITEM');
                $helper->fields_value['FIELD_NEWPSL_AUTOSCROLL'] = Configuration::get('FIELD_NEWPSL_AUTOSCROLL');
                $helper->fields_value['FIELD_NEWPSL_AUTOSCROLLDELAY'] = Configuration::get('FIELD_NEWPSL_AUTOSCROLLDELAY');
                $helper->fields_value['FIELD_NEWPSL_PAUSEONHOVER'] = Configuration::get('FIELD_NEWPSL_PAUSEONHOVER');
                $helper->fields_value['FIELD_NEWPSL_PAGINATION'] = Configuration::get('FIELD_NEWPSL_PAGINATION');
                $helper->fields_value['FIELD_NEWPSL_NAVIGATION'] = Configuration::get('FIELD_NEWPSL_NAVIGATION');
                $helper->fields_value['FIELD_NEWPSL_NBR'] = Configuration::get('FIELD_NEWPSL_NBR');
                $helper->fields_value['FIELD_NEWPSL_DISPLAY'] = Configuration::get('FIELD_NEWPSL_DISPLAY');
                $helper->fields_value['PS_NB_DAYS_NEW_PRODUCT'] = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');

                foreach($languages as $language){
                    $helper->fields_value['FIELD_NEWPSL_TITLE'][$language['id_lang']] = Configuration::get('FIELD_NEWPSL_TITLE', $language['id_lang']);
                }

		return $helper->generateForm(array($fields_form));
	}

	/* ------------------------------------------------------------- */
        /*  PREPARE FOR HOOK
        /* ------------------------------------------------------------- */

        private function _prepHook($params)
        {
            $id_default_lang = $this->context->language->id;

            $fieldnewpsl = array(
                'FIELD_NEWPSL_TITLE' => Configuration::get('FIELD_NEWPSL_TITLE', $id_default_lang),
                'FIELD_NEWPSL_COLUMNITEM' => Configuration::get('FIELD_NEWPSL_COLUMNITEM'),
                'FIELD_NEWPSL_MAXITEM' => Configuration::get('FIELD_NEWPSL_MAXITEM'),
                'FIELD_NEWPSL_MEDIUMITEM' => Configuration::get('FIELD_NEWPSL_MEDIUMITEM'),
                'FIELD_NEWPSL_MINITEM' => Configuration::get('FIELD_NEWPSL_MINITEM'),
                'FIELD_NEWPSL_AUTOSCROLL' => Configuration::get('FIELD_NEWPSL_AUTOSCROLL'),
                'FIELD_NEWPSL_AUTOSCROLLDELAY' => Configuration::get('FIELD_NEWPSL_AUTOSCROLLDELAY'),
                'FIELD_NEWPSL_PAUSEONHOVER' => Configuration::get('FIELD_NEWPSL_PAUSEONHOVER'),
                'FIELD_NEWPSL_PAGINATION' => Configuration::get('FIELD_NEWPSL_PAGINATION'),
                'FIELD_NEWPSL_NAVIGATION' => Configuration::get('FIELD_NEWPSL_NAVIGATION'),
                'FIELD_NEWPSL_NBR' => Configuration::get('FIELD_NEWPSL_NBR'),
                'FIELD_NEWPSL_DISPLAY' => Configuration::get('FIELD_NEWPSL_DISPLAY')
            );

            $this->smarty->assign('fieldnewpsl', $fieldnewpsl);
        }
}
