jQuery(document).ready(function($) {
    // Initialize sortable
    $('#gallery-images').sortable({
        items: '.gallery-item',
        cursor: 'move',
        opacity: 0.6,
        update: function() {
            var gallery = [];
            $('#gallery-images .gallery-item').each(function() {
                gallery.push($(this).find('.remove-image').data('id'));
            });
            // Update the hidden input value
            $('#' + listingGallery.meta_key).val(gallery.join(','));
        }
    });

    $('#add-gallery-image').click(function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: 'Select or Upload Images',
            multiple: true,
            library: {
                type: 'image'
            }
        });

        frame.on('select', function() {
            var selection = frame.state().get('selection');
            var gallery = [];
            var galleryInput = $('#' + listingGallery.meta_key);
            
            if (galleryInput.val()) {
                gallery = galleryInput.val().split(',');
            }

            selection.models.forEach(function(attachment) {
                var attachmentId = attachment.attributes.id;
                if (gallery.indexOf(attachmentId.toString()) === -1) {
                    gallery.push(attachmentId);
                    
                    var image = attachment.attributes.sizes.thumbnail || attachment.attributes.sizes.full;
                    $('#gallery-images').append(
                        '<div class="gallery-item" style="cursor: move;">' +
                        '<img src="' + image.url + '" alt="Gallery Image">' +
                        '<button type="button" class="remove-image" data-id="' + attachmentId + '">—</button>' +
                        '</div>'
                    );
                }
            });

            galleryInput.val(gallery.join(','));
        });

        frame.open();
    });

    $(document).on('click', '.remove-image', function() {
        var attachmentId = $(this).data('id');
        var galleryInput = $('#' + listingGallery.meta_key);
        var gallery = galleryInput.val().split(',');
        
        gallery = gallery.filter(function(id) {
            return id !== attachmentId.toString();
        });

        $(this).closest('.gallery-item').remove();
        galleryInput.val(gallery.join(','));
    });
});
