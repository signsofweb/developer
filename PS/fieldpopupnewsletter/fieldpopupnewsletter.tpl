{if $field_ppp.FIELD_NEWSLETTER == 1}
<div id="moda_popupnewsletter" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document" style="{if $field_ppp.FIELD_WIDTH}max-width:{$field_ppp.FIELD_WIDTH}px;{/if}">
    <div class="modal-content">
    
<div class="fieldpopupnewsletter" style="{if $field_ppp.FIELD_WIDTH}width:{$field_ppp.FIELD_WIDTH}px;{/if}{if $field_ppp.FIELD_HEIGHT}height:{$field_ppp.FIELD_HEIGHT}px;{/if}{if $field_ppp.FIELD_BG == 1 && !empty($field_ppp.FIELD_BG_IMAGE)}background-image: url({$field_ppp.FIELD_BG_IMAGE});{/if}">
	
	<div id="newsletter_block_popup" class="block">
		<div class="bd_block_content">
		{if isset($msg) && $msg}
			<p class="{if $nw_error}warning_inline{else}success_inline{/if}">{$msg}</p>
		{/if}
			<form method="post">
                            {if $field_ppp.FIELD_TITLE}<div class="bd_popup_title">{$field_ppp.FIELD_TITLE|stripslashes nofilter}</div>{/if}
                            {if $field_ppp.FIELD_TEXT}<div class="bd_popup_text">{$field_ppp.FIELD_TEXT|stripslashes nofilter}</div>{/if}
                            <div class="send-response"></div>
                            <input class="inputNew" id="newsletter-input-popup" type="text" name="email" placeholder="{l s='Enter Email Address Here' mod='fieldpopupnewsletter'}"/>
                            <div class="send-reqest button_unique main_color_hover">{l s='Sent me a 50% coupon  now' mod='fieldpopupnewsletter'}</div>
                            <button type="button" class="bd_close" data-dismiss="modal" aria-label="Close"><span>{l s='No, thanks'}</span></button>
			</form>
		</div>
                <div class="newsletter_block_popup-bottom">
                    <input id="newsletter_popup_dont_show_again" type="checkbox">
                    <label for="newsletter_popup_dont_show_again">{l s='Don\'t show this popup again' mod='fieldpopupnewsletter'}</label>
                </div>
	</div>

</div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
var field_width={$field_ppp.FIELD_WIDTH};
var field_height={$field_ppp.FIELD_HEIGHT};
var field_newsletter={$field_ppp.FIELD_NEWSLETTER};
var field_path='{$field_ppp.FIELD_PATH}';
</script>
	{/if}