jQuery(document).ready(function($) {
    // Initialize Swiper after Elementor is fully loaded
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        elementorFrontend.hooks.addAction('frontend/element_ready/listing-reviews.default', function($scope) {
            const $slider = $scope.find('.listing-reviews-slider');
            const $container = $slider.find('.swiper-container');

            // Clean up existing instance
            const existingSwiper = $container.data('swiper-instance');
            if (existingSwiper && existingSwiper.destroy) {
                existingSwiper.destroy(true, true);
            }

            // Simple initialization for editor
            if (window.elementorFrontend?.isEditMode()) {
                initializeSwiperEditor($slider);
            } else {
                initializeSwiper($slider);
            }
        });
    }

    // On frontend page load
    if (!window.elementorFrontend?.isEditMode()) {
        $('.listing-reviews-slider').each(function() {
            initializeSwiper($(this));
        });
    }

    function initializeSwiperEditor($slider) {
        const $container = $slider.find('.swiper-container');
        const slidesPerView = $slider.attr('data-slides-per-view') || 1;

        // Basic configuration for editor
        const swiper = new Swiper($container[0], {
            slidesPerView: slidesPerView,
            spaceBetween: 30,
            speed: 400,
            loop: false,
            autoplay: false,
            pagination: {
                el: $slider.find('.swiper-pagination')[0],
                clickable: true
            },
            navigation: {
                nextEl: $slider.find('.swiper-button-next')[0],
                prevEl: $slider.find('.swiper-button-prev')[0]
            }
        });

        $container.data('swiper-instance', swiper);
    }

    function initializeSwiper($slider) {
        const slidesPerView = $slider.attr('data-slides-per-view') || 1;
        const $container = $slider.find('.swiper-container');

        const swiper = new Swiper($container[0], {
            slidesPerView: slidesPerView,
            spaceBetween: 30,
            speed: 800,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            pagination: {
                el: $slider.find('.swiper-pagination')[0],
                clickable: true,
                dynamicBullets: true
            },
            navigation: {
                nextEl: $slider.find('.swiper-button-next')[0],
                prevEl: $slider.find('.swiper-button-prev')[0],
                disabledClass: 'swiper-button-disabled'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 15
                },
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                768: {
                    slidesPerView: Math.min(slidesPerView > 1 ? slidesPerView : 2, 2),
                    spaceBetween: 25
                },
                1024: {
                    slidesPerView: Math.min(slidesPerView > 1 ? slidesPerView : 3, 3),
                    spaceBetween: 30
                }
            },
            mousewheel: {
                forceToAxis: true,
                sensitivity: 1
            },
            keyboard: {
                enabled: true,
                onlyInViewport: true
            },
            effect: 'slide',
            observer: true,
            observeParents: true,
            preloadImages: true,
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 1
            }
        });

        // Store the swiper instance
        $container.data('swiper-instance', swiper);

        // Handle slide changes
        swiper.on('slideChange', function() {
            $slider.find('.swiper-slide').removeClass('active-slide');
            $slider.find('.swiper-slide-active').addClass('active-slide');
        });

        // Handle window resize
        $(window).on('resize', function() {
            swiper.update();
        });
    }
});
