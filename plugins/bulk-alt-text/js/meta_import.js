jQuery(document).ready(function ($) {
    // Preview functionality
    $('#preview-button').on('click', function (e) {
        e.preventDefault();

        const importType = $('#import-type').val();
        const importFile = $('#import-file')[0].files[0];

        if (!importType) {
            alert('Please select a type to preview.');
            return;
        }

        if (!importFile) {
            alert('Please upload a valid CSV file.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'preview_meta_import');
        formData.append('import_type', importType);
        formData.append('import_file', importFile);
        formData.append('security', BulkAltUpdater.nonce);

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const previewData = response.data.preview;
                    let previewTable = '<table class="wp-list-table widefat fixed striped">';
                    previewTable += '<thead><tr><th>ID</th><th>Type</th><th>URL</th><th>Old Title</th><th>New Title</th><th>Old SEO Title</th><th>New SEO Title</th><th>Old Meta Description</th><th>New Meta Description</th></tr></thead>';
                    previewTable += '<tbody>';

                    previewData.forEach(function (row) {
                        previewTable += `<tr>
                            <td>${row.ID}</td>
                            <td>${row.Type}</td>
                            <td>${row.URL}</td>
                            <td>${row.Old_Page_Title}</td>
                            <td>${row.New_Page_Title}</td>
                            <td>${row.Old_SEO_Title}</td>
                            <td>${row.New_SEO_Title}</td>
                            <td>${row.Old_Meta_Description}</td>
                            <td>${row.New_Meta_Description}</td>
                        </tr>`;
                    });

                    previewTable += '</tbody></table>';
                    $('#import-preview').html(previewTable);

                    // Show Import button
                    $('#import-button').show();
                } else {
                    alert('Preview failed: ' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                alert('An error occurred while processing the preview.');
                console.error('Preview AJAX Error:', error);
            },
        });
  
    });

   // Import functionality (unchanged from the previous version)
   const $progressModal = $('#progress-modal');
   const $progressBar = $('#progress-bar');
   const $progressText = $('#progress-text');
   const $closeButton = $('#close-progress');

   $('#import-button').on('click', function (e) {
    e.preventDefault();

    const importType = $('#import-type').val();
    const importFile = $('#import-file')[0].files[0];

    if (!importType || !importFile) {
        alert('Please select both a type and a valid CSV file.');
        return;
    }

    if (!confirm('Are you sure you want to import this file?')) {
        return;
    }

    let processed = 0;
    let tempFilePath = null;
    let isProcessing = true;
    let totalRecords = null;

    // Show the progress modal
    $progressModal.css('display', 'flex');
    $progressBar.val(0);
    $progressText.text('Initializing import...');

    function updateProgressBar(current, total) {
        const percentage = Math.round((current / total) * 100);
        $progressBar.val(percentage);
        $progressText.text(`Processing: ${current} of ${total} records (${percentage}%)`);
    }

    function processBatch() {
        const batchData = new FormData();
        batchData.append('action', 'import_meta_data');
        batchData.append('import_type', importType);
        batchData.append('security', BulkAltUpdater.nonce);
        batchData.append('processed', processed);
    
        if (!tempFilePath) {
            batchData.append('import_file', importFile);
        } else {
            batchData.append('temp_file_path', tempFilePath);
        }
    
        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: batchData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (!isProcessing) return;
    
                if (response.success) {
                    const { progress, total_records, complete, temp_file_path, processed: newProcessed } = response.data;
                    
                    tempFilePath = temp_file_path || tempFilePath;
                    processed = parseInt(newProcessed || progress);
                    
                    // Set total records on first batch
                    if (totalRecords === null) {
                        totalRecords = total_records;
                    }
    
                    // Update progress bar
                    updateProgressBar(processed, totalRecords);
    
                    if (!complete && processed < totalRecords) {
                        setTimeout(processBatch, 500);
                    } else {
                        $progressText.text('Import completed successfully!');
                        $progressBar.val(100);
                        setTimeout(() => {
                            alert('Import completed successfully!');
                            $progressModal.hide();
                        }, 500);
                    }
                } else {
                    handleError(response.data.message || 'Unknown error occurred');
                }
            },
            error: function(xhr, status, error) {
                if (!isProcessing) return;
                handleError(error);
            }
        });
    }

    function handleError(error) {
        console.error('Import error:', error);
        let errorMessage = error;
        
        // Try to parse error message if it's an object
        if (typeof error === 'object') {
            errorMessage = error.message || error.statusText || 'Unknown error occurred';
        }
        
        // Clean up the error message
        errorMessage = errorMessage.toString().replace(/^Error:\s*/, '');
        
        const retry = confirm(`An error occurred: ${errorMessage}\nWould you like to retry?`);
        if (retry && isProcessing) {
            processed = Math.max(0, processed - 10); // Go back one batch to ensure no records are missed
            setTimeout(processBatch, 1000);
        } else {
            isProcessing = false;
            $progressModal.hide();
            if (tempFilePath) {
                // Clean up the temporary file if it exists
                $.post(BulkAltUpdater.ajax_url, {
                    action: 'cleanup_temp_file',
                    temp_file_path: tempFilePath,
                    security: BulkAltUpdater.nonce
                });
            }
        }
    }

    // Add cancel capability
    $closeButton.off('click').on('click', function() {
        if (confirm('Are you sure you want to cancel the import?')) {
            isProcessing = false;
            $progressModal.hide();
        }
    });

    processBatch();
});

   // Close modal
   $closeButton.on('click', function () {
       $progressModal.hide();
   });

    // Close modal
    $closeButton.on('click', function () {
        $progressModal.hide();
    });
});
