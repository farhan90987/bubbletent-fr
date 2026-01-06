(function( $ ) {
	//'use strict';

	function wt_order_number_fields()
	{
		var vl=$('#wt_sequence_order_number_format').val();
		var prefix_tr=$('#wt_sequence_order_number_prefix').parents('tr');
		var prefix_date_tr=$('#wt_sequence_order_date_prefix').parents('tr');
		var title_tr=$('#wt_sequencial_settings_page-description');
		var search_tr=$('#wt_custom_order_number_search').parents('tr');
		var pro_subtitle_tr=$('#wt_sequentials_pro_subtitle-description');
		prefix_tr.hide();
		prefix_date_tr.hide();
		title_tr.show().find('p').css({'font-size':'14px'});
		$('#wt_sequencial_documentation-description').css({'border-bottom': '1px dashed rgb(204, 204, 204)'});
		search_tr.css({'border-bottom': '1px dashed rgb(204, 204, 204)'});
		$('.form-table th label').css({'float':'left','width':'100%'});

		if(vl=='[prefix][number]')
		{
			prefix_tr.show();
		}
		if(vl=='[date][number]')
		{
			prefix_date_tr.show().find('p').css({'max-width':'80%'});
		}
		if(vl=='[prefix][date][number]')
		{
			prefix_tr.show();
			prefix_date_tr.show().find('p').css({'max-width':'80%'});
		}
	}

	function wt_sample_order_number()
	{
		var format=$('#wt_sequence_order_number_format').val();
		var start_num = $('#wt_sequence_order_number_start').val();
		var prefix = $('#wt_sequence_order_number_prefix').val();
		var date_value = $('#wt_sequence_order_date_prefix').val();
		var order_num_width = $('#wt_sequence_order_number_padding').val();
		if( start_num == '') {
			start_num = 1;
		}
		var padding_count = order_num_width - start_num.length;
		var padding = '';
        if (padding_count > 0) 
        {
            for (i = 0; i < padding_count; i++)
            {
                padding += '0';
            }
            var start_num = padding+start_num;
        }
        var date_str =date_value.replace(/[\[\]s]/g, '');
		var date=wt_seq_number_sample.wt_seq_php_date(date_str);
		var text = wt_seq_settings.msgs.prev;
		var start_num_len = $('#wt_sequence_order_number_start').val().length;
		if(start_num_len > order_num_width)
		{
			$('#wt_sequence_order_number_padding').val(start_num_len);
		}
		if(start_num && format =='[number]')
		{
			$('#wt_sequence_order_number_start').next().html('<span style="color:#646970; font-size :14px; font-weight:500;">'+text+start_num+'</span>');
		}
		if( start_num && prefix && format =='[prefix][number]')
		{
			$('#wt_sequence_order_number_start').next().html('<span style="color:#646970; font-size :14px; font-weight:500;">'+text+prefix+start_num+'</span>');
		}
		if(date && start_num && format =='[date][number]')
		{
			$('#wt_sequence_order_number_start').next().html('<span style="color:#646970; font-size :14px; font-weight:500;">'+text+date+start_num+'</span>');
		}
		if(date && start_num && prefix && format =='[prefix][date][number]')
		{
			$('#wt_sequence_order_number_start').next().html('<span style="color:#646970; font-size :14px; font-weight:500;">'+text+prefix+date+start_num+'</span>');
		}	
	}
	wt_seq_number_sample=
	{
		Set:function()
		{
			var timer = null;
			$('#wt_sequence_order_number_start').keydown(function(){
			       clearTimeout(timer); 
			       timer = setTimeout(wt_sample_order_number, 1000)
			});
			$('#wt_sequence_order_number_padding').keydown(function(){
			       clearTimeout(timer); 
			       timer = setTimeout(wt_sample_order_number, 1000)
			});
			$('#wt_sequence_order_number_prefix').keydown(function(){
			       clearTimeout(timer); 
			       timer = setTimeout(wt_sample_order_number, 1000)
			});
			$('#wt_sequence_order_date_prefix').keydown(function(){
			       clearTimeout(timer); 
			       timer = setTimeout(wt_sample_order_number, 1000)
			});
			$('[name="wt_sequence_order_number_start"],[name="wt_sequence_order_number_padding"]').on('change',function(){
				wt_sample_order_number();
			});
		},
		wt_seq_php_date:function(format, timestamp)
		{
			let jsdate, f
			// Keep this here (works, but for code commented-out below for file size reasons)
			// var tal= [];
			const txtWords = [
		    'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
		    'January', 'February', 'March', 'April', 'May', 'June',
		    'July', 'August', 'September', 'October', 'November', 'December'
		  ]
			// trailing backslash -> (dropped)
			// a backslash followed by any character (including backslash) -> the character
			// empty string -> empty string
			const formatChr = /\\?(.?)/gi
			const formatChrCb = function (t, s) {
				return f[t] ? f[t]() : s
		  	}
		  	const _pad = function (n, c) {
			    n = String(n)
			    while (n.length < c) {
			      n = '0' + n
			    }
		    	return n
		  	}
		  	f = {
		    // Day
		    d: function () {
		      // Day of month w/leading 0; 01..31
		      return _pad(f.j(), 2)
		    },
		    D: function () {
		      // Shorthand day name; Mon...Sun
		      return f.l()
		        .slice(0, 3)
		    },
		    j: function () {
		      // Day of month; 1..31
		      return jsdate.getDate()
		    },
		    l: function () {
		      // Full day name; Monday...Sunday
		      return txtWords[f.w()] + 'day'
		    },
		    N: function () {
		      // ISO-8601 day of week; 1[Mon]..7[Sun]
		      return f.w() || 7
		    },
		    S: function () {
		      // Ordinal suffix for day of month; st, nd, rd, th
		      const j = f.j()
		      let i = j % 10
		      if (i <= 3 && parseInt((j % 100) / 10, 10) === 1) {
		        i = 0
		      }
		      return ['st', 'nd', 'rd'][i - 1] || 'th'
		    },
		    w: function () {
		      // Day of week; 0[Sun]..6[Sat]
		      return jsdate.getDay()
		    },
		    z: function () {
		      // Day of year; 0..365
		      const a = new Date(f.Y(), f.n() - 1, f.j())
		      const b = new Date(f.Y(), 0, 1)
		      return Math.round((a - b) / 864e5)
		    },

		    // Week
		    W: function () {
		      // ISO-8601 week number
		      const a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3)
		      const b = new Date(a.getFullYear(), 0, 4)
		      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2)
		    },

		    // Month
		    F: function () {
		      // Full month name; January...December
		      return txtWords[6 + f.n()]
		    },
		    m: function () {
		      // Month w/leading 0; 01...12
		      return _pad(f.n(), 2)
		    },
		    M: function () {
		      // Shorthand month name; Jan...Dec
		      return f.F()
		        .slice(0, 3)
		    },
		    n: function () {
		      // Month; 1...12
		      return jsdate.getMonth() + 1
		    },
		    t: function () {
		      // Days in month; 28...31
		      return (new Date(f.Y(), f.n(), 0))
		        .getDate()
		    },

		    // Year
		    L: function () {
		      // Is leap year?; 0 or 1
		      const j = f.Y()
		      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0
		    },
		    o: function () {
		      // ISO-8601 year
		      const n = f.n()
		      const W = f.W()
		      const Y = f.Y()
		      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0)
		    },
		    Y: function () {
		      // Full year; e.g. 1980...2010
		      return jsdate.getFullYear()
		    },
		    y: function () {
		      // Last two digits of year; 00...99
		      return f.Y()
		        .toString()
		        .slice(-2)
		    },

		    // Time
		    a: function () {
		      // am or pm
		      return jsdate.getHours() > 11 ? 'pm' : 'am'
		    },
		    A: function () {
		      // AM or PM
		      return f.a()
		        .toUpperCase()
		    },
		    B: function () {
		      // Swatch Internet time; 000..999
		      const H = jsdate.getUTCHours() * 36e2
		      // Hours
		      const i = jsdate.getUTCMinutes() * 60
		      // Minutes
		      // Seconds
		      const s = jsdate.getUTCSeconds()
		      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3)
		    },
		    g: function () {
		      // 12-Hours; 1..12
		      return f.G() % 12 || 12
		    },
		    G: function () {
		      // 24-Hours; 0..23
		      return jsdate.getHours()
		    },
		    h: function () {
		      // 12-Hours w/leading 0; 01..12
		      return _pad(f.g(), 2)
		    },
		    H: function () {
		      // 24-Hours w/leading 0; 00..23
		      return _pad(f.G(), 2)
		    },
		    i: function () {
		      // Minutes w/leading 0; 00..59
		      return _pad(jsdate.getMinutes(), 2)
		    },
		    s: function () {
		      // Seconds w/leading 0; 00..59
		      return _pad(jsdate.getSeconds(), 2)
		    },
		    u: function () {
		      // Microseconds; 000000-999000
		      return _pad(jsdate.getMilliseconds() * 1000, 6)
		    },

		    // Timezone
		    e: function () {
		      // Timezone identifier; e.g. Atlantic/Azores, ...
		      // The following works, but requires inclusion of the very large
		      // timezone_abbreviations_list() function.
		      /*              return that.date_default_timezone_get();
		       */
		      const msg = 'Not supported (see source code of date() for timezone on how to add support)'
		      throw new Error(msg)
		    },
		    I: function () {
		      // DST observed?; 0 or 1
		      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
		      // If they are not equal, then DST is observed.
		      const a = new Date(f.Y(), 0)
		      // Jan 1
		      const c = Date.UTC(f.Y(), 0)
		      // Jan 1 UTC
		      const b = new Date(f.Y(), 6)
		      // Jul 1
		      // Jul 1 UTC
		      const d = Date.UTC(f.Y(), 6)
		      return ((a - c) !== (b - d)) ? 1 : 0
		    },
		    O: function () {
		      // Difference to GMT in hour format; e.g. +0200
		      const tzo = jsdate.getTimezoneOffset()
		      const a = Math.abs(tzo)
		      return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4)
		    },
		    P: function () {
		      // Difference to GMT w/colon; e.g. +02:00
		      const O = f.O()
		      return (O.substr(0, 3) + ':' + O.substr(3, 2))
		    },
		    T: function () {
		      // The following works, but requires inclusion of the very
		      // large timezone_abbreviations_list() function.
		      /*              var abbr, i, os, _default;
		      if (!tal.length) {
		        tal = that.timezone_abbreviations_list();
		      }
		      if ($locutus && $locutus.default_timezone) {
		        _default = $locutus.default_timezone;
		        for (abbr in tal) {
		          for (i = 0; i < tal[abbr].length; i++) {
		            if (tal[abbr][i].timezone_id === _default) {
		              return abbr.toUpperCase();
		            }
		          }
		        }
		      }
		      for (abbr in tal) {
		        for (i = 0; i < tal[abbr].length; i++) {
		          os = -jsdate.getTimezoneOffset() * 60;
		          if (tal[abbr][i].offset === os) {
		            return abbr.toUpperCase();
		          }
		        }
		      }
		      */
		      return 'UTC'
		    },
		    Z: function () {
		      // Timezone offset in seconds (-43200...50400)
		      return -jsdate.getTimezoneOffset() * 60
		    },

		    // Full Date/Time
		    c: function () {
		      // ISO-8601 date.
		      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb)
		    },
		    r: function () {
		      // RFC 2822
		      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb)
		    },
		    U: function () {
		      // Seconds since UNIX epoch
		      return jsdate / 1000 | 0
		    }
		  }

	  const _date = function (format, timestamp) {
	    jsdate = (timestamp === undefined ? new Date() // Not provided
	      : (timestamp instanceof Date) ? new Date(timestamp) // JS Date()
	          : new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
	    )
	    return format.replace(formatChr, formatChrCb)
	  }

	  return _date(format, timestamp)
		}
	}
	wt_seq_popup={
	Set:function()
	{
		$('[name="wt_sequence_order_number_format"]').on('change',function(){

			wt_order_number_fields();
			wt_sample_order_number();
		});
		this.regPopupOpen();
		this.regPopupClose();
		jQuery('body').prepend('<div class="wt_seq_overlay"></div>');
		wt_order_number_fields();
		wt_sample_order_number();
		$('.wt_seq_num_frmt_hlp_btn').on('click', function(){
			var trgt_field=$(this).attr('data-wf-trget');
			$('.wt_seq_num_frmt_hlp').attr('data-wf-trget',trgt_field);
			wt_seq_popup.showPopup($('.wt_seq_num_frmt_hlp'));
		});

		$('.wt_seq_num_frmt_append_btn').on('click', function(){
			var trgt_elm_name=$(this).parents('.wt_seq_num_frmt_hlp').attr('data-wf-trget');
			var trgt_elm=$('[name="'+trgt_elm_name+'"]');
			var exst_vl=trgt_elm.val();
			var cr_vl=$(this).text();
			trgt_elm.val(exst_vl+cr_vl);
			wt_seq_popup.hidePopup();
			wt_sample_order_number();
		});
		var free_order_tr=$('#wt_sequential_free_orders').parents('tr');
		var increment_tr=$('#wt_sequence_increment_counter').parents('tr');
		var reset_tr=$('#wt_sequential_reset_counter').parents('tr');
		var pro_version_text = wt_seq_settings.msgs.pro_text;
		free_order_tr.find('th').append(pro_version_text);
		increment_tr.find('th').append(pro_version_text);
		reset_tr.find('th').append(pro_version_text);

	},
	regPopupOpen:function()
	{
		jQuery('[data-wt_seq_popup]').on('click',function(){
			var elm_class=jQuery(this).attr('data-wt_seq_popup');
			var elm=jQuery('.'+elm_class);
			if(elm.length>0)
			{
				wt_seq_popup.showPopup(elm);
			}
		});
	},
	showPopup:function(popup_elm)
	{
		var pw=popup_elm.outerWidth();
		var wh=jQuery(window).height();
		var ph=wh-150;
		popup_elm.css({'margin-left':((pw/2)*-1),'display':'block','top':'20px'}).animate({'top':'50px'});
		popup_elm.find('.wt_seq_num_popup_body').css({'max-height':ph+'px','overflow':'auto'});
		jQuery('.wt_seq_overlay').show();
	},
	hidePopup:function()
	{
		jQuery('.wt_seq_num_popup_close').click();
	},
	regPopupClose:function(popup_elm)
	{
		jQuery(document).keyup(function(e){
			if(e.keyCode==27)
			{
				wt_seq_popup.hidePopup();
			}
		});
		jQuery('.wt_seq_num_popup_close, .wt_seq_num_popup_cancel').unbind('click').on('click',function(){
			jQuery('.wt_seq_overlay, .wt_seq_num_popup').hide();
		});
	},
}

})( jQuery );
jQuery(document).ready(function(){

	function handleScrollEvent(e) {
		e.preventDefault();
	}
	
	jQuery('form')
		.on('focus', 'input[name=wt_sequence_order_number_start], input[name=wt_sequence_order_number_padding]', function (e) {
			jQuery(this).on('wheel.disableScroll', handleScrollEvent);
		})
		.on('blur', 'input[name=wt_sequence_order_number_start], input[name=wt_sequence_order_number_padding]', function (e) {
			jQuery(this).off('wheel.disableScroll');
		});
		
	wt_seq_popup.Set();
	wt_seq_number_sample.Set();
});