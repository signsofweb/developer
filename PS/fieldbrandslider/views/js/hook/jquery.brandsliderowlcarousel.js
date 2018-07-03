$(window).load(function(){
    $('#fieldbrandslider-manu').owlCarousel({
        itemsCustom: [ [0, 2], [320, fieldbs_minitem], [479, 3], [768, 3], [992, 3], [1023, 3], [1200, 3], [1559, 3] ],
        responsiveRefreshRate: 50,
        slideSpeed: 200,
        paginationSpeed: 500,
        rewindSpeed: 600,
        autoPlay: fieldbs_autoscroll,
        stopOnHover: fieldbs_pauseonhover,
        rewindNav: true,
        pagination: false,
        navigation: false,
        navigationText: ['<div class="carousel-previous disable-select"><span class="fa fa-angle-left"></span></div>', '<div class="carousel-next disable-select"><span class="fa fa-angle-right"></span></div>']
    });
});