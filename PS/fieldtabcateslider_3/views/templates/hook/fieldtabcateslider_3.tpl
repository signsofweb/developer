{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Tab Cate Products Products -->
<script type="text/javascript">
$(document).ready(function() {
	$(".tab_category_slider_3").hide();
	$(".tab_category_slider_3:first").show(); 
	$(".tab-category-container-slider_3 ul.tab_cates li").click(function() {
		$(".tab-category-container-slider_3 ul.tab_cates li").removeClass("active");
		$(this).addClass("active");
		$(".tab_category_slider_3").hide();
		$(".tab_category_slider_3").removeClass("animate1");
		var activeTab = $(this).attr("rel"); 
		$("#"+activeTab).addClass("animate1");
		$("#"+activeTab).fadeIn(); 
	});
});

</script>
<script type="text/javascript">
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MAXITEM)}
	var fieldtabcatepsl_3_maxitem = {$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MAXITEM};
	{else}
	var fieldtabcatepsl_3_maxitem=3;
{/if}	
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MEDIUMITEM)}
	var fieldtabcatepsl_3_mediumitem={$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MEDIUMITEM};
	{else}
	var fieldtabcatepsl_3_mediumitem=2;
{/if}
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MINITEM)}
	var fieldtabcatepsl_3_minitem={$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_MINITEM}
	{else}
	var fieldtabcatepsl_3_minitem=1;
{/if}
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_AUTOSCROLL)}
	var fieldtabcatepsl_3_autoscroll={$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_AUTOSCROLLDELAY}
	{else}
	var fieldtabcatepsl_3_autoscroll=false;
{/if}
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_PAUSEONHOVER)}
	var fieldtabcatepsl_3_pauseonhover=true;
	{else}
	var fieldtabcatepsl_3_pauseonhover=false;
{/if}
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_PAGINATION)}
	var fieldtabcatepsl_3_pagination=true;
	{else}
	var fieldtabcatepsl_3_pagination=false;
{/if}
{if ($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_NAVIGATION)}
	var fieldtabcatepsl_3_navigation=true;
	{else}
	var fieldtabcatepsl_3_navigation=false;
{/if}
</script>

