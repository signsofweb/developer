<div class="block block_testimonials" {if $conf_testimonials.show_background && $background != ""}style="background: url({$link->getMediaLink("`$module_dir`$background")}) center center no-repeat;"{/if}>
    <div class="overlay_testimonials">
        <div class="container">
<div id="testimonials_block_right">
  <script type="text/javascript">
    $(document).ready(function(){
      $('#slide').cycle({
        fx:    'fade',
        speed:  1000,
        timeout: 6000,
        next:  '.next',
        prev:  '.prev',
		pager:'#pagination_cycle'
      });
      $('#media_post').fancybox();
      $('.fancybox-media')
        .attr('rel', 'media-gallery')
        .fancybox({
          openEffect : 'none',
          closeEffect : 'none',
          prevEffect : 'none',
          nextEffect : 'none',
          arrows : false,
          helpers : {
            media : {},
            buttons : {}
          }
        });
        if($(window).width()< 768){
            $('#left_column #wrapper').css({ 'display':'none' });
            $('#right_column #wrapper').css({ 'display':'none' });
        }
    });
  </script>
  <div id="wrapper_testimonials" class="block_content">
    {if $testimonials}
    <div class="bx-controls-direction">
            <a class="prev bx-prev" href=""></a>
            <a class="next bx-next" href=""></a>
        </div>
      <div id="slide-panel">
        <div id="slide">
          {foreach from=$testimonials key=test item=testimonial}
            {if $testimonial.active == 1}
              <div class="main-block">

		{if $conf_testimonials.show_info}
		    <div class="media">
            <div class="content_test">
			  <p class="des_namepost title_font">{$testimonial.name_post}</p>
			  <p class="des_company">{$testimonial.company}</p>
			</div>
			<div class="media-content">
			  {if $testimonial.media}
			    {if in_array($testimonial.media_type,$conf_testimonials.arr_img_type)}
			      <span class="fancybox-media">
				<img class="img-responsive" src="{$conf_testimonials.mediaUrl}{$testimonial.media}" alt="Image Testimonial"/>
			      </span>
				{/if}
			  {/if}
			    {if in_array($testimonial.media_type,$conf_testimonials.video_types)}
				<video width="260" height="240" controls>
				    <source src="{$conf_testimonials.mediaUrl}{$testimonial.media}" type="video/mp4" />
				</video>
			    {/if}
			    {if $testimonial.media_type == 'youtube'}
			      <a class="fancybox-media" href="{$testimonial.media_link}"><img src="{$conf_testimonials.video_youtube}" alt="Youtube Video"/></a>
			    {elseif $testimonial.media_type == 'vimeo'}
			      <a class="fancybox-media" href="{$testimonial.media_link}"><img src="{$conf_testimonials.video_vimeo}" alt="Vimeo Video"/></a>
			    {/if}
			</div>
		    </div>
		{/if}
                        <div class="content_test_top">
                  <div class="des_testimonial">{$testimonial.content nofilter}{*<a href="{$link->getModuleLink('fieldtestimonials','views',['process'=>'view','id'=>{$testimonial.id_fieldtestimonials}])}" class="read_more">Read More</a>*}</div>
                </div>
                {if $conf_testimonials.show_button_link}
                	<a href="{$testimonial.button_link}" class="view_link">{l s='View link' mod='fieldtestimonials'}</a>
                {/if}
              </div>
            {/if}
          {/foreach}
        </div>
        
      </div>
    {/if}
    {if $conf_testimonials.show_submit}
    <div class="button_testimonial">
      <div class="view_all"><a href="{$link->getModuleLink('fieldtestimonials','views',['process' => 'view'])}">{l s='View All' mod='fieldtestimonials'}</a></div>
      <div class="submit_link"><a href="{$link->getModuleLink('fieldtestimonials','views',['process' => 'form_submit'])}">{l s='Submit Your Testimonial' mod='fieldtestimonials'}</a></div>
    </div>
    {/if}
                    </div>
</div>
        </div>
    </div>
</div>
