jQuery(document).ready(function ($) {
    const $progressContainer = $('#progress-container');
    const $progressBar = $('#progress-bar');
    const $progressText = $('#progress-text');

    $('#process-file-button').on('click', function (e) {
        e.preventDefault();

        const fileInput = $('#alt_text_file')[0].files[0];
        if (!fileInput) {
            alert('Please upload a file.');
            return;
        }

        let processed = 0;
        let tempFilePath = null;

        $progressContainer.show();
        $progressBar.val(0);
        $progressText.text('Initializing...');

        // Ensure the percentage text is displayed
        if (!$progressBar.next('.progress-percentage').length) {
            $progressBar.after('<span class="progress-percentage" style="margin-left: 10px;">0%</span>');
        }

        // Function to update progress bar and percentage
        function updateProgressBar(current, total) {
            const percentage = Math.round((current / total) * 100);
            $progressBar.val(percentage);
            $progressBar.next('.progress-percentage').text(`${percentage}%`); // Update percentage text
            $progressText.text(`Processed ${current} of ${total} records (${percentage}%)`);
        }

        function processBatch() {
            const formData = new FormData();
            formData.append('action', 'process_image_alt_file');
            formData.append('security', BulkAltUpdater.nonce);
            formData.append('processed', processed);

            if (!tempFilePath) {
                formData.append('file', fileInput);
            } else {
                formData.append('temp_file_path', tempFilePath);
            }

            $.ajax({
                url: BulkAltUpdater.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        processed = response.data.progress;
                        const total = response.data.total;
                        tempFilePath = response.data.temp_file_path || tempFilePath;

                        // Update progress bar and percentage
                        updateProgressBar(processed, total);

                        if (!response.data.complete) {
                            setTimeout(processBatch, 500); // Delay to reduce server load
                        } else {
                            updateProgressBar(total, total); // Ensure progress bar shows 100% on completion
                            $progressText.text('Processing complete! 100%');
                            setTimeout(() => {
                                alert('Image alt text processing completed successfully!');
                                $progressContainer.hide();
                            }, 500);
                        }
                    } else {
                        alert(response.data.message || 'Error processing file.');
                    }
                },
                error: function () {
                    alert('An error occurred while processing the file.');
                },
            });
        }

        processBatch();
    });
});
