{if $fieldbrandslider.manufacturers}
    <div id="bd_fieldbrandslider" class="bd_block">
            <h4 class="bd_title_block"> 
              <a class="bd_title_text" href="{$link->getPageLink('manufacturer')|escape:'html'}">Top Brands</a>
            </h4>
        <div class="row">
                <div id="fieldbrandslider-manu" class="grid carousel-grid owl-carousel">
                        {assign var="e" value="0"}
                        {assign var="r" value=3} {*the number item of one row*}
                    {foreach $fieldbrandslider.manufacturers as $manufacturer}
                        {if $e mod $r eq 0}
                        <div class="bd_items">
                          {/if}
                       
                        <div class="item">
                           
                           <a class="img-wrapper" href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$manufacturer.name|escape:'htmlall':'UTF-8'}">
                                <img class="img-responsive" 
                                src="{$link->getMediaLink($manu_dir)}{$manufacturer.id_manufacturer|escape:'htmlall':'UTF-8'}-field_manufacture.jpg"
                                 alt="{$manufacturer.name|escape:'htmlall':'UTF-8'}" 
                               {if isset($size_field_manufacture.width)}width="{$size_field_manufacture.width}"{/if}
                              {if isset($size_field_manufacture.height)}height="{$size_field_manufacture.height}"{/if}  
                                 />
                                {if isset($fieldbrandslider.mantitle) AND $fieldbrandslider.mantitle == 1}<br/>
                                    <p>{$manufacturer.name|escape:'htmlall':'UTF-8'}</p>
                                {/if}
                            </a>
                           </div>
                             {assign var="e" value="`$e+1`"}
                            {if $e mod $r eq 0 || $e eq count($fieldbrandslider.manufacturers)}
                        </div>
                          {/if}
                    {/foreach}
                </div>
        </div>
    </div>
{/if}    
