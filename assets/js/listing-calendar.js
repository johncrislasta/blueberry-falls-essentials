jQuery(document).ready(function($) {
    function updateCalendar(calendar_id) {
        wp.apiFetch({
            path: '/wp/v2/listing/' + listingCalendar.post_id,
            method: 'POST',
            data: {
                meta: {
                    [listingCalendar.meta_key]: calendar_id
                }
            },
            headers: {
                'X-WP-Nonce': listingCalendar.nonce
            }
        }).then(function(response) {
            console.log('Calendar updated successfully:', response);
        }).catch(function(error) {
            console.error('Error updating calendar:', error);
        });
    }

    $('#' + listingCalendar.meta_key).change(function() {
        var calendar_id = $(this).val();
        updateCalendar(calendar_id);
    });
});
