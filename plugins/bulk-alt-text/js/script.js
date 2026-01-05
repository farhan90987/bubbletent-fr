console.log("Bulk Alt Updater script loaded on this page.");
jQuery(document).ready(function ($) {
    let previewData = [];
    let currentBatchIndex = 0;
    let currentPage = 1; // Start with the first page for preview
    let totalPages = 0; // Total pages for preview (set dynamically)

    // Suppress admin notices dynamically
    function suppressAdminNotices() {
        $('div.notice').remove(); // Remove any existing notices
    }

    // Initial suppression
    suppressAdminNotices();

    // Re-check every 500ms to suppress dynamically added notices
    setInterval(suppressAdminNotices, 500);

    // Function to load preview data with pagination
    function loadPreview(page = 1) {
        const fileInput = $('#alt_text_file')[0].files[0];
        if (!fileInput) {
            alert('Please select a file.');
            return;
        }

        const formData = new FormData();
        formData.append('alt_text_file', fileInput);
        formData.append('action', 'preview_alt_updates');
        formData.append('page', page);
        formData.append('security', BulkAltUpdater.nonce);

        $('#progress-container').show(); // Show the progress bar
        $('#progress-bar').val(0); // Reset progress bar value
        $('#progress-text').text(`Loading preview page ${page}...`);

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    previewData = response.data.preview;
                    totalPages = response.data.total_pages;
                    currentPage = response.data.current_page;

                    const tableBody = $('#preview-table tbody');
                    tableBody.empty();

                    previewData.forEach((item) => {
                        tableBody.append(`
                            <tr>
                                <td>${item.url}</td>
                                <td>${item.current_alt_text}</td>
                                <td>${item.new_alt_text}</td>
                            </tr>
                        `);
                    });

                    $('#progress-bar').val((currentPage / totalPages) * 100); // Update progress
                    $('#progress-text').text(`Loaded page ${currentPage} of ${totalPages}`);

                    if (currentPage === totalPages) {
                        $('#progress-container').hide(); // Hide progress bar when done
                    }

                    $('#preview-container').show();
                    $('#start-processing').show();

                    updatePaginationControls();
                } else {
                    alert(response.data.message || 'Error processing file.');
                }
            },
            error: function () {
                alert('An error occurred during the preview process.');
            },
        });
    }

    // Update pagination controls for preview
    function updatePaginationControls() {
        const paginationControls = $('#pagination-controls');
        paginationControls.empty();

        if (totalPages > 1) {
            if (currentPage > 1) {
                paginationControls.append('<button class="button prev-page">Previous</button>');
            }
            if (currentPage < totalPages) {
                paginationControls.append('<button class="button next-page">Next</button>');
            }
        }
    }

    // Handle pagination button clicks
    $('#pagination-controls').on('click', '.prev-page', function () {
        if (currentPage > 1) {
            loadPreview(currentPage - 1);
        }
    });

    $('#pagination-controls').on('click', '.next-page', function () {
        if (currentPage < totalPages) {
            loadPreview(currentPage + 1);
        }
    });

    // Initial form submission to start preview
    $('#upload-form').on('submit', function (e) {
        e.preventDefault();
        loadPreview(1); // Load the first page
    });

    // Function to process batches of data
    function processBatch(batches, index) {
        if (index >= batches.length) {
            $('#progress-text').text('Processing complete!');
            $('#progress-bar').val(100);
            return;
        }

        const batch = batches[index];
        const totalBatches = batches.length;

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: {
                action: 'update_batch',
                batch: batch,
                security: BulkAltUpdater.nonce,
            },
            success: function (response) {
                if (response.success) {
                    currentBatchIndex++;
                    $('#progress-text').text(`Processing batch ${currentBatchIndex + 1} of ${totalBatches}`);
                    $('#progress-bar').val(((currentBatchIndex + 1) / totalBatches) * 100);

                    processBatch(batches, currentBatchIndex); // Process next batch
                } else {
                    alert(response.data.message || 'Error processing batch.');
                }
            },
            error: function () {
                alert('An error occurred during batch processing.');
            },
        });
    }

    // Handle the "Start Processing" button click
    $('#start-processing').on('click', function () {
        if (!previewData.length) {
            alert('No preview data available for processing.');
            return;
        }

        const batchSize = 50; // Number of rows per batch for processing
        const batches = chunkArray(previewData, batchSize);
        currentBatchIndex = 0; // Reset batch index

        $('#progress-container').show();
        $('#progress-bar').val(0);
        $('#progress-text').text('Processing batch 1 of ' + batches.length);

        processBatch(batches, currentBatchIndex);
    });

    // Utility function to split an array into chunks
    function chunkArray(array, size) {
        const results = [];
        for (let i = 0; i < array.length; i += size) {
            results.push(array.slice(i, i + size));
        }
        return results;
    }

    // Handle "View Log" button click
