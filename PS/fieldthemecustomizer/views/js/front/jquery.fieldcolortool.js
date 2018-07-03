$(document).ready(function() {
    function add_backgroundcolor(bgcolor) {
	$('<style type="text/css">.button_unique:hover,.v-megamenu-title,.box_banner_product a.btn_content, .outer-slide [data-u="arrowright"],.outer-slide [data-u="arrowleft"],a.slide-button:hover,.new_product,.menu-bottom .menu-bottom-dec a,#header_mobile_menu .fieldmm-nav,.product-actions .add-to-cart:hover,.bootstrap-touchspin .group-span-filestyle .btn-touchspin, .group-span-filestyle .bootstrap-touchspin .btn-touchspin, .group-span-filestyle .btn-default,.btn-tertiary ,.btn-primary,.btn,.cart-grid .cart-grid-body > a.label,.field-demo-wrap .control.inactive,.cl-row-reset .cl-reset,#header_mobile_menu ,.page-footer .text-xs-center a ,.page-footer a.account-link ,#blockcart-modal .cart-content .btn,#cart_block_top .cart_top_ajax a.view-cart,.button_unique:hover,a.show_now_full:hover,.box_f1 a:hover,.bd_shop_now:hover,.owl-buttons [class^="carousel-"] span,.horizontal_mode .item-inner .right-product .add-to-cart,.news_form button:hover{ background-color:#' + bgcolor + '}</style>').appendTo('head');
	$('<style type="text/css">#cms #cms-about-us .cms-line .label,.click-product-list-grid > div,#cms #cms-about-us .cms-line .label{ color:#' + bgcolor + '}</style>').appendTo('head');
	$('<style type="text/css">.box_f1 a:hover{ border-color:#' + bgcolor + '}</style>').appendTo('head');
    }
    function add_hovercolor(hcolor) {
	$('<style type="text/css">.button-action .quick-view:hover,.button-action .add-to-cart:hover,.button_unique,.tag_block li a:hover,.owl-theme .owl-controls .owl-page:hover:before,.owl-theme .owl-controls .active.owl-page:before, #back-top a,.title_block .title_text,#tags_block_left a:hover,.box_banner_product,.outer-slide [data-u="arrowright"]:hover, .outer-slide [data-u="arrowleft"]:hover, .outer-slide [data-u="navigator"] [data-u="prototype"]:hover, .outer-slide:hover [u="navigator"], .outer-slide [data-u="navigator"] .av[data-u="prototype"],a.slide-button,.product-full-vertical .owl-buttons [class^="carousel-"] span:hover,.sale_product,.price-percent-reduction,#header_menu .fieldmegamenu .root:hover .root-item > a > .title,#header_menu .fieldmegamenu .root:hover .root-item > .title,#header_menu .fieldmegamenu .root.active .root-item > a > .title,#header_menu .fieldmegamenu .root.active .root-item > .title,#header_menu .fieldmegamenu .root .root-item > a.active > .title,.menu-bottom .menu-bottom-dec a:hover,.v-megamenu > ul > li:hover > a:not(.opener),.modal-header .close:hover,.has-discount .discount,#fieldsizechart-show:hover ,.product-actions .add-to-cart,.social-sharing li a:hover,.products.horizontal_mode #box-product-list .quick-view:hover,.products.horizontal_mode #box-product-list .add-to-cart:hover,#products .item-product-list .right-product .discount-percentage-product,.products-sort-order .select-list:hover,.block-categories > ul > li:first-child a ,.btn-secondary.focus, .btn-secondary:focus, .btn-secondary:hover, .btn-tertiary:focus, .btn-tertiary:hover, .focus.btn-tertiary,.btn-primary.focus,.btn-primary:focus,.btn-primary:hover,.btn:hover,.btn-primary:active,.cart-grid .cart-grid-body > a.label:hover,.pagination .current a,.pagination a:not(.disabled ):hover,#cms #cms-about-us .page-subheading ,#cms #cms-about-us .cms-line .cms-line-comp,.field-demo-wrap .control.active,.cl-row-reset .cl-reset:hover,#fieldmegamenu-main.fieldmegamenu .root:hover .root-item > a > .title:after,#fieldmegamenu-main.fieldmegamenu .root:hover .root-item > .title:after,#fieldmegamenu-main.fieldmegamenu .root.active .root-item > a > .title:after,#fieldmegamenu-main.fieldmegamenu .root.active .root-item > .title:after,#fieldmegamenu-main.fieldmegamenu .root .root-item > a.active > .title:after,.menu-bottom .menu-bottom-dec a,#recent_article_smart_blog_block_left .block_content ul li a.read-more:hover,.field-slideshow-container .flex-control-paging li a:hover, .field-slideshow-container .flex-control-paging li a.flex-active, .nivo-controlNav a:hover, .nivo-controlNav a.active,.page-footer .text-xs-center a:hover,.page-footer a.account-link:hover,#blockcart-modal .cart-content .btn:hover,#cart_block_top .cart_top_ajax a.view-cart:hover,#search_block_top .current:hover,#search_block_top .current[aria-expanded=true],#search_block_top .btn.button-search,#search_block_top .btn.button-search:hover,#search_block_top .btn.button-search.active,.right_blog_home .content a:hover,.bbb_aaa:before,.bbb_aaa:after,.footer-newsletter .button-newletter,.footer-container .bullet ul li a:hover:before,.footer-container .contact_ft ul li div,.social_footer a:hover,#newsletter_block_popup .block_content,a.show_now_full,.right-block-full .section_cout ,.banner_sizechart p a:hover,.banner_sizechart p a.buy_now,.box_f1,.bd_title_block:before,#cart_block_top span.fa,.sticky-fixed-top .bd_cart,.bd_shop_now,.owl-buttons [class^="carousel-"] span:hover,.tab_title_text,.horizontal_mode .item-inner .right-product .add-to-cart:hover,.news_content ul li:before,.news_form button,.bd_social .bd_content a:hover,.bd_footer_block.bd_links .bd_content ul li a:before ,.js-qv-mask .owl-theme .owl-controls .owl-buttons [class^="carousel-"] span:hover,.tabs .nav-tabs .nav-link.active, .tabs .nav-tabs .nav-link:hover{ background-color:#' + hcolor + '}</style>').appendTo('head');
	$('<style type="text/css">  #header_menu:not(.fieldmegamenu-sticky) #fieldmegamenu-main.fieldmegamenu .root:hover .root-item > a > .title, #header_menu:not(.fieldmegamenu-sticky) #fieldmegamenu-main.fieldmegamenu .root:hover .root-item > .title, #header_menu:not(.fieldmegamenu-sticky) #fieldmegamenu-main.fieldmegamenu .root.active .root-item > a > .title, #header_menu:not(.fieldmegamenu-sticky) #fieldmegamenu-main.fieldmegamenu .root.active .root-item > .title, #header_menu:not(.fieldmegamenu-sticky) #fieldmegamenu-main.fieldmegamenu .root .root-item > a.active > .title,a:hover, a:focus,.cart-grid-body a.label:hover,.fieldtabproductsisotope-filter a.active,.fieldtabproductsisotope-filter a:hover,.box_banner_product a.btn_content:hover,.box-static_content:hover > .fa,.product-full-vertical .title_block .title_text,#header a:hover,#header .dropdown-menu li.current a,#header .dropdown-menu li:hover a,#header .header-nav .language-selector:hover .expand-more,#header .header-nav .currency-selector:hover .expand-more,#header .header-nav .language-selector .expand-more[aria-expanded=true],#header .header-nav .currency-selector .expand-more[aria-expanded=true],#info-nav.header_links li span,.header_links li a:hover,#header .header-nav #mobile_links:hover .expand-more,#header .header-nav #mobile_links .expand-more[aria-expanded=true],.ui-menu .ui-menu-item a.ui-state-focus .search-name-ajax, .ui-menu .ui-menu-item a.ui-state-active .search-name-ajax,.price-ajax,.link_feature:hover,.button-action .quick-view:hover,.button-action .add-to-cart:hover,.price,#header .fieldmegamenu .menu-item.depth-1 > .title a:hover,.fieldmegamenu .submenu .title:hover a,.fieldmegamenu .menu-item.depth-1 > .title a:hover,#header .fieldmegamenu .submenu .title a:hover,.menu-bottom h3,.custom_link_feature li:hover a,#fieldmegamenu-mobile.fieldmegamenu > ul > li .no-description .title:hover,.fieldmegamenu .demo_custom_link_cms .menu-item.depth-1 > .title:hover a,.v-main-section-sublinks li a:hover,.v-main-section-links > li > a:hover,.has-discount.product-price, .has-discount p,.click-product-list-grid > div:hover,.active_list .click-product-list-grid > div.click-product-list,.active_grid .click-product-list-grid > div.click-product-grid,#products .item-product-list .right-product .product-price .price,.block-categories a:hover,.block-categories .collapse-icons .add:hover, .block-categories .collapse-icons .remove:hover,.block-categories .arrows .arrow-down:hover, .block-categories .arrows .arrow-right:hover,.product-cover .layer:hover .zoom-in,#fieldmegamenu-main.fieldmegamenu .root:hover .root-item > a > .title,#fieldmegamenu-main.fieldmegamenu .root:hover .root-item > .title,#fieldmegamenu-main.fieldmegamenu .root.active .root-item > a > .title,#fieldmegamenu-main.fieldmegamenu .root.active .root-item > .title,#fieldmegamenu-main.fieldmegamenu .root .root-item > a.active > .title,#fieldmegamenu-mobile.fieldmegamenu .root:hover .root-item > a > .title , #fieldmegamenu-mobile.fieldmegamenu .root:hover .root-item > .title , #fieldmegamenu-mobile.fieldmegamenu .root.active .root-item > a > .title , #fieldmegamenu-mobile.fieldmegamenu .root.active .root-item > .title , #fieldmegamenu-mobile.fieldmegamenu .root .root-item > a.active > .title,.fieldmegamenu .menu-item.depth-1 > .title a:hover,.fieldmegamenu .demo_custom_link_cms .menu-item.depth-1 > .title a:hover ,.fieldmegamenu .submenu .title a:hover,.menu-bottom h3,.custom_link_feature li a:hover,.custom-col-html a,.custom-col-html h4 ,#recent_article_smart_blog_block_left .block_content ul li .info,.info-category span,.info-category span a,.order-confirmation-table .text-xs-left,.order-confirmation-table .text-xs-right,#order-items table tr td:last-child,.page-my-account #content .links a:hover ,.page-my-account #content .links a:hover i,body#checkout section.checkout-step .add-address a:hover,.page-addresses .address .address-footer a:hover,.cart-summary-line .value,.product-line-grid-right .cart-line-product-actions, .product-line-grid-right .product-price,#blockcart-modal .cart-content p,.product-price,#blockcart-modal .divide-right p.price,.tabs .nav-tabs .nav-link.active, .tabs .nav-tabs .nav-link:hover,.cart_top_ajax:before ,#cart_block_top .product-name-ajax a:hover,#cart_block_top .cart_top_ajax a.remove-from-cart:hover,#search_block_top .current,#search_block_top div.dropdown-menu:before,#search_block_top .search_filter div.selector.hover span::before, #search_block_top .search_filter div.selector.focus span::before,.right_blog_home .block_date_post span,.right_blog_home .content h3:hover a,.sdsblog-box-content .sds_blog_post:hover .right_blog_home .r_more,#testimonials_block_right .next.bx-next:hover:before,#testimonials_block_right .prev.bx-prev:hover:before,#testimonials_block_right p.des_company,.footer-container .links ul.tag_block > li a:hover,.footer-container .bullet ul li a:hover,.footer-address a,#wrapper .breadcrumb li:last-child a,#wrapper .breadcrumb li a:hover,#wrapper .breadcrumb li a:hover,.banner_sizechart p a,.banner_sizechart p a.buy_now:hover,.left_box_infor .fa,.right_box_infor p a:hover,.tab_cates li:hover,.tab_cates li.active,#header .desktop_links ul li a:hover,.bd_title_2,#smart-blog-custom .bd_title_post a:hover,.bd_store .bd_content ul li div,.bd_footer_block.bd_links .bd_content ul li a:hover,.product-prices .current-price{ color:#' + hcolor + '}</style>').appendTo('head');
	$('<style type="text/css"> .title_block,.fieldtabproductsisotope-filter a.active,.fieldtabproductsisotope-filter a:hover,.fieldmegamenu .menu-items:before,#header_menu.fieldmegamenu-sticky,.v-megamenu > ul > li div.submenu,#search_filters > h4,.form-control:focus,.search-widget form input[type="text"]:focus,.cart_top_ajax,.box_testimonials::before ,#pagination_cycle .activeSlide.main-block img,.footer-container .links ul.tag_block > li a:hover,.left-block-full > a .bor1,.left-block-full > a .bor1,.left-block-full > a .bor2,.left-block-full > a .bor2,.left-block-full > a .bor3,.left-block-full > a .bor3,.left-block-full > a .bor4,.left-block-full > a .bor4,.banner_sizechart p a, .bd_title_block, .tab-category-slider,.js-qv-mask .owl-theme .owl-controls .owl-buttons [class^="carousel-"] span:hover,.tabs .nav-tabs{ border-color:#' + hcolor + '}</style>').appendTo('head');
    }
    $('.control').click(function() {

	if ($(this).hasClass('inactive')) {
	    $(this).removeClass('inactive');
	    $(this).addClass('active');
	    if (LANG_RTL == '1') {
		$('.field-demo-wrap').animate({right: '0'}, 500);
	    } else {
		$('.field-demo-wrap').animate({left: '0'}, 500);
	    }
	    $('.field-demo-wrap').css({'box-shadow': '0 0 10px #adadad', 'background': '#fff'});
	    $('.field-demo-option').animate({'opacity': '1'}, 500);
	    $('.field-demo-title').animate({'opacity': '1'}, 500);
	} else {
	    $(this).removeClass('active');
	    $(this).addClass('inactive');
	    if (LANG_RTL == '1') {
		$('.field-demo-wrap').animate({right: '-210px'}, 500);
	    } else {
		$('.field-demo-wrap').animate({left: '-210px'}, 500);
	    }
	    $('.field-demo-wrap').css({'box-shadow': 'none', 'background': 'transparent'});
	    $('.field-demo-option').animate({'opacity': '0'}, 500);
	    $('.field-demo-title').animate({'opacity': '0'}, 500);
	}
    });
    $('#backgroundColor, #hoverColor').each(function() {
	var $el = $(this);
	/* set time */var date = new Date();
	date.setTime(date.getTime() + (1440 * 60 * 1000));
	$el.ColorPicker({color: '#555555', onChange: function(hsb, hex, rgb) {
		$el.find('div').css('backgroundColor', '#' + hex);
		switch ($el.attr("id")) {
		    case 'backgroundColor' :
			add_backgroundcolor(hex);
			$.cookie('background_color_cookie', hex, {expires: date});
			break;
		    case 'hoverColor' :
			add_hovercolor(hex);
			$.cookie('hover_color_cookie', hex, {expires: date});
			break;
		    }
	    }});
    });
    /* set time */var date = new Date();
    date.setTime(date.getTime() + (1440 * 60 * 1000));
    if ($.cookie('background_color_cookie') && $.cookie('hover_color_cookie')) {
	add_backgroundcolor($.cookie('background_color_cookie'));
	add_hovercolor($.cookie('hover_color_cookie'));
	var backgr = "#" + $.cookie('background_color_cookie');
	var activegr = "#" + $.cookie('hover_color_cookie');
	$('#backgroundColor div').css({'background-color': backgr});
	$('#hoverColor div').css({'background-color': activegr});
    }
    /*Theme mode layout*/
    if (!$.cookie('mode_css') && FIELD_mainLayout == "boxed"){
	$('input[name=mode_css][value=box]').attr("checked", true);
    } else if (!$.cookie('mode_css') && FIELD_mainLayout == "fullwidth") {
	$('input[name=mode_css][value=wide]').attr("checked", true);
    } else if ($.cookie('mode_css') == "boxed") {
	$('body').removeClass('fullwidth');
	$('body').removeClass('boxed');
	$('body').addClass('boxed');
	$.cookie('mode_css', 'boxed');
	$.cookie('mode_css_input', 'box');
	$('input[name=mode_css][value=box]').attr("checked", true);
    } else if ($.cookie('mode_css') == "fullwidth") {
	$('body').removeClass('fullwidth');
	$('body').removeClass('boxed');
	$('body').addClass('fullwidth');
	$.cookie('mode_css', 'fullwidth');
	$.cookie('mode_css_input', 'wide');
	$('input[name=mode_css][value=wide]').attr("checked", true);
    }
    $('input[name=mode_css][value=box]').click(function() {
	$('body').removeClass('fullwidth');
	$('body').removeClass('boxed');
	$('body').addClass('boxed');
	$.cookie('mode_css', 'boxed');
        fullwidth_click();
    });
    $('input[name=mode_css][value=wide]').click(function() {
	$('body').removeClass('fullwidth');
	$('body').removeClass('boxed');
	$('body').addClass('fullwidth');
	$.cookie('mode_css', 'fullwidth');
        fullwidth_click();
    });
    $('.cl-td-layout a').click(function() {
	var id_color = this.id;
	$.cookie('background_color_cookie', id_color.substring(0, 6));
	$.cookie('hover_color_cookie', id_color.substring(7, 13));
	add_backgroundcolor($.cookie('background_color_cookie'));
	add_hovercolor($.cookie('hover_color_cookie'));
	var backgr = "#" + $.cookie('background_color_cookie');
	var activegr = "#" + $.cookie('hover_color_cookie');
	$('#backgroundColor div').css({'background-color': backgr});
	$('#hoverColor div').css({'background-color': activegr});
    });
    /*reset button*/$('.cl-reset').click(function() {
	/* Color */$.cookie('background_color_cookie', '');
	$.cookie('hover_color_cookie', '');
	/* Mode layout */$.cookie('mode_css', '');
	location.reload();
    });
    function fullwidth_click(){
        $('.fieldFullWidth').each(function() {
                var t = $(this);
                var fullwidth = $('main').width(),
                    margin_full = fullwidth/2;
        if (LANG_RTL != 1) {
                t.css({'left': '50%', 'position': 'relative', 'width': fullwidth, 'margin-left': -margin_full});
        } else{
                t.css({'right': '50%', 'position': 'relative', 'width': fullwidth, 'margin-right': -margin_full});
        }
    });
    }
});