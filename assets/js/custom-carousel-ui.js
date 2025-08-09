jQuery(function($) {

    setTimeout(function(){

        $('.autoplay-on-hover .swiper').each(function(){
            let swiper = $(this)[0].swiper;

            if(!swiper) return;

            swiper.autoplay.stop();
            $(this).hover(function(){
                swiper.autoplay.start();
            }, function(){
                swiper.autoplay.stop();
            })
        })
    }, 1000)
})
