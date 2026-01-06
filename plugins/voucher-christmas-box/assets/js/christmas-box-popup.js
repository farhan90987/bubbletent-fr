jQuery(document).ready(function ($) {
    console.log('[Christmas Box Plugin] Initializing popup script.');

    // AJAX request to fetch the popup HTML
    $.ajax({
        url: christmasBoxPopup.ajax_url,
        method: 'POST',
        data: { action: 'get_christmas_box_popup' },
        success: function (response) {
            if (response.success && response.data.popup_html) {
                $('body').append(response.data.popup_html);
                $('#christmas-box-popup').fadeIn();
                console.log('[Christmas Box Plugin] Pop-up content received and displayed.');

                // Trigger popup only if eligible products exist
                if (response.data.trigger_popup) {
                    $('#christmas-box-popup').fadeIn();
                } else {
                    console.log('[Christmas Box Plugin] No eligible product in cart. Popup not displayed.');
                    $('#christmas-box-popup').remove();
                }
            }
        },
        error: function () {
            console.error('[Christmas Box Plugin] Failed to fetch pop-up content.');
        },
    });

    // Event listener for closing the popup
    $(document).on('click', '#close-popup-btn', function () {
        $('#christmas-box-popup').fadeOut(function () {
            $(this).remove(); // Ensure the popup is removed from DOM
        });
    });

    // Event listener for adding the Christmas Box to the cart
    $(document).on('click', '#add-christmas-box-btn', function (e) {
        e.preventDefault(); // Prevent default action
        const redirectUrl = $(this).attr('href');
        console.log('[Christmas Box Plugin] Redirecting to:', redirectUrl);
        window.location.href = redirectUrl; // Redirect to cart page
    });
});
