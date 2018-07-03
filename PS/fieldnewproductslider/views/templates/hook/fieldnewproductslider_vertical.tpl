<!-- MODULE News Products Products -->
<div id="field_newproductslider_block" class="block vertical_mode col-sm-4 col-xs-12 clearfix">
	{if isset($fieldnewpsl.FIELD_NEWPSL_TITLE) && $fieldnewpsl.FIELD_NEWPSL_TITLE != ""}
            <h4 class="title_block title_font">
                <a href="{$link->getPageLink('new-products')|escape:'html'}" title="{$fieldnewpsl.FIELD_NEWPSL_TITLE}">
                    {$fieldnewpsl.FIELD_NEWPSL_TITLE}
                </a>
            </h4>
        {/if}
          
	{if isset($new_products) AND $new_products}
		    <div class="row">
		<div id="new_products" class="carousel-grid owl-carousel">
        {assign var="i" value="0"}
        {if isset($fieldnewpsl.FIELD_NEWPSL_COLUMNITEM) && $fieldnewpsl.FIELD_NEWPSL_COLUMNITEM}{assign var="y" value=$fieldnewpsl.FIELD_NEWPSL_COLUMNITEM}{else}{assign var="y" value=1}{/if}<!--Number Row-->
      {foreach from=$new_products item='product'}
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
                                {l s='Add to cart' mod='fieldnewproductslider'}
                              {else}
                                {l s='Out of stock' mod='fieldnewproductslider'}
                              {/if}  
                              </button>
                        </form>
                    </div>
                </div>
                
           </div>
           {assign var="i" value="`$i+1`"}
                {if $i mod $y eq 0 || $i eq count($new_products)}
                   </div>
                {/if}
        {/foreach}
		</div></div>
	{else}
		<p>{l s='No products at this time.' mod='fieldfeaturedproductslider'}</p>
	{/if}
</div>  








<script type="text/javascript">
{if ($fieldnewpsl.FIELD_NEWPSL_MAXITEM)}
	var fieldnewpsl_maxitem = {$fieldnewpsl.FIELD_NEWPSL_MAXITEM};
	{else}
	var fieldnewpsl_maxitem=3;
{/if}	
{if ($fieldnewpsl.FIELD_NEWPSL_MEDIUMITEM)}
	var fieldnewpsl_mediumitem={$fieldnewpsl.FIELD_NEWPSL_MEDIUMITEM};
	{else}
	var fieldnewpsl_mediumitem=2;
{/if}
{if ($fieldnewpsl.FIELD_NEWPSL_MINITEM)}
	var fieldnewpsl_minitem={$fieldnewpsl.FIELD_NEWPSL_MINITEM}
	{else}
	var fieldnewpsl_minitem=1;
{/if}
{if ($fieldnewpsl.FIELD_NEWPSL_AUTOSCROLL)}
	var fieldnewpsl_autoscroll={$fieldnewpsl.FIELD_NEWPSL_AUTOSCROLLDELAY}
	{else}
	var fieldnewpsl_autoscroll=false;
{/if}
{if ($fieldnewpsl.FIELD_NEWPSL_PAUSEONHOVER)}
	var fieldnewpsl_pauseonhover=true;
	{else}
	var fieldnewpsl_pauseonhover=false;
{/if}
{if ($fieldnewpsl.FIELD_NEWPSL_PAGINATION)}
	var fieldnewpsl_pagination=true;
	{else}
	var fieldnewpsl_pagination=false;
{/if}
{if ($fieldnewpsl.FIELD_NEWPSL_NAVIGATION)}
	var fieldnewpsl_navigation=true;
	{else}
	var fieldnewpsl_navigation=false;
{/if}


$(window).load(function() {
    $('#new_products').owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [480, fieldnewpsl_minitem], [768, fieldnewpsl_mediumitem], [992, fieldnewpsl_maxitem], [1200, fieldnewpsl_maxitem] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: fieldnewpsl_autoscroll,
        stopOnHover: fieldnewpsl_pauseonhover,
        rewindNav: true,
        pagination: fieldnewpsl_pagination,
        navigation: fieldnewpsl_navigation,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
    });
});
</script>
<!-- /MODULE News Products -->