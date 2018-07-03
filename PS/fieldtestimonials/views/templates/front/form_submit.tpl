<div id="testimonials_block_center" class="block" xmlns="http://www.w3.org/1999/html">
  <h4 class="title_block">{l s='Add new one testimonial' mod='fieldtestimonials'}</h4>
  {if $errors}
    <div class="alert alert-danger">
      {foreach from=$errors item=error}
        <p>{l s={$error} mod='fieldtestimonials'}</p></br>
      {/foreach}
    </div>
  {/if}
  {if $success}<div class="alert alert-success">{l s={$success} mod='fieldtestimonials'}</div>{/if}
   <div class="col-xs-6 col-md-12 form-submit">
    <form name="fr_testimonial" id="fr_testimonial" action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label  class="col-sm-4 control-label">{l s='Your Name' mod='fieldtestimonials'}:</label>
            <div class="col-sm-10">

                 <sup class="require">*</sup>
                </div>
            </div>
         <div class="form-group">
              <label  class="col-sm-4 control-label">{l s='Your Email' mod='fieldtestimonials'}:</label>
              <div class="col-sm-10">
                 <p class="form-group">

                    <sup class="require">*</sup>
                 </p>
            </div>
          </div>

      <div class="form-group">
         <label class="col-sm-4 control-label">{l s='Company' mod='fieldtestimonials'}:</label>
            <div class="col-sm-10">
            <input id="company" name="company" type="text" value="{if isset($company)}{$company|escape:'html':'UTF-8'}{/if}" size="40" class="form-control"/>
                <sup class="require"></sup>
            </div>
        </div>
        <div class="form-group">
              <label class="col-sm-4 control-label">{l s='Address' mod='fieldtestimonials'}:</label>
                <div class="col-sm-10">
                  <input id="Address" name="address" type="text" value="{if isset($address)}{$address|escape:'html':'UTF-8'}{/if}" size="40" class="form-control"/>
                  <sup class="require">*</sup>
                </div>
         </div>
        <div class="form-group">
          <label class="col-sm-4 control-label" >{l s='Media Link' mod='fieldtestimonials'}:</label>&nbsp;
            <div class="col-sm-10">
            <input id="media_link" name="media_link" type="text" value="{if isset($media_link)}{$media_link|escape:'html':'UTF-8'}{/if}" size="40" class="form-control"/>
            </div>
            </div>
            <div id="show_link"></div>
        <div class="form-group">
          <label class="col-sm-4 control-label">{l s='Multimedia' mod='fieldtestimonials'}:</label>&nbsp;
            <div class="col-sm-10">
            <input id="media" name="media" type="file" value="" size="40" class="form-control"/>
              <sup class="require">*</sup>
                </div></div>
        <div class="form-group">
          <label class="col-sm-4 control-label">{l s='Content' mod='fieldtestimonials'}:</label>&nbsp;
            <div class="col-sm-10">
            <textarea id="content" name="content" cols="50" rows="8" class="form-control">{if isset($content)}{$content|escape:'html':'UTF-8'|stripslashes}{/if}</textarea>
            <sup class="require">*</sup>
             </div></div>
        {if $captcha == 1}
            <div class="form-group">
            <label class="col-sm-3 control-label">{l s='Captcha' mod='fieldtestimonials'}:</label>&nbsp;
            <div class="col-sm-5">
                <input name="captcha" type="text" value="" size="20" class="form-control"/>&nbsp;
                <sup class="require">*</sup>
            </div>
            <div class="col-sm-4">
                <img src="{$captcha_code}" alt="{l s='captcha' mod='fieldtestimonials'}"/>
            </div></div>
        {/if}
        <div class="form-group">
            <div class="col-sm-5">
         <button id="submitNewTestimonial"  class="button btn btn-default button-medium" name="submitNewTestimonial" type="submit" value="{l s='Send Your Testimonial' mod='fieldtestimonials'}"/><span> send </span></button>
        </div>
            <label class="col-sm-4 control-label require"> * required field </label>
        </div>
    </form>

  </div>
</div>