<div class="row">
<div class="col-xs-12 col-md-9">
<div class="tab-category-container-slider_3 horizontal_mode block">
    <div class="tab-category-slider_3">

    {if isset($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_TITLE) && $fieldtabcatepsl_3.FIELD_TABCATEPSL_3_TITLE != ""}
                <h4 class="title_block title_font" data-title="">
                    <span class="title_text"> {$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_TITLE}</span>
                   {if $productCates|count > 1}         
                   <ul class="tab_cates"> 
                        {$count=1}
                        {foreach from=$productCates item=productCate name=fieldTabCategorySlider_3}
                            <li rel="tab_{$productCate.id}" {if $count==1} class="active"  {/if} > {$productCate.name}</li>
                            {$count= $count+1}
                        {/foreach}	
                    </ul>
                    {/if}  
                </h4>
        {/if}    
        

        
        
        <div class="tab_cate_container">
            {foreach from=$productCates item=productCate name=fieldTabCategorySlider_3}
                <div id="tab_3_{$productCate.id}" class="tab_category_slider_3">
                    <div class="row1">
                        <div class="tabcate_content carousel-grid owl-carousel">
			    {assign var="i" value="0"}
			    {if isset($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_ROWITEM) && $fieldtabcatepsl_3.FIELD_TABCATEPSL_3_ROWITEM}{assign var="y" value=$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_ROWITEM}{else}{assign var="y" value=1}{/if}
                            {foreach from=$productCate.product item=product name=fieldTabCategorySlider_3}
				{if $i mod $y eq 0}
                                <div class="item">
				{/if}
            <div class="item-inner">
                 <div class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
                 <div class="left-product">
                      <a href="{$product.url}" class="thumbnail product-thumbnail">
                      	<span class="cover_image">
                            <img
                              src = "{$product.cover.bySize.home_default.url}"
                              data-full-size-image-url = "{$product.cover.large.url}" alt=""
                                {if isset($size_home_default.width)}width="{$size_home_default.width}"{/if}
                              {if isset($size_home_default.height)}height="{$size_home_default.height}"{/if} 
                            >
                        </span>
                        {if isset($product.images[1])}
                        <span class="hover_image">
                            <img 
                              src = "{$product.images[1].bySize.home_default.url}"
                              data-full-size-image-url = "{$product.images[1].bySize.home_default.url}" alt=""
                                   {if isset($size_home_default.width)}width="{$size_home_default.width}"{/if}
                              {if isset($size_home_default.height)}height="{$size_home_default.height}"{/if} 
                            > 
                        </span>
                        {/if}               
                      </a> 
                      <div class="conditions-box">
                            {if isset($product.show_condition) && $product.condition.type == "new" && $product.show_condition == 1  && isset($product.new) && $product.new == 1 }
                            <span class="new_product">{l s='New' mod='fieldfeaturedproductslider'}</span>
                            {/if}
                            {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price }
                            <span class="sale_product">{$product.discount_percentage}</span>
                            {/if}  
                            </div>                     
                        <form action="{$urls.pages.cart}" method="post">
                            <input type="hidden" name="token" value="{$static_token}">
                            <input type="hidden" name="id_product" value="{$product.id}">
                            <div class="button-action">
                              <button class="add-to-cart" data-button-action="add-to-cart" type="submit" {if !$product.quantity}disabled{/if}>
                              {if $product.quantity}
                                <i class="fa fa-shopping-cart"></i>
                              {else}
                                <i class="fa fa-ban"></i>
                              {/if}  
                              </button>
                              <a href="javascript:void(0)" class="quick-view" data-link-action="quickview" title="{l s='Quick view' mod='fieldfeaturedproductslider'}"> 
                                <i class="fa fa-eye"></i>
                              </a>
                             </div>
                        </form>
                    </div>  
                    <div class="right-product">       
                        <div class="product-description">  
                            <div class="product_name"><a href="{$product.url}">{$product.name|truncate:30:'...'}</a></div>   
                            {if $product.show_price}
                              <div class="product-price-and-shipping">
                              	  <div class="content-price">
                                    {if $product.has_discount}
                                      {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                      <span class="regular-price">{$product.regular_price}</span>
                                    {/if}
                                    <span class="price">{$product.price}</span>
                                  </div>
                                {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                
                    
                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                    
                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                              </div>
                            {/if}     
                        </div>
                    </div>
                </div>
                
           </div>
			{assign var="i" value="`$i+1`"}
			    {if $i mod $y eq 0 || $i eq count($productCate.product)}
				</div>
			    {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
                <script type="text/javascript"> 
				$(window).load(function() {
                    $('.tab-category-container-slider_3 #tab_3_{$productCate.id} .tabcate_content').owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [400, 2], [768, fieldtabcatepsl_3_mediumitem], [1200, fieldtabcatepsl_3_maxitem] ],
                        responsiveRefreshRate: 50,
                        slideSpeed: 200,
                        paginationSpeed: 500,
                        rewindSpeed: 600,
                        autoPlay: fieldtabcatepsl_3_autoscroll,
                        stopOnHover: fieldtabcatepsl_3_pauseonhover,
                        rewindNav: true,
                        pagination: fieldtabcatepsl_3_pagination,
                        navigation: fieldtabcatepsl_3_navigation,
                        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
                    });
				     });	
                </script>
            {/foreach}	
        </div>
    </div>
	</div>

<!-- /MODULE Tab Cate Products Products -->
</div>
<div class="col-xs-12 col-md-3">
<!-- /MODULE Tab Cate Products Products -->
                {if isset($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_BANNER) && $fieldtabcatepsl_3.FIELD_TABCATEPSL_3_BANNER != ""}
                    <div class="image_product block">
                        {if isset($fieldtabcatepsl_3.FIELD_TABCATEPSL_3_LINKS) && $fieldtabcatepsl_3.FIELD_TABCATEPSL_3_LINKS != ""}
                            <a href="{$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_LINKS|escape:'html'}"><img class="img-responsive" alt="" src="{$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_BANNER}"></a>
                        {else}
                           <a href="#"><img class="img-responsive" alt="" src="{$fieldtabcatepsl_3.FIELD_TABCATEPSL_3_BANNER}"></a>
                        {/if}
                    </div>
                 {/if}
</div>
</div>
