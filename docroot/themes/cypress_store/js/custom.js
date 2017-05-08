(function ($) {
    $('.path-checkout .view-delivery-address .view-content').slick({
        infinite: false,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 4,
        arrows: true,
        responsive: [
            {
                breakpoint: 3000,
                settings: {
                    slidesToShow: 6,
                    slidesToScroll: 6,
                }
            },
            {
                breakpoint: 1700,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            },
            {
                breakpoint: 1440,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                }
            },
            // {
            //     breakpoint: 768,
            //     settings: {
            //         slidesToShow: 2,
            //         slidesToScroll: 2
            //     }
            // },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });
})(jQuery);
