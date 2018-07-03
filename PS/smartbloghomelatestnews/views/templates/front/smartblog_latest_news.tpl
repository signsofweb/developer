
<div class="bd_smart-blog-home-post block bd_block clearfix">
        <h4 class="bd_title_block">
            <span class="bd_title_text">{l s='From the blog' mod='smartbloghomelatestnews'}</span>
        </h4>
	    <div id="smart-blog-custom" class="sdsblog-box-content grid carousel-grid owl-carousel">
		{if isset($view_data) AND !empty($view_data)}
                    {assign var="i" value="0"}
                    {assign var="y" value=1} {*the number item of one row*}
		    {foreach from=$view_data item=post}
			    {assign var="options" value=null}
			    {$options.id_post = $post.id}
			    {$options.slug = $post.link_rewrite}
                            {if $i mod $y eq 0}
			    <div class="item sds_blog_post">
                            {/if}
                    <div class="item-inner">
                    <div class="news_module_image_holder">
                        <div class="inline-block_relative">
                        <div class="image_holder_wrap">
                            <a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">
                            <img alt="{$post.title}" class="img-responsive" src="{$link->getMediaLink($smarty.const._MODULE_DIR_)}smartblog/images/{$post.post_img}-home-default.jpg"
                             {if isset($size_home_default_post.width)}width="{$size_home_default_post.width}"{/if}
                             {if isset($size_home_default_post.height)}height="{$size_home_default_post.height}"{/if}
                            >
                            </a>
                        </div>

                        <div class="right_blog_home">
                            <div class="border_content">
                            <h3 class="bd_title_post"><a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">{$post.title}</a></h3>
                             <div class="bd_date_post">
                                    <i class="fa fa-calendar"></i><span>{$post.date_added|date_format:" %b %e %Y"}</span>
                            </div>
                            </div>

                        </div>
                        </div>
                    </div>
                    </div>
                            {assign var="i" value="`$i+1`"}
                            {if $i mod $y eq 0 || $i eq count($view_data)}
			    </div>
                            {/if}
		    {/foreach}
		{/if}
	     </div>
</div>