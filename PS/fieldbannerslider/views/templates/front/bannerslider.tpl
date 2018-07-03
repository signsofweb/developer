    <div class="field-slideshow-container">
	<div class="flexslider field-nivoslider">
        <div class="field-loading"></div>
            <div id="field-slideshow-home" class="slides">
                {$count=0}
                {foreach from=$slides key=key item=slide}
                    {if $slide.link}
                            <a href="{$slide.link}"><img style ="display:none" src="{$slide.image}" alt="" title="#htmlcaption{$slide.id_fieldbannerslider}"  /></a>
                    {else}
                            <img style ="display:none" src="{$slide.image}" alt="" title="#htmlcaption{$slide.id_fieldbannerslider}"  />
                    {/if}
                {/foreach}
            </div>
            {if $slideOptions.show_caption != 0}
                {foreach from=$slides key=key item=slide}
                    <div id="htmlcaption{$slide.id_fieldbannerslider}" class="field-slideshow-caption nivo-html-caption nivo-caption">
                            {if $slide.title}
                            <div class="field-slideshow-title">
                                   <h3>{$slide.title}</h3>
                            </div>
                            {/if}
                            {if $slide.description}
                            <div class="field-slideshow-des">
                                    <strong>{$slide.description}</strong>
                            </div>
                            {/if}
                            {if $slide.link}
                            <div class="field-slideshow-readmore">
                                <a href="{$slide.link}">{l s=('Read more') mod= 'fieldslideshow'}</a>	
                            </div>
                            {/if}
                    </div>
                 {/foreach}
             {/if}
        </div>
    </div>

 <script type="text/javascript">
    $(window).load(function() {
        $('#field-slideshow-home').nivoSlider({
			effect: '{if $slideOptions.animation_type != ''}{$slideOptions.animation_type}{else}random{/if}',
			slices: 15,
			boxCols: 8,
			boxRows: 4,
			animSpeed: '{if $slideOptions.animation_speed != ''}{$slideOptions.animation_speed}{else}600{/if}',
			pauseTime: '{if $slideOptions.pause_time != ''}{$slideOptions.pause_time}{else}5000{/if}',
			startSlide: {if $slideOptions.start_slide != ''}{$slideOptions.start_slide}{else}0{/if},
			directionNav: {if $slideOptions.show_arrow != 0}{$slideOptions.show_arrow}{else}false{/if},
			controlNav: {if $slideOptions.show_navigation != 0}{$slideOptions.show_navigation}{else}false{/if},
			controlNavThumbs: false,
			pauseOnHover: true,
			manualAdvance: false,
			prevText: 'Prev',
			nextText: 'Next',
                        afterLoad: function(){
                         $('.field-loading').css("display","none");
                        },     
                        beforeChange: function(){ 
                            $('.field-slideshow-title, .field-slideshow-des').css("left","-100%" );
                            $('.field-slideshow-readmore').css("left","-100%"); 
                        },
                        afterChange: function(){ 
                            $('.field-slideshow-title, .field-slideshow-des, .field-slideshow-readmore').css("left","0") 
                        }
 		});
    });
    </script>
