<!-- MODULE Featured Products Products -->
<div class="block special_block_right bd_block vertical_mode clearfix">
  {if isset($fieldspecialpsl.FIELD_SPECIALPLS_TITLE) && $fieldspecialpsl.FIELD_SPECIALPLS_TITLE != ""}
            <h4 class="bd_title_block">
                <span class="bd_title_text">{l s="Viewed Pro"}</span>
            </h4>
        {/if}
          
  {if isset($specials) AND $specials}
        <div class="row">
    <div class="special_products carousel-grid owl-carousel">
        {assign var="i" value="0"}
     {assign var="y" value="3"}
      {foreach from=$specials item='product'}
           {if $i mod $y eq 0}         
           <div class="item">
           {/if}
<div class="item-inner">
  <div class="item-inner-border">
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
                    </div>
                </div>
                </div>
           </div>
           {assign var="i" value="`$i+1`"}
                {if $i mod $y eq 0 || $i eq count($specials)}
                   </div>
                {/if}
        {/foreach}
    </div></div>
  {else}
    <p>{l s='No products at this time.' mod='fieldspecialproduct'}</p>
  {/if}
</div>  








<script type="text/javascript">
{if ($fieldspecialpsl.FIELD_SPECIALPLS_MAXITEM)}
  var fieldspecialpsl_maxitem = {$fieldspecialpsl.FIELD_SPECIALPLS_MAXITEM};
  {else}
  var fieldspecialpsl_maxitem=3;
{/if} 
{if ($fieldspecialpsl.FIELD_SPECIALPLS_MEDIUMITEM)}
  var fieldspecialpsl_mediumitem={$fieldspecialpsl.FIELD_SPECIALPLS_MEDIUMITEM};
  {else}
  var fieldspecialpsl_mediumitem=2;
{/if}
{if ($fieldspecialpsl.FIELD_SPECIALPLS_MINITEM)}
  var fieldspecialpsl_minitem={$fieldspecialpsl.FIELD_SPECIALPLS_MINITEM}
  {else}
  var fieldspecialpsl_minitem=1;
{/if}
{if ($fieldspecialpsl.FIELD_SPECIALPLS_AUTOSCROLL)}
  var fieldspecialpsl_autoscroll={$fieldspecialpsl.FIELD_SPECIALPLS_AUTOSCROLLDELAY}
  {else}
  var fieldspecialpsl_autoscroll=false;
{/if}
{if ($fieldspecialpsl.FIELD_SPECIALPLS_PAUSEONHOVER)}
  var fieldspecialpsl_pauseonhover=true;
  {else}
  var fieldspecialpsl_pauseonhover=false;
{/if}
{if ($fieldspecialpsl.FIELD_SPECIALPLS_PAGINATION)}
  var fieldspecialpsl_pagination=true;
  {else}
  var fieldspecialpsl_pagination=false;
{/if}
{if ($fieldspecialpsl.FIELD_SPECIALPLS_NAVIGATION)}
  var fieldspecialpsl_navigation=true;
  {else}
  var fieldspecialpsl_navigation=false;
{/if}

$(window).load(function() {
    $('.special_block_right .special_products').each(function(){
      $(this).owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [480, 1], [768, 1], [992, 1], [1200, 1] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: fieldspecialpsl_autoscroll,
        stopOnHover: fieldspecialpsl_pauseonhover,
        rewindNav: true,
        pagination: false,
        navigation: true,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
      });
    });
});

</script>
<!-- /MODULE Featured Products -->