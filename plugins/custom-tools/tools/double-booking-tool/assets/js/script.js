jQuery(document).ready(function($) {
    console.log('Double Booking Tool script loaded');
    console.log('smoobu_ajax object:', smoobu_ajax);

    // ===== EXISTING FILTER FUNCTIONALITY =====
    $('#status_filter, #search_filter, #double_booking_filter').on('change keyup', function() {
        var status = $('#status_filter').val();
        var search = $('#search_filter').val().toLowerCase();
        var doubleOnly = $('#double_booking_filter').is(':checked');
        
        $('#orders-table tbody tr').each(function() {
            var row = $(this);
            var rowStatus = row.data('status');
            var isDouble = row.data('double');
            var rowText = row.text().toLowerCase();
            
            var statusMatch = status === '' || rowStatus === status;
            var searchMatch = search === '' || rowText.indexOf(search) > -1;
            var doubleMatch = !doubleOnly || isDouble == 1;
            
            if (statusMatch && searchMatch && doubleMatch) {
                row.show();
            } else {
                row.hide();
            }
        });
    });
    
    // ===== EXISTING EXPORT FUNCTIONALITY =====
 // ===== ENHANCED EXPORT FUNCTIONALITY =====
$('#export_btn').on('click', function() {
    // Get table data with proper column mapping
    var data = [];
    var headers = [];
    
    // Define the export column order (matching the hidden columns we added)
    var exportColumns = [
        'Order ID', 'Order Date', 'Customer Name', 'Phone', 'Email', 
        'Order Status', 'Check-in Date', 'Check-out Date', 
        'Smoobu Status', 'Double Booking', 'Order Link'
    ];
    
    data.push(exportColumns);
    
    $('#orders-table tbody tr:visible').each(function() {
        var row = [];
        var $row = $(this);
        
        // Order ID - get from the link text
        row.push($row.find('td:eq(0)').text().trim());
        
        // Order Date
        row.push($row.find('td:eq(1)').text().trim());
        
        // Customer Name
        row.push($row.find('td:eq(2)').text().trim());
        
        // Phone (hidden column)
        row.push($row.find('td:eq(3)').text().trim());
        
        // Email (hidden column)
        row.push($row.find('td:eq(4)').text().trim());
        
        // Order Status - get the text without HTML
        row.push($row.find('td:eq(5)').text().trim());
        
        // Check-in Date - get the actual text content
        var checkinContent = $row.find('td:eq(6)').clone();
        checkinContent.find('strong').contents().unwrap(); // Remove strong tags but keep text
        row.push(checkinContent.text().trim());
        
        // Check-out Date
        row.push($row.find('td:eq(7)').text().trim());
        
        // Smoobu Status - get the text content
        row.push($row.find('td:eq(8)').text().trim());
        
        // Double Booking - get the text content
        var doubleBookingContent = $row.find('td:eq(9)').clone();
        doubleBookingContent.find('.double-booking-badge').contents().unwrap(); // Remove span but keep text
        row.push(doubleBookingContent.text().trim());
        
        // Order Link (hidden column) - get the actual URL
        row.push($row.find('td:eq(10)').text().trim());
        
        data.push(row);
    });
    
    // Create worksheet
    var ws = XLSX.utils.aoa_to_sheet(data);
    
    // Add hyperlinks to Order ID and Order Link columns
    if (ws['!ref']) {
        var range = XLSX.utils.decode_range(ws['!ref']);
        for (var R = range.s.r + 1; R <= range.e.r; ++R) {
            // Order ID column (column A, index 0) - link to order
            var orderIdCell = XLSX.utils.encode_cell({r: R, c: 0});
            var orderLinkCell = XLSX.utils.encode_cell({r: R, c: 10}); // Order Link column (K)
            
            if (ws[orderLinkCell] && ws[orderLinkCell].v) {
                // Create hyperlink for Order ID
                if (!ws[orderIdCell].l) ws[orderIdCell].l = [];
                ws[orderIdCell].l.push({Target: ws[orderLinkCell].v, Tooltip: 'View Order'});
            }
        }
    }
    
    // Create workbook
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Double Bookings");
    
    // Generate file and download
    var date = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, "double_bookings_" + date + ".xlsx");
});

    // ===== NEW SMOOBU MODAL FUNCTIONALITY =====
function initSmoobuModal() {
    console.log('Initializing Smoobu modal');
    
    // Create modal HTML if it doesn't exist
    if ($('#smoobu-modal').length === 0) {
        console.log('Creating modal HTML');
        const modalHTML = `
            <div id="smoobu-modal" class="smoobu-modal" style="display: none;">
                <div class="smoobu-modal-content">
                    <div class="smoobu-modal-header">
                        <h3>Smoobu Booking Details</h3>
                        <button class="smoobu-close">&times;</button>
                    </div>
                    <div class="smoobu-modal-body">
                        <div class="smoobu-modal-scrollable">
                            <div class="smoobu-loading">
                                <div class="smoobu-loading-spinner"></div>
                                <p>Loading booking details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHTML);
    }

    const $modal = $('#smoobu-modal');
    const $modalScrollable = $modal.find('.smoobu-modal-scrollable'); // FIXED: Target scrollable container

    // Close modal events
    $modal.on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('smoobu-close')) {
            closeModal();
        }
    });

    // ESC key to close
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $modal.is(':visible')) {
            closeModal();
        }
    });

    function closeModal() {
        $modal.fadeOut(200);
        // Clear content after animation
        setTimeout(() => {
            $modalScrollable.html(`
                <div class="smoobu-loading">
                    <div class="smoobu-loading-spinner"></div>
                    <p>Loading booking details...</p>
                </div>
            `);
        }, 200);
    }

    // Click handler for Smoobu status cells
    $(document).on('click', '.smoobu-status', function(e) {
        console.log('Smoobu status clicked!');
        e.preventDefault();
        e.stopPropagation();
        
        const $statusCell = $(this);
        const bookingId = $statusCell.closest('tr').data('smoobu-booking-id');
        
        console.log('Clicked element:', $statusCell);
        console.log('Booking ID found:', bookingId);
        
        if (!bookingId || bookingId === '0' || bookingId === '') {
            alert('No Smoobu booking ID found for this order.');
            return;
        }
        
        openModal(bookingId);
    });

    function openModal(bookingId) {
        console.log('Opening modal for booking ID:', bookingId);
        $modal.fadeIn(200);
        
        // Fetch fresh data from Smoobu
        $.ajax({
            url: smoobu_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_smoobu_booking_details',
                booking_id: bookingId,
                nonce: smoobu_ajax.nonce
            },
            beforeSend: function() {
                $modalScrollable.html(`
                    <div class="smoobu-loading">
                        <div class="smoobu-loading-spinner"></div>
                        <p>Loading booking details...</p>
                    </div>
                `);
            },
            success: function(response) {
                console.log('AJAX success:', response);
                if (response.success) {
                    displayBookingDetails(response.data);
                } else {
                    showError(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', error);
                showError('Failed to load booking details: ' + error);
            }
        });
    }
    
    function displayBookingDetails(data) {
        console.log('Displaying booking details:', data);
        if (data.error) {
            showError(data.error);
            return;
        }
        
        let html = '';
        
        // Basic Information Section
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Basic Information</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.basic_info).forEach(key => {
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${data.basic_info[key]}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        // Accommodation Section
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Accommodation</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.accommodation).forEach(key => {
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${data.accommodation[key]}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        // Guest Information Section
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Guest Information</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.guest_info).forEach(key => {
            const value = data.guest_info[key];
            const isEmail = key === 'Email' && value !== 'N/A';
            const displayValue = isEmail ? `<a href="mailto:${value}" class="smoobu-url">${value}</a>` : value;
            
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${displayValue}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        // Check-in/Check-out Times
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Check-in & Check-out Times</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.check_times).forEach(key => {
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${data.check_times[key]}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        // Financial Information
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Financial Information</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.financial).forEach(key => {
            const value = data.financial[key];
            const isPaid = key.includes('Paid') && value === 'Yes';
            const displayValue = isPaid ? `<span class="smoobu-success">${value}</span>` : value;
            
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${displayValue}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        // Additional Information
        html += `
            <div class="smoobu-section">
                <div class="smoobu-section-header">Additional Information</div>
                <div class="smoobu-section-content">
        `;
        Object.keys(data.additional).forEach(key => {
            let value = data.additional[key];
            let displayValue = value;
            
            if (key === 'Guest App URL' && value !== 'N/A') {
                displayValue = `<a href="${value}" target="_blank" class="smoobu-url">${value}</a>`;
            } else if (key === 'Notice' && value === 'No special notes') {
                displayValue = `<span class="smoobu-notice">${value}</span>`;
            }
            
            html += `
                <div class="smoobu-detail-row">
                    <div class="smoobu-detail-label">${key}:</div>
                    <div class="smoobu-detail-value">${displayValue}</div>
                </div>
            `;
        });
        html += `</div></div>`;
        
        $modalScrollable.html(html); // FIXED: Target scrollable container
    }
    
    function showError(message) {
        // FIXED: Target scrollable container
$modalScrollable.html(`
    <div class="smoobu-loading">
        <div class="smoobu-loading-spinner"></div>
        <p>Loading booking details...</p>
    </div>
`);

    }
}
    // Initialize modal when page loads
    initSmoobuModal();

    // Test click handler separately
    $('body').on('click', '.smoobu-status', function() {
        console.log('Direct click handler fired');
    });

    console.log('Smoobu status elements found:', $('.smoobu-status').length);
    console.log('First smoobu status element:', $('.smoobu-status').first());
});