$(document).on('click', '.view-log', function () {
    const logFile = $(this).data('log');
    console.log("View Log clicked for:", logFile); // Debugging log

    if (!logFile) {
        alert('Log file name is missing.');
        return;
    }

    $.ajax({
        url: BulkAltUpdater.ajax_url,
        type: 'POST',
        data: {
            action: 'view_log',
            log_file: logFile,
            security: BulkAltUpdater.nonce,
        },
        success: function (response) {
            if (response.success) {
                console.log("Log file content retrieved successfully."); // Debugging log

                // Create the modal
                const modal = `
                    <div id="log-modal" class="log-modal">
                        <div class="log-modal-content">
                            <button id="close-log-modal" class="button close-modal-btn" aria-label="Close">&times;</button>
                            <h2>Log: ${logFile}</h2>
                            <pre style="white-space: pre-wrap; word-wrap: break-word;">${response.data.content}</pre>
                        </div>
                    </div>
                `;

                // Remove any existing modals and append the new one
                $('#log-modal').remove(); // Prevent duplicate modals
                $('body').append(modal);

                // Style the modal dynamically
                $('.log-modal').css({
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'rgba(0, 0, 0, 0.5)',
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center',
                    zIndex: 1000,
                });

                $('.log-modal-content').css({
                    backgroundColor: '#fff',
                    padding: '20px',
                    borderRadius: '8px',
                    boxShadow: '0 4px 8px rgba(0, 0, 0, 0.2)',
                    maxWidth: '800px', // Similar to main page width
                    width: '90%',
                    maxHeight: '80%',
                    overflowY: 'auto',
                    position: 'relative', // Required for positioning the close button
                });

                // Style for close button
                $('.close-modal-btn').css({
                    position: 'absolute',
                    top: '10px',
                    right: '10px',
                    backgroundColor: 'transparent',
                    border: 'none',
                    fontSize: '24px',
                    fontWeight: 'bold',
                    cursor: 'pointer',
                    color: '#333',
                });

                $('.close-modal-btn').hover(
                    function () {
                        $(this).css({ color: '#007cba' });
                    },
                    function () {
                        $(this).css({ color: '#333' });
                    }
                );
            } else {
                console.error("Error viewing log:", response.data.message); // Debugging log
                alert(response.data.message || 'Failed to retrieve the log file.');
            }
        },
        error: function () {
            console.error("AJAX error occurred while viewing the log."); // Debugging log
            alert('An error occurred while trying to view the log file.');
        },
    });
});

// Ensure the modal closes when clicking the close button or outside the modal
$(document).on('click', '#close-log-modal', function () {
    console.log("Close button clicked."); // Debugging log
    $('#log-modal').remove(); // Remove modal
});

$(document).on('click', '.log-modal', function (event) {
    if ($(event.target).is('.log-modal')) {
        console.log("Clicked outside the modal content."); // Debugging log
        $('#log-modal').remove(); // Close modal if clicked outside
    }
});


    // Handle "Delete Log" button click
    $(document).on('click', '.delete-log', function () {
        const logFile = $(this).data('log');

        if (!confirm('Are you sure you want to delete this log file?')) {
            return;
        }

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_log',
                log_file: logFile,
                security: BulkAltUpdater.nonce,
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message || 'Log file deleted successfully.');
                    location.reload(); // Refresh the page to update the logs list
                } else {
                    alert(response.data.message || 'Error deleting log file.');
                }
            },
            error: function () {
                alert('An error occurred while deleting the log file.');
            },
        });
    });
});


jQuery(document).ready(function ($) {
    $('#export-images-button').on('click', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to export all images? This might take some time.')) {
            return;
        }

        $.ajax({
            url: BulkAltUpdater.ajax_url,
            type: 'POST',
            data: {
                action: 'export_image_alt_texts',
                security: BulkAltUpdater.nonce,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.file_url;
                } else {
                    alert('Export failed: ' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                alert('An error occurred while exporting images.');
                console.error('Export AJAX Error:', error);
            },
        });
    });
});
