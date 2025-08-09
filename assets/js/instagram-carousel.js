jQuery(document).ready(function($) {
    class InstagramCarousel {
        constructor(element) {
            this.element = element;
            this.wrapper = element;
            this.carousel = element.find('.instagram-carousel-container');
            this.init();
        }

        init() {
            this.setupCarousel();
            this.fetchInstagramPosts();
        }

        setupCarousel() {
            const columns = parseInt(this.wrapper.data('columns')) || 4;
            const swiperOptions = {
                slidesPerView: columns,
                spaceBetween: 10,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    320: {
                        slidesPerView: 2,
                    },
                    768: {
                        slidesPerView: 3,
                    },
                    1024: {
                        slidesPerView: columns,
                    },
                },
            };

            this.swiper = new Swiper(this.carousel[0], swiperOptions);
        }

        async fetchInstagramPosts() {
            const number = parseInt(this.wrapper.data('number')) || 8;
            const showCaption = this.wrapper.data('show-caption') === 'yes';

            console.log('Fetching Instagram posts', { number, showCaption });

            if (!window.blueberry_instagram?.access_token) {
                console.error('Instagram access token is missing');
                this.showError('Instagram access token is missing. Please connect your Instagram account in the WordPress admin settings.');
                return;
            }

            try {
                const accessToken = window.blueberry_instagram.access_token;
                const mediaUrl = `https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username&limit=${number}&access_token=${accessToken}`;

                console.log('Fetching media...', mediaUrl);
                const response = await fetch(mediaUrl);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error?.message || 'Failed to fetch media');
                }

                if (!data.data || data.data.length === 0) {
                    throw new Error('No posts found for this account');
                }

                console.log('Fetched posts:', data.data);
                this.displayPosts(data.data, showCaption);

            } catch (error) {
                console.error('Error fetching Instagram posts:', error);
                this.showError(error.message || 'Failed to load Instagram posts. Please try again later.');
            }
        }

        displayPosts(posts, showCaption) {
            const swiperWrapper = this.carousel.find('.swiper-wrapper');
            swiperWrapper.empty();

            posts.forEach(post => {
                const imageUrl = post.media_type === 'VIDEO' ? post.thumbnail_url : post.media_url;
                const caption = showCaption && post.caption ? post.caption : '';

                const postElement = $(`
            <div class="swiper-slide">
                <a href="${post.permalink}" target="_blank" rel="noopener noreferrer">
                    <img src="${imageUrl}" alt="${caption}" class="instagram-image">
                    ${showCaption && caption ? `<div class="instagram-caption">${caption}</div>` : ''}
                </a>
            </div>
        `);

                swiperWrapper.append(postElement);
            });

            // Update Swiper
            if (this.swiper) {
                this.swiper.update();
            }

            this.hideLoading();
        }

        showError(message = 'Error loading Instagram posts') {
            this.wrapper.html(`<div class="instagram-error">${message}</div>`);

            this.hideLoading();
        }

        hideLoading() {
            this.carousel.find('.instagram-loading').remove();
        }
    }

    // Initialize carousels
    $('.instagram-carousel').each(function() {
        new InstagramCarousel($(this));
    });
});
