$(window).load(function(){
    $('#smart-blog-custom').owlCarousel({
        itemsCustom: [ [0, 1], [320, 1], [568, 1], [992, 1], [1200, 1] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: false,
        stopOnHover: true,
        rewindNav: true,
        pagination: false,
        navigation: true,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
    });
});