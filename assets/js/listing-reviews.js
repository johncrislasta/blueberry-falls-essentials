jQuery(document).ready(function($) {
    // Handle add review button click
    $('#add-review').click(function() {
        var template = $('#review-template').html();
        var index = $('.review-item').length;
        
        // Replace 'new' with actual index in the template
        template = template.replace(/new/g, index);
        
        // Add new review fields
        $('#reviews-list').append(template);
        
        // Initialize the remove button for the new review
        $('.remove-review').last().click(function() {
            $(this).closest('.review-item').remove();
        });
    });
});