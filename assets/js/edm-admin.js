jQuery(document).ready(function($) {
    $('#glint-edm-setting-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        // Show loading indicator
        $('#save-edm-settings').text('Saving...').prop('disabled', true);
        
        $.ajax({
            url: glintEdmAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'save_edm_settings',
                nonce: glintEdmAdmin.nonce,
                ...$(this).serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {})
            },
            success: function(response) {
                if (response.success) {
                    $('#edm-message')
                        .removeClass('error hidden')
                        .addClass('success')
                        .html('<p>' + response.data + '</p>')
                        .show();
                } else {
                    $('#edm-message')
                        .removeClass('success hidden')
                        .addClass('error')
                        .html('<p>' + response.data + '</p>')
                        .show();
                }
            },
            error: function() {
                $('#edm-message')
                    .removeClass('success hidden')
                    .addClass('error')
                    .html('<p>An error occurred while saving settings.</p>')
                    .show();
            },
            complete: function() {
                $('#save-edm-settings').text('Save Settings').prop('disabled', false);
                
                // Hide message after 5 seconds
                setTimeout(function() {
                    $('#edm-message').fadeOut();
                }, 5000);
            }
        });
    });
});