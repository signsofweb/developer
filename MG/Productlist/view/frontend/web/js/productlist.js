function owlCarouselInit(eOwlData, eOwlCarousel){
    var config = [];
    if(jQuery(eOwlData).data('nav')){
        config['nav'] = jQuery(eOwlData).data('nav');
    }
    if(jQuery(eOwlData).data('dot')){
        config['dots'] = jQuery(eOwlData).data('dot');
    }else{
        config['dots'] = false;
    }
    if(jQuery(eOwlData).data('autoplay')){
        config['autoplay'] = jQuery(eOwlData).data('autoplay');
    }
    if(jQuery(eOwlData).data('autoplay-timeout')){
        config['autoplayTimeout'] = jQuery(eOwlData).data('autoplay-timeout');
    }
    if(jQuery(eOwlData).data('rtl')){
        config['rtl'] = jQuery(eOwlData).data('rtl');
    }
    if(jQuery(eOwlData).data('loop')){
        config['loop'] = jQuery(eOwlData).data('loop');
    }
    config['navText'] = [ 'prev', 'next' ];
    if(jQuery(eOwlData).data("nav-text-owlpre")){
        config['navText'] = [ jQuery(eOwlData).data("nav-text-owlpre"), 'next' ];
    }
    if(jQuery(eOwlData).data("nav-text-owlnext")){
        config['navText'] = [ 'pre', jQuery(eOwlData).data("nav-text-owlnext") ];
    }
    if(jQuery(eOwlData).data("nav-text-owlpre") && jQuery(eOwlData).data("nav-text-owlnext")){
        config['navText'] = [ jQuery(eOwlData).data("nav-text-owlpre"), jQuery(eOwlData).data("nav-text-owlnext") ];   
    }
    var mobile_items = 1;
    if(jQuery(eOwlData).data('mobile-items')){
        mobile_items = jQuery(eOwlData).data('mobile-items');
    }
    var tablet_small_items = 3;
    if(jQuery(eOwlData).data('tablet-small-items')){
        tablet_small_items = jQuery(eOwlData).data('tablet-small-items');
    }
    var tablet_items = 3;
    if(jQuery(eOwlData).data('tablet-items')){
        tablet_items = jQuery(eOwlData).data('tablet-items');
    }
    var portrait_items = 4;
    if(jQuery(eOwlData).data('portrait-items')){
        portrait_items = jQuery(eOwlData).data('portrait-items');
    }
    var large_items = 5;
    if(jQuery(eOwlData).data('large-items')){
        large_items = jQuery(eOwlData).data('large-items');
    }
    var large_max_items = 6;
    if(jQuery(eOwlData).data('large-max-items')){
        large_max_items = jQuery(eOwlData).data('large-max-items');
    }
    config['responsive'] = {
        0 : {items: mobile_items},
        480 : {items: tablet_small_items},
        640 : {items: tablet_items},
        768 : {items: portrait_items},
        980 : {items: large_items},
        1200 : {items: large_max_items}
    };
    jQuery(eOwlCarousel).owlCarousel(config);
    equalHeight(eOwlData);
}
function ajaxProducts(tabData, ajaxUrl, margin){
    jQuery.ajax({
        url: ajaxUrl,
        dataType: 'json', 
        type : 'post',
        data : tabData,
        cache: true,
        beforeSend: function(){
            jQuery("#"+tabData.tab.id).addClass('productlist-wait');
            jQuery("#"+tabData.tab.id+">div").fadeOut();
        },
        success: function(dataResponse){
            var tabId = "#"+dataResponse.tab.id;
            jQuery("#"+tabData.tab.id).removeClass('productlist-wait');
            jQuery(tabId+ ' .product-items').append(dataResponse.html);
            jQuery("#"+tabData.tab.id+">div").fadeIn();
            if(dataResponse.layout_type == 'owl_carousel'){
                owlCarouselInit("#"+dataResponse.ajaxBlockId, tabId+" .product-items");
            }
            jQuery(tabId).find('.owl-nav .owl-prev').css({'margin-right': margin});
            jQuery(tabId).find('.owl-nav .owl-next').css({'margin-left': margin});
        }
    });
}
function ajaxClickProduct(tabData, ajaxUrl, margin){
    jQuery("#tab-" + tabData.tab.id ).on("click", function(){
        if(jQuery(this).hasClass("has-click")) return;
        jQuery(this).addClass('has-click');
        jQuery.ajax({
            url: ajaxUrl,
            dataType: 'json', 
            type : 'post',
            data : tabData,
            cache: true,
            beforeSend: function(){
                jQuery("#"+tabData.tab.id).addClass('productlist-wait');
                jQuery("#"+tabData.tab.id+">div").fadeOut();
            },
            success: function(dataResponse){
                var tabId = "#"+dataResponse.tab.id;
                jQuery("#"+tabData.tab.id).removeClass('productlist-wait');
                jQuery(tabId+ ' .product-items').append(dataResponse.html);
                jQuery("#"+tabData.tab.id+">div").fadeIn();
                if(dataResponse.layout_type == 'owl_carousel'){
                    owlCarouselInit("#"+dataResponse.ajaxBlockId, tabId+" .product-items");
                }
                jQuery(tabId).find('.owl-nav .owl-prev').css({'margin-right': margin});
                jQuery(tabId).find('.owl-nav .owl-next').css({'margin-left': margin});
            }
        });
    });
}
function easyTabInit(eEasyTabId){
    var config = [];
    var t = jQuery(eEasyTabId);
    if(t.data('animate')){
        config['animate'] = t.data('animate');
    }
    if(t.data('animation-speed')){
        config['animationSpeed'] = t.data('animation-speed');
    }
    if(t.data('collapsible')){
        config['collapsible'] = t.data('collapsible');
    }
    if(t.data('cycle')){
        config['cycle'] = t.data('cycle');
    }
    if(t.data('default-tab')){
        config['defaultTab'] = t.data('default-tab');
    }
    if(t.data('transition-in')){
        config['transitionIn'] = t.data('transition-in');
    }
    if(t.data('transition-in-easing')){
        config['transitionInEasing'] = t.data('transition-in-easing');
    }
    if(t.data('transition-out')){
        config['transitionOut'] = t.data('transition-out');
    }
    if(t.data('transition-out-easing')){
        config['transitionOutEasing'] = t.data('transition-out-easing');
    }
    if(t.data('event')){
        config['event'] = t.data('event');
    }
    config.updateHash = false;
    t.easytabs(config);
    return t;
}
function equalHeight(objectId){
    if(jQuery(objectId).data('height-type')!='' && jQuery(objectId).data('height-type')=='equal'){
        var height = 0;
        if(jQuery(objectId+" .product-item-info").height){
            jQuery(objectId+" .product-item-info").each(function(){
                if(jQuery(this).height()>height){
                    height = jQuery(this).height();
                }
            });
            if(height>0){
                jQuery(objectId+" .product-item-info").css({"height":height+"px"});
            }
        }
    }
}