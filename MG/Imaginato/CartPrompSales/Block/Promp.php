<?php

class Imaginato_CartPrompSales_Block_Promp extends Mage_Catalog_Block_Product_Abstract
{

    protected $_template = 'imaginato/cartprompsales/promp.phtml';
    /**
     * Price template
     *
     * @var string
     */
    protected $_priceBlockDefaultTemplate = 'imaginato/cartprompsales/product/price.phtml';
    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;

    protected $_productConfig;

    protected $_addCartUrlCollection;
    protected $_parentProductCollection;

    public function _construct()
    {
        if(!Mage::getStoreConfig('cartprompsales/generl/active')){
            $this->setTemplate('');
        }
        if(!$this->getProductCollection()){
            $this->setTemplate('');
        }
        if(Mage::getStoreConfig('cartprompsales/generl/check_to_cart')){
            $checkSubtotal = Mage::getStoreConfig('cartprompsales/generl/check_to_cart_total');
            $quoteSubtotal = Mage::getModel('cartprompsales/observer')->getQuoteSubtotal(Mage::getSingleton('checkout/cart')->getQuote());
            if(doubleval($checkSubtotal)>$quoteSubtotal){
                $this->setTemplate('');
            }
        }
        if(Mage::getStoreConfig('cartprompsales/generl/check_to_add') && $this->hasAddCart()){
            $this->setTemplate('');
        }
        return parent::_construct();
    }

    public function getTitle(){
        $text_block_id = Mage::getStoreConfig('cartprompsales/generl/text_block_id');
        if(empty($text_block_id)){
            return '';
        }
        return $this->getLayout()->createBlock('cms/block')->setBlockId($text_block_id)->toHtml();
    }

    public function showPrice(){
        return Mage::getStoreConfig('cartprompsales/generl/price_show');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $store_id = Mage::app()->getStore()->getId();
            $productConfig = $this->getProductConfig();
            if(empty($productConfig)){
                $this->_productCollection = '';
                return '';
            }
			$products = Mage::getModel('cartprompsales/product')->getProductsPosition($store_id);
            if(empty($products)){
                $store_id = 0;
            }
            $productCollection = Mage::getModel('catalog/product')->getCollection();

            $productCollection
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents();

            $productCollection->joinField('position',
                'cartprompsales/product',
                'position',
                'product_id=entity_id',
                'store_id='.(int) $store_id,
                'inner');
            $productCollection->setOrder('position', 'asc');

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);


            // get simple product from configurable product
            $allProductCollection = array();
            $colorAttributeId = Mage::getModel('catalog/product')->getResource()->getAttribute('color_family')->getAttributeId();
            $sizeAttributeId = Mage::getModel('catalog/product')->getResource()->getAttribute('size')->getAttributeId();
            foreach($productCollection as $product){
                if(!isset($allProductCollection[$product->getId()])){
                    $allProductCollection[$product->getId()] = $product;
                }
            }

            $this->_productCollection = $allProductCollection;
        }

        return $this->_productCollection;
    }

    public function getProductConfig(){
        if(!isset($this->_productConfig)){
            $store_id = Mage::app()->getStore()->getId();
            $products = Mage::getModel('cartprompsales/product')->getProductsPosition($store_id);
            if(empty($products)){
                $products = Mage::getModel('cartprompsales/product')->getProductsPosition(0);
            }

            $this->_productConfig = $products;
        }
        return $this->_productConfig;
    }

    public function getParentProduct($product){
        if(isset($this->_parentProductCollection[$product->getId()])){
            return $this->_parentProductCollection[$product->getId()];
        }
        return $product;
    }

    public function getOptions($product){
        $option = array();
        if(!empty($product->getAttributeText('color_family'))){
            $option[] = $product->getAttributeText('color_family');
        }
        if(!empty($product->getAttributeText('size'))){
            $option[] = $product->getAttributeText('size');
        }
        return implode(', ',$option);
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if(isset($this->_addCartUrlCollection[$product->getId()])){
            return $this->_addCartUrlCollection[$product->getId()];
        }
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

    public function hasAddCart(){
        $productConfig = $this->getProductConfig();
        $productIds = array_keys($productConfig);

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $itemCollection = $quote->getItemsCollection();
        $cartProductIds = $itemCollection->getColumnValues('product_id');
        return !empty(array_intersect($productIds,$cartProductIds));
    }
	public function getChildBlock($product)
	{
		$block = $this->getLayout()->createBlock('cartprompsales/catalog_product_view_type_configurable','promp.product.info.options.configurable',array('template' => 'imaginato/cartprompsales/catalog/product/view/type/options/configurable.phtml'));
		$block->setProduct($product);
		return $block;
	}
}
