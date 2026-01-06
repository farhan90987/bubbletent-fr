(function($) {	

    $.fn.progressBar = function ( givenValue, euro_left, currentGoal ) {
	  
        const $this = $(this);	
        
        function init( selector ) {
            
          const progressValue = selector.children().attr('aria-valuenow')
            
          $('.shipping-progress-bar').width(progressValue + "%")
          $('.shipping-progress-bar').html('<span></span>')
          
          $this.hasClass('md-progress') ? $('.shipping-progress-bar').addClass('md-progress-bar-text') : $('.shipping-progress-bar').children().addClass('progress-bar-text');     
         
        }
    
        function changeValue( selector, value ) {
            
              $('.shipping-progress-bar').removeClass('success active');
             
              $('.shipping-progress-bar').attr('aria-valuenow', value.toFixed(2) );
              init(selector);
            
              const maxGoals  = wc_shipping_bar.goals.length
              const item      = currentGoal >= maxGoals ? maxGoals - 1 : currentGoal
              const text      = currentGoal >= maxGoals ? formatPriceInText( wc_shipping_bar.success_text ) : formatPriceInText( wc_shipping_bar.goals[ currentGoal ]['text']  )
              const icon      = wc_shipping_bar.goals[ item ]['icon']
           
              if (value >= 100) {                  
                  
                    $('.wcjfw-shipping-bar').addClass('success');
                    $('.shipping-bar-plugin').addClass('success');
                    $('.shipping-bar-plugin').removeClass('empty almost');
                    $('.shipping-progress-bar').addClass('success');
                    $('.wcjfw-shipping-bar').find('.magic').addClass('activated');
    
                    $('.shipping-bar-plugin .shipping-bar-text').html( text );                    
              
              }else if (value == 0) {
                  
                    $('.wcjfw-shipping-bar').removeClass('success');
                    $('.shipping-bar-plugin').removeClass('success almost');
                    $('.shipping-bar-plugin').addClass('empty');
                    $('.shipping-progress-bar').addClass('empty');
                    $('.wcjfw-shipping-bar').find('.magic').removeClass('activated');
                    
                    $('.shipping-bar-plugin .shipping-bar-text').html('');
                    
                
              }else if (value > 60 ) {
                  
                  $('.wcjfw-shipping-bar').removeClass('success');
                  $('.shipping-bar-plugin').removeClass('success empty');
                  $('.shipping-bar-plugin').addClass('almost');
                  $('.shipping-progress-bar').addClass('active');
                  $('.wcjfw-shipping-bar').find('.magic').removeClass('activated');
                  
                  $('.shipping-bar-plugin .shipping-bar-text').html('');

                  $('.shipping-bar-plugin .shipping-bar-text').html( text );
                  
              
             }else{
                  
                    $('.wcjfw-shipping-bar').removeClass('success');
                    $('.shipping-bar-plugin').removeClass('success almost');
                    $('.shipping-bar-plugin').removeClass('empty');
                    $('.shipping-progress-bar').addClass('active');
                    $('.wcjfw-shipping-bar').find('.magic').removeClass('activated');
                    
                    $('.shipping-bar-plugin .shipping-bar-text').html( text );
             } 
             
             $('.shipping-bar-plugin .shipping-icon i').removeClassStartingWith('wooj-icon')
             $('.shipping-bar-plugin .shipping-icon i').addClass( icon )
        }   
        
        function formatPriceInText( text )
        {
            return text.replace(/{price_left}/gi, 
            '<b><div class="wc-timeline-product-price"><span class="timeline-price">' + parseFloat(euro_left).toFixed( wc_shipping_bar.decimals ) + '</span><span class="currency">'+wc_shipping_bar.currency +'</span></div></b>' )
        }

        changeValue( $this, givenValue );
      }

      $.fn.removeClassStartingWith = function (filter) {
            $(this).removeClass(function (index, className) {
                return (className.match(new RegExp("\\S*" + filter + "\\S*", 'g')) || []).join(' ')
            });
            return this;
        };

})( jQuery );

