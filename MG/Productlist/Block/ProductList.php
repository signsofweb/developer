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
class ProductList extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
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
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
        ) {
    	$this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

	public function _construct(){
		parent::_construct();
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
        $template = '';
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
        $this->setTemplate($template);
        return parent::_toHtml();
    }


	/**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        //$url = str_replace("checkout/cart", "productlist/cart", $url);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    public function getFieldProductPriceHtml($product){
        return $this->getProductPrice($product);
    }
}