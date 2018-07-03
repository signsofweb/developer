<!-- MODULE Featured Products Products -->
<div id="featured_products_block" class="block vertical_mode col-sm-4 col-xs-12 clearfix">
	{if isset($fieldfeaturedpsl.FIELD_FEATUREDPSL_TITLE) && $fieldfeaturedpsl.FIELD_FEATUREDPSL_TITLE != ""}
            <h4 class="title_block title_font">
                <span class="title_text">{$fieldfeaturedpsl.FIELD_FEATUREDPSL_TITLE}</span>
            </h4>
        {/if}
          
	{if isset($products) AND $products}
		    <div class="row">
		<div id="featured_products" class="carousel-grid owl-carousel">
        {assign var="i" value="0"}
        {if isset($fieldfeaturedpsl.FIELD_FEATUREDPSL_COLUMNITEM) && $fieldfeaturedpsl.FIELD_FEATUREDPSL_COLUMNITEM}{assign var="y" value=$fieldfeaturedpsl.FIELD_FEATUREDPSL_COLUMNITEM}{else}{assign var="y" value=3}{/if}
      {foreach from=$products item='product' name='fieldFeaturedProductSlider'}
           {if $i mod $y eq 0}         
           <div class="item">
           {/if}
<div class="item-inner">
                 <div class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
                 <div class="left-product">
                      <a href="{$product.url}" class="thumbnail product-thumbnail">
                      	<span class="cover_image">
                            <img
                              src = "{$product.cover.bySize.small_default.url}"
                              data-full-size-image-url = "{$product.cover.large.url}"
                              alt=""
                              {if isset($size_small_default.width)}width="{$size_small_default.width}"{/if}
                              {if isset($size_small_default.height)}height="{$size_small_default.height}"{/if}
                            >
                        </span>             
                      </a>  
                    </div>  
                    <div class="right-product">       
                        <div class="product-description">
                            <div class="product_name"><a href="{$product.url}">{$product.name|truncate:30:'...'}</a></div>          
                            {if $product.show_price}
                              <div class="product-price-and-shipping">
                                {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                <span class="price">{$product.price}</span>
                    			{if $product.has_discount}
                                  {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                  <span class="regular-price">{$product.regular_price}</span>
                                {/if}
                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                    
                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                              </div>
                            {/if}
                        </div>
                        <form action="{$urls.pages.cart}" method="post">
                            <input type="hidden" name="token" value="{$static_token}">
                            <input type="hidden" name="id_product" value="{$product.id}">
                              <button class="add-to-cart" data-button-action="add-to-cart" type="submit" {if !$product.quantity}disabled{/if}>
                              {if $product.quantity}
                                {l s='Add to cart' mod='fieldfeaturedproductslider'}
                              {else}
                                {l s='Out of stock' mod='fieldfeaturedproductslider'}
                              {/if}  
                              </button>
                        </form>
                    </div>
                </div>
                
           </div>
           {assign var="i" value="`$i+1`"}
                {if $i mod $y eq 0 || $i eq count($products)}
                   </div>
                {/if}
        {/foreach}
		</div></div>
	{else}
		<p>{l s='No products at this time.' mod='fieldfeaturedproductslider'}</p>
	{/if}
</div>  

<script type="text/javascript">
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_MAXITEM)}
	var fieldfeaturedpsl_maxitem = {$fieldfeaturedpsl.FIELD_FEATUREDPSL_MAXITEM};
	{else}
	var fieldfeaturedpsl_maxitem=3;
{/if}	
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_MEDIUMITEM)}
	var fieldfeaturedpsl_mediumitem={$fieldfeaturedpsl.FIELD_FEATUREDPSL_MEDIUMITEM};
	{else}
	var fieldfeaturedpsl_mediumitem=2;
{/if}
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_MINITEM)}
	var fieldfeaturedpsl_minitem={$fieldfeaturedpsl.FIELD_FEATUREDPSL_MINITEM}
	{else}
	var fieldfeaturedpsl_minitem=1;
{/if}
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_AUTOSCROLL)}
	var fieldfeaturedpsl_autoscroll={$fieldfeaturedpsl.FIELD_FEATUREDPSL_AUTOSCROLLDELAY}
	{else}
	var fieldfeaturedpsl_autoscroll=false;
{/if}
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_PAUSEONHOVER)}
	var fieldfeaturedpsl_pauseonhover=true;
	{else}
	var fieldfeaturedpsl_pauseonhover=false;
{/if}
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_PAGINATION)}
	var fieldfeaturedpsl_pagination=true;
	{else}
	var fieldfeaturedpsl_pagination=false;
{/if}
{if ($fieldfeaturedpsl.FIELD_FEATUREDPSL_NAVIGATION)}
	var fieldfeaturedpsl_navigation=true;
	{else}
	var fieldfeaturedpsl_navigation=false;
{/if}


$(document).ready(function() {
    $('#featured_products').owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [480, fieldfeaturedpsl_minitem], [568, fieldfeaturedpsl_mediumitem], [992, fieldfeaturedpsl_maxitem], [1200, fieldfeaturedpsl_maxitem] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: fieldfeaturedpsl_autoscroll,
        stopOnHover: fieldfeaturedpsl_pauseonhover,
        rewindNav: true,
        pagination: fieldfeaturedpsl_pagination,
        navigation: fieldfeaturedpsl_navigation,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
    });
});
</script>
<!-- /MODULE Featured Products -->
