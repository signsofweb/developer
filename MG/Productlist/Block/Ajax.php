<?php
/**
 * Fieldthemes
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Fieldthemes.com license that is
 * available through the world-wide-web at this URL:
 * http://www.fieldthemes.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Fieldthemes
 * @package    Field_Productlist
 * @copyright  Copyright (c) 2014 Fieldthemes (http://www.fieldthemes.com/)
 * @license    http://www.fieldthemes.com/LICENSE-1.0.html
 */
namespace Field\Productlist\Block;

class Ajax extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context     
     * @param \Magento\Framework\Url\Helper\Data     $urlHelper   
     * @param array                                  $data        
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
        ) {
        parent::__construct($context, $data);
    }

	public function getConfig($key, $default = '')
	{
		if($this->hasData($key))
		{
			return $this->getData($key);
		}
		return $default;
	}

	public function _toHtml(){
		if($template = $this->getConfig('template')){
			$this->setTemplate($template);
		}else{
			$layout_type = $this->getConfig('layout_type');
			if($layout_type == 'owl_carousel'){
                $this->setTemplate('Field_Productlist::widget/owlcarousel/ajax.phtml');
            }
    		if($layout_type == 'bootstrap_carousel'){
                $this->setTemplate('Field_Productlist::widget/bootstrapcarousel/ajax.phtml');
            }
		}
		return parent::_toHtml();
	}
	
	public function getProductHtml($data){
		$template = 'Field_Productlist::widget/owlcarousel/items.phtml';
		
		if(isset($data['template'])) {
			$template = $data['template'];
		}elseif(isset($data['product_template'])){
			$template = $data['product_template'];
		}else{
			$layout_type = $this->getConfig('layout_type');
	        if($layout_type == 'owl_carousel'){
	            $template = 'Field_Productlist::widget/owlcarousel/items.phtml';
	        }
	        if($layout_type == 'bootstrap_carousel'){
	            $template = 'Field_Productlist::widget/bootstrapcarousel/items.phtml';
	        }
	        if($productTemplate = $this->getConfig('product_template')){
	            $template = $productTemplate;
	        }

	    }

	    unset($data['type']);
		unset($data['cache_lifetime']);
		unset($data['cache_tags']);

        $html = $this->getLayout()->createBlock('Field\Productlist\Block\ProductList')->setData($data)->setTemplate($template)->toHtml();
        return $html;
    }
}