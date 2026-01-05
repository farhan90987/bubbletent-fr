jQuery(function($){
    // open popup when clicking a day box
    const sound = document.getElementById('clickSound');
    $('#ac-grid').on('click', '.ac-box', function(){
    sound.currentTime = 0; // restart if clicked again
        $(this).addClass('active');
        var $box = $(this);
        if ($box.hasClass('disabled')) {
            return;
        }
        sound.play();
        var day = $(this).data('day');
        var popupText = $(this).data('popup-text');
        
        // Set the day value
        $('#ac-day').val(day);
        
        // Set the day-specific text
        var dayTextContainer = $('#ac-day-specific-text');
        if (popupText && popupText.trim() !== '') {
            dayTextContainer.html('<p>' + popupText + '</p>').show();
        } else {
            dayTextContainer.hide();
        }
        var day = $box.data('day');
        var q = $box.data('question') || '';
        $('#ac-day').val(day);
        $('#ac-question').val(q);
        $('#ac-day-title').text('Day ' + day);
        $('#ac-response').empty();
        setTimeout(() => {
            $('#ac-overlay, #ac-popup').fadeIn(150);
        }, 1000);
    });

    $('#ac-cancel').on('click', function(e){
        e.preventDefault();
        $('#ac-overlay, #ac-popup').fadeOut(150);
        $('.ac-box').removeClass('active');
    });

    // submit handler
    $('#ac-entry-form').on('submit', function(e){
        e.preventDefault();
        $('.loader-main').show();
        $('.ac-popup').hide();
        var $form = $(this);
        var data = {
            action: 'submit_advent_entry',
            nonce: AdventAjax.nonce,
            day: $form.find('[name="day"]').val(),
            first_name: $form.find('[name="first_name"]').val(),
            email: $form.find('[name="email"]').val(),
            last_name: $form.find('[name="last_name"]').val()
        };
        $.post(AdventAjax.ajaxurl, data, function(res){
            if (res.success) {
                $form[0].reset();
                alert(res.data.message);
            } else {
               alert(res.data.message);
            }
            $('.loader-main').hide();
            $('#ac-overlay').hide();
            $('.ac-box').removeClass('active');
        }).fail(function(){
            alert('Network error. Try again.');
            $('.loader-main').hide();
            $('#ac-overlay').hide();
            $('.ac-box').removeClass('active');
        });
    });
});
