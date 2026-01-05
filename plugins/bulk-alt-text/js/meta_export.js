jQuery(document).ready(function ($) {
    $('#export-button').on('click', function (e) {
        e.preventDefault();
        console.log('Export button clicked'); // Debug message

        const exportType = $('#export-type').val();

        if (!exportType) {
            alert('Please select a type to export.');
            return;
        }

        console.log('Export type:', exportType); // Debug message

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'export_meta_data',
                export_type: exportType,
                security: BulkAltUpdater.nonce,
            },
            success: function (response) {
                console.log('Export response:', response); // Debug message
                if (response.success) {
                    window.location.href = response.data.file_url; // Trigger download
                } else {
                    alert('Export failed: ' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error); // Debug message
                alert('An error occurred while processing the export.');
            },
        });
    });
});
