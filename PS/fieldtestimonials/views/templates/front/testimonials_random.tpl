<div id="bd_testimonial" class="block">
       <h4 class="bd_title_block">
              <span class="bd_title_text">{l s='Testimonials'}</span>
        </h4>
        {if $testimonials}
        <div class="bd_border_carousel">
          <div id="bd_testimonial_slider">
                {foreach from=$testimonials key=test item=testimonial}
                  <div class="bd_items">
                       <div class="bd_des_test">{$testimonial.content nofilter}</div>
                      <div class="bd_test_bottom">
                          {if $testimonial.media}
                        {if in_array($testimonial.media_type,$conf_testimonials.arr_img_type)}
                        <div class="test_img"><img class="img-responsive" src="{$conf_testimonials.mediaUrl}{$testimonial.media}" alt="Image Testimonial"/></div>
                        {/if}
                        {/if}
                         <div class="bd_content_test">
                            <p class="bd_des_namepost">{$testimonial.name_post}</p>
                            <p class="bd_des_company">{$testimonial.company}</p>
                          </div>
                      </div>
                  </div>

                {/foreach}
          </div>
        </div>
        {/if}
</div>
<script type="text/javascript">
$(window).load(function() {
    $('#bd_testimonial_slider').owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [400, 1], [768, 1], [1200, 1] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: false,
        stopOnHover:  false,
        rewindNav: true,
        pagination: false,
        navigation: true,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
    });
});
</script>