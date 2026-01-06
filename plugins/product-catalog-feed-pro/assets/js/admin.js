jQuery(function ($) {

    function initPage() {
        if ((typeof wpwoof_current_page !== 'undefined')) {

            switch (wpwoof_current_page) {
                case 'dashboard':
                    if (typeof wpwoof_editorsId4init !== 'undefined' && wpwoof_editorsId4init.length) {
                        for (var i = 0; i < wpwoof_editorsId4init.length; i++) {
                            initWPEditor(wpwoof_editorsId4init[i]);
                        }
                    }
                    $(document).on('change', 'textarea[id^=wpwoof-editor-]', function () {
                        $('input[name="extra[' + $(this).attr('id').substring(14) + '][custom_value]"').val($(this).val()).change();
                    });
                    setInterval(() => WPEditor_detectChanges(), 2000);
                    file_name_placeholder_auto_fill_from_feed_name();
                    // show global button "Hide localized feeds" only if localized feeds present on page
                    if ($('.wpwoof-localized-switcher').length) {
                        $('.wpwoof-localized-switcher-global').css('display', 'inline-flex');
                    }
                    $(document).on('click', ".show-feed-all-categories", function (e) {
                        e.preventDefault();

                        іncludeCategoriesString = $(this).data('categories')
                        excludeCategoriesString = $(this).data('categories_excluded')

                        $('#feed-all-categories-dialog-content').html(іncludeCategoriesString + '; <s>' + excludeCategoriesString + '</s>');

                        $("#feed-all-categories-dialog-content").dialog({
                            'dialogClass': 'wp-dialog',
                            'title': 'Categories',
                            'modal': true,
                            'autoOpen': true,
                            'closeOnEscape': true,
                        });
                    });
                    enable_cost_of_goods_sold_in_feed();

                    break;
                case 'editProduct':
                    $(document).on('submit', "form#post", prepareCustomEditorToSubmit);
                    $('body').on('woocommerce_variations_save_variations_button', prepareCustomEditorToSubmit);
                    $('body').on('wc-enhanced-select-init', function () {
                        if (typeof wpwoof_editorsId4initVar !== 'undefined' && wpwoof_editorsId4initVar.length) {
                            for (var loop = 0; loop < wpwoof_editorsId4initVar.length; loop++) {
                                if (wpwoof_editorsId4initVar[loop].length) {
                                    for (var i = 0; i < wpwoof_editorsId4initVar[loop].length; i++) {
                                        initWPEditor(wpwoof_editorsId4initVar[loop][i]);
                                    }
                                }
                            }
                        }
                    });
                    if (typeof wpwoof_editorsId4init !== 'undefined' && wpwoof_editorsId4init.length) {
                        for (var i = 0; i < wpwoof_editorsId4init.length; i++) {
                            initWPEditor(wpwoof_editorsId4init[i]);
                        }
                    }
                    break;
                case 'edit_page':
                    $(document).on('click', 'a#wpwoof-button-create-localize', function (event) {
                        event.preventDefault();
                        create_localize_form_submit($(this).attr('href'));
                    });
                    file_name_placeholder_auto_fill_from_feed_name();
                    enable_cost_of_goods_sold_in_feed();
                    break;
                default:
                    console.log(`Wrong wpwoof_current_page: ${wpwoof_current_page}.`);
            }

        }
    }

    initPage();

    function initWPEditor(id) {
        if ($('#wpwoof-editor-' + id).length) {
            id = 'wpwoof-editor-' + id;
        }
        if (typeof tinyMCE !== 'undefined' && typeof tinyMCE.editors[id] !== 'undefined') {
            wp.editor.remove(id);
        }

        wp.editor.initialize(id, {
            tinymce: {
                wpautop: true,
                plugins: 'charmap colorpicker hr lists paste tabfocus textcolor fullscreen wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,wp_adv,listbuttons',
                toolbar2: 'styleselect,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                textarea_rows: 20
            },
            quicktags: {buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'},
            mediaButtons: false,
        });
    }

    function WPEditor_detectChanges() {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.editors.length) {
            for (var i = 0; i < tinyMCE.editors.length; i++) {
                if (tinyMCE.editors[i].isDirty()) {
                    tinyMCE.triggerSave();
                    $('#' + tinyMCE.editors[i].id).change();
                }
            }
        }
    }

    function prepareCustomEditorToSubmit() {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.editors.length) {
            tinyMCE.triggerSave();
            for (var i = 0; i < tinyMCE.editors.length; i++) {
                var fullID = tinyMCE.editors[i].id;
                if (fullID.startsWith('wpwoof-editor-')) {
                    var re = /wpwoof-editor-(\d)_/;
                    var loopArray = re.exec(fullID);
                    if (loopArray) {
                        $('input[name="wpfoof-box-media[extra][' + loopArray[1] + '][' + fullID.replace('wpwoof-editor-' + loopArray[1] + '_', '') + '][value]"').val($('#' + fullID).val());
                    } else {
                        $('input[name="wpfoof-box-media[extra][' + fullID.replace('wpwoof-editor-', '') + '][value]"').val($('#' + fullID).val());
                    }

                }
            }

        }

    }

    $(document).on('click', '.wpwoof-localized-switcher', function (e) {
        e.preventDefault();

        let currentElement = $(this);
        let currentMainId = currentElement.attr('data-main-feed-id');
        if (!currentMainId) return;

        let currentChild = $('.wpwoof-localized-row[data-parent-feed-id="' + currentMainId + '"]');
        if (!currentChild.length) return;

        currentElement.toggleClass('wpwoof-localized-switcher-shown');

        if (currentElement.hasClass('wpwoof-localized-switcher-shown')) {
            currentChild.fadeIn();
            currentElement.text('Hide localized feeds');
        } else {
            currentChild.fadeOut();
            currentElement.text('Show localized feeds');
        }
    });

    $(document).on('click', '.wpwoof-localized-switcher-global', function (e) {
        e.preventDefault();

        let currentElement = $(this);
        let currentSpan = currentElement.find('span');
        let currentChild = $('.wpwoof-localized-row');
        let currentMainFeeds = $('.wpwoof-localized-switcher');
        if (!currentSpan.length || !currentChild.length || !currentMainFeeds.length) return;

        currentElement.toggleClass('wpwoof-localized-switcher-global-shown');

        if (currentElement.hasClass('wpwoof-localized-switcher-global-shown')) {
            currentChild.fadeIn();
            currentMainFeeds.addClass('wpwoof-localized-switcher-shown');
            currentMainFeeds.text('Hide localized feeds');
            currentSpan.text('Hide localized feeds');
        } else {
            currentChild.fadeOut();
            currentMainFeeds.removeClass('wpwoof-localized-switcher-shown');
            currentMainFeeds.text('Show localized feeds');
            currentSpan.text('Show localized feeds');
        }
    });

    $(document).on('click', 'a.wpwoof-button-forlist:disabled', function (e) {
        return false;
    });

    function Wpwoof_getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return null;
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    $.fn.wpwoofSwicher = function (feed_id, status) {
        $.post(ajaxurl, {
            'action': 'set_wpwoof_disable_status',
            'set_wpwoof_disable_status': (status) ? 0 : 1,
            'feed_id': feed_id,
            '_ajax_nonce': WPWOOF.nonce
        }, function (answ) {
            if (answ.status && answ.status == "OK") {
                console.log('ok');

            }
        }, "JSON").fail(function () {
            alert('AJAX REQUEST ERROR: Your changes were not saved!\n' +
                'Please reload the page and make changes again.');
        });
    }

    $.fn.saveWPWoofParam = function (data, cb) {
        //console.log("senddata:",data);
        if (typeof data["action"] != "undefined" && data["action"] == 'set_wpwoof_schedule') {

            data["wpwoof_schedule"] = $("select[name='wpwoof_schedule']").val();
            data["wpwoof_schedule_from"] = $("input[name='wpwoof_schedule_from']").val();

        }
        if (typeof data === 'string') {
            data = data + "&_ajax_nonce=" + WPWOOF.nonce;
        } else {
            data['_ajax_nonce'] = WPWOOF.nonce;
        }
        if (data) $.post(ajaxurl, data, function (answ) {
            //console.log("answ:",answ);
            if (cb) cb(answ);
        }).fail(function (xhr, status, error) {
            console.log("saveWPWoofParam error:", error, "xhr:", xhr, "status:", status);
            if (error === 'Forbidden') {
                alert('AJAX REQUEST ERROR: Your changes were not saved!\n' +
                    'Please reload the page and make changes again.');
            }
            // if (cb) cb(error);
        });
    }


    $.fn.wpwoofOpenCloseFieldList = function (sList, isShow) {
        if (isShow) jQuery('#id' + sList + 'Fields').show();
        else jQuery('#id' + sList + 'Fields').hide();
    }

    var menutab = Wpwoof_getParameterByName('tab');
    var edittab = Wpwoof_getParameterByName('edit');

    if (menutab == null || menutab < 0)
        menutab = 0;
    if (edittab == null) {
        //toggle tab content
        $('#idWpWoofAddNewFeed').on('click', function () {
//                console.log("HIDER ");
            $('.wpwoof-settings-panel.first').hide();
            $('.wpwoof-settings-panel.second').show();
            return false;
        });
    }

    $(document).on('click', '.wpwoof-open-popup', function (event) {
        event.preventDefault();
        $(this).parents('.wpwoof-open-popup-wrap').find('.wpwoof-popup-wrap').show();
    });

    $(document).on('click', '.wpwoof-popup-close, .wpwoof-popup-done', function () {
        $(this).parents('.wpwoof-popup-wrap').hide();
    });


    function sendWPWOOForm() {
        $('.wpwoof_addfeed_submit').toggleClass('wpwoof-loader');
        $('.wpwoof_addfeed_submit').toggleClass('CLSwpwoofSubmit');
        if ($('#feed_file_name').val() == '') {
            $('#feed_file_name').val($('#feed_file_name').attr('placeholder'));
        }
        var formhref = $('#wpwoof-addfeed').attr('action');
        $.fn.saveWPWoofParam($('#wpwoof-addfeed').serialize() + "&wpwoof_addfeed_submit=ajax&action=wpwoof_addfeed_submit", function (answ) {
            if (answ.status && answ.status == "error" && answ.message) {
                alert('Failed to save the feed: ' + answ.message);
            } else {
                $('.wpwoof_addfeed_submit').toggleClass('wpwoof-loader');
                $('.wpwoof_addfeed_submit').toggleClass('CLSwpwoofSubmit');
            }
            window.location.href = formhref;
        });
        return false;
    }

    function wpFindInArray(arr, val) {
        if (typeof arr === 'object') {
            for (var i in arr) {
                if (arr[i] == val) return i;
            }
        }
        return -1;
    }

    $(document).on('click', '.CLSwpwoofSubmit', function (e) {     /*#wpwoof-addfeed*/
        //console.log('TYT.CLSwpwoofSubmit');
        var feed_name = ($('#idFeedName').val()).trim();
        var regexEmpty = /^\s+$/;
        var regexTitle = /[!\!@#$%^&*()+\=\[\]{};':"\\|,<>\/?]/;
        if (!feed_name.length && typeof $('#idFeedName').attr('placeholder') != 'undefined' && $('#idFeedName').attr('placeholder').length) {
            feed_name = $('#idFeedName').attr('placeholder');
            $('#idFeedName').val($('#idFeedName').attr('placeholder'));
        }
        if (feed_name == '') {
            e.preventDefault();
            alert('The feed name can not be empty.');
            $('html, body').animate({
                scrollTop: ($('#idFeedName').offset().top - 150)
            }, 200);
            $('#idFeedName').focus();
            return false;
        } else if (regexTitle.test(feed_name)) {
            e.preventDefault();
            alert('A Feed Name should not contain special characters. The following special characters are not allowed: " ! @ # $ % ^ & * ( ) + \\ / = [ ] { } ; \' : " , < > ? ".');
            $('html, body').animate({
                scrollTop: ($('#idFeedName').offset().top - 150)
            }, 200);
            $('#idFeedName').focus();
            return false;
        } else if (feed_name.length < 3 || feed_name.length > 100) {
            e.preventDefault();
            alert('The feed name should contain at least 3 characters but less than 100 characters.');
            $('html, body').animate({
                scrollTop: ($('#idFeedName').offset().top - 150)
            }, 200);
            $('#idFeedName').focus();
            return false;
        } else if ($('#IDtax_countries').length > 0 && $('#IDtax_countries').is(":visible") && $('#IDtax_countries').val() == "") {
            e.preventDefault();
            alert('Please define “apply tax for” under Price and Tax settings.');
            $('html, body').animate({
                scrollTop: ($('#IDtax_countries').offset().top - 150)
            }, 200);
            $('#IDtax_countries').focus();
            return false;
        }

        if ($('#ID-feed_type').val() == 'google_local_inventory' && $('#store_code').val() === "") {
            e.preventDefault();
            alert('Please set “Store code”.');
            $('html, body').animate({
                scrollTop: ($('#store_code').offset().top - 150)
            }, 200);
            $('#store_code').focus();
            return false;
        }

        if (!$('.feed_category:checked').length) {
            const result = confirm("At least one category must be selected! Select all categories? If not, select at least one category before saving the feed.");
            if (result) {
                $('#feed_category_all').prop('checked', true);
            } else {
                return false;
            }
        }
        if ($('#feed_category_all').is(':checked')) {
            $('#wpwoof-popup-categories input').each(function () {
                if ($(this).attr('id') != 'feed_category_all') {
                    $(this).remove();
                }
            });
        } else if ($("select[name='feed_use_lang']").length && $("select[name='feed_use_lang']").val() != 'all') {
            let lang = $("select[name='feed_use_lang']").val();
            $('#wpwoof-popup-categories li.language_all:not(.language_' + lang + ')').each(function () {
                $(this).remove();
            });
        }

        if ($('#ID-feed_type').val() == 'googleReviews' && !jQuery('#feed_remove_variations').prop('checked')) {
            jQuery('#feed_remove_variations').prop('checked', true)
        }

        sendWPWOOForm();

        return false;

    });

    $(document).on('click', '#wpwoof-hide-additional', function () {
        $('#wpwoof-additionalfield-wrap').toggleClass('wpwoof-additional-hide');
        if ($('#wpwoof-additionalfield-wrap').hasClass('wpwoof-additional-hide')) {
            $(this).text('Show Additional Attributes');
        } else {
            $(this).text('Hide Additional Attributes');
        }
    });

    $(document).on('click', '#wpwoof-popup-categories li input.feed_category', function (e) {
        var cat_id = $(this).attr('id') || '';
        if (cat_id != 'feed_category_all') {
            var allchecked = true;
            let lang_selector = ($("select[name='feed_use_lang']").length) ? ".language_" + $("select[name='feed_use_lang']").val() : "";
            $('#wpwoof-popup-categories li' + lang_selector + ' input.feed_category').each(function (index, el) {
                var cat_id = $(this).attr('id') || '';
                if (cat_id != 'feed_category_all' && $(this).prop('checked') == false)
                    allchecked = false;
            });

            if (!allchecked) {
                $('#feed_category_all').prop('checked', false);
            } else {
                $('#feed_category_all').prop('checked', true);
            }
            id = $(this).val();
            if ($(this).prop('checked') && id > 0) {
                $('#feed_category_excluded_' + id).prop('checked', false);
            }
        }
    });

    $(document).on('click', '#wpwoof-popup-categories li input.feed_category_excluded', function (e) {
        if ($(this).prop('checked')) {
            $('#feed_category_' + $(this).val()).prop('checked', false);
            $('#feed_category_all').prop('checked', false);
        }
    });

    $(document).on('click', '#feed_category_all', function (e) {
        var tick = $(this).prop('checked');
        let lang_selector = ($("select[name='feed_use_lang']").length) ? ".language_" + $("select[name='feed_use_lang']").val() : "";
        $('#wpwoof-popup-categories li' + lang_selector + ' input.feed_category').prop('checked', tick);
        if (tick) {
            $('#wpwoof-popup-categories li' + lang_selector + ' input.feed_category_excluded').prop('checked', false);
        }

    });

    $(document).on('click', '#feed_check_all_additional', function (e) {
        var tick = $(this).prop('checked');
        $('input.wpwoof-field-additional').prop('checked', tick);
    });

    $(document).on('change', 'select.wpwoof_mapping_option', function () {
        if ($(this).val() == 'use_custom_attribute') {
            if (!$(this).next('input').hasClass('wpwoof_mapping_attribute')) {
                var name = $(this).attr('name');
                name = name.toString();
                name = name.replace('[value]', '[custom_attribute]');
                var html = '<input type="text" name="' + name + '" value="" class="wpwoof_mapping_attribute" />';
                $(this).after(html);
            }
        } else {
            if ($(this).next('input').hasClass('wpwoof_mapping_attribute')) {
                $(this).next('input').remove();
            }
        }
    });
    $('a.regenerate').on('click', function () {

        if ($(this).is(':disabled')) return false;
        sendRegeneration($(this).attr('href'));
        var elmID = $(this).attr('id');
        if (elmID) {
            elmID = elmID.substring(0, elmID.length - 1);
            showFeedLoaders($('#' + elmID).data('feedid'), -29);
        }
        //$(this).find('span').html( ' in progress ');
        return false;
    });

    function sendRegeneration(url) {
        if (url) {
            $.post(url);
        }
    }


    function showFeedLoaders(feedId, marginleft, total) {
        $('tr#idTr' + feedId + ' div.wpwoof-loader').show();
        $("#wpwoof_img_" + feedId).css('margin-left', marginleft);
        if (total) $("#wpwoof_img_" + feedId).attr('title', 'generated - ' + Math.round(total) + '%');
        $('#wpwoof_status_' + feedId).show();
    }

    function checkFeedsStatus() {
        var feedsIdsOnPage = [];
        $.each($('div.wpwoof_statusbar'), function (index, elm) {
            feedsIdsOnPage.push($(elm).data('feedid'));
        });
        if (!feedsIdsOnPage.length) return false;
        $.fn.saveWPWoofParam({
            'action': 'wpwoof_status',
            'wpwoof_status': 'get',
            'feedids': feedsIdsOnPage
        }, function (data) {
            var starded = new Array();
            $.each($('div.wpwoof_statusbar'), function (index, elm) {
                var marginleft = -2;
                var total = 100;
                var hideButtons = false;
                var feedId = $(elm).data('feedid');
//                    if(starded.indexOf(data[i]['feed_id'])==-1){
//                        starded.push(data[i]['feed_id']);
//                        if($('#'+ data[i]['option_name']+'a').length) sendRegeneration( $('#'+ data[i]['option_name']+'a').attr('href') );
//                    }
                if (typeof data[feedId] != "undefined") {
                    if (data[feedId]['processed'] != -1) {
                        var prods = data[feedId]['total'];
                        total = prods ? 100.0 / prods * data[feedId]['processed'] * 1.0 : 100;
                        marginleft += -29 + Math.round(3 * total / 10);
                    } else {
                        marginleft = -29;
                        total = 0;
                    }
                    if (typeof data[feedId]['timestr'] != "undefined") {
                        $('tr#idTr' + feedId + ' td.column-feeddate').html(data[feedId]['timestr']);
                    }
                    if (typeof data[feedId]['total'] != "undefined" && data[feedId]['total']) {
                        $('tr#idTr' + feedId + ' td.column-feedproducts').html(data[feedId]['total']);
                    }
                    if (typeof data[feedId]['hideFeedButtons'] != "undefined" && data[feedId]['hideFeedButtons']) {
                        $('tr#idTr' + feedId + ' a.wpwoof-hide-feed-button').hide();
                    } else {
                        $('tr#idTr' + feedId + ' a.wpwoof-hide-feed-button').show();
                    }
                    if ((data[feedId]['processed'] != 0 && data[feedId]['total'] != 0) || data[feedId]['processed'] == -1) hideButtons = true;

                    if (typeof data[feedId]['error'] !== 'undefined') {
                        if ($('tr#idTr' + feedId + ' a.wpwoof_alarm').length) {
                            $('tr#idTr' + feedId + ' a.wpwoof_alarm').attr('title', data[feedId]['error'])
                        } else {
                            var $linkAlarm = $('<a>', {class: "wpwoof_alarm", title: data[feedId]['error']});
                            $linkAlarm.append('<span class="dashicons dashicons-warning" style="color:#dd4e4e;"></span>');
                            $('tr#idTr' + feedId + ' div.wpwoof-feedname-content div.wpwoof-loader').after($linkAlarm.clone());
                            $('tr#idTr' + feedId + ' div.wpwoof-feednumber-content').append($('<div>', {class: 'wpwoof-feed-status'}).append($linkAlarm));
                        }
                    } else if ($('tr#idTr' + feedId + ' a.wpwoof_alarm').length) {
                        $('tr#idTr' + feedId + ' div.wpwoof-feed-status').remove();
                        $('tr#idTr' + feedId + ' a.wpwoof_alarm').remove();
                    }
                    if (data[feedId]['show_loader']) {
                        showFeedLoaders(feedId, marginleft, total);
                    } else {
                        $('tr#idTr' + feedId + ' div.wpwoof-loader').hide();
                        $('#wpwoof_status_' + feedId).hide();
                    }
                } else {
                    alert('The page is out of date. It will be reloaded to show updated data.')
                    location.reload();
                }
            });
        });

    }

    setInterval(checkFeedsStatus, 47000);
    $.fn.toggleFeedField = function (sClass) {
        //console.log('toggle:'+sClass);
        $('[class|="stl"],[class*=" stl-"]').hide();
        $('.stl-' + sClass).show();
        if ($('#ID-feed_type').val() == 'google' && $('#inventory').attr('data-new') == 1)
            $('#inventory').prop('checked', false);
        /* showSKUorID();*/
    }
    $.fn.initGoogleTaxonomy = function (elm, cats, acturl) {
        elm = elm ? elm : '.wpwoof_google_category';


        WPWOOFpreselect = cats ? cats : WPWOOFpreselect;
        WPWOOFtaxSrc = acturl ? acturl : WPWOOFtaxSrc;


        var WPWOOFoptions = {
            empty_value: 'null',
            indexed: true,  // the data in tree is indexed by values (ids), not by labels
            on_each_change: WPWOOFtaxSrc,//'<?php echo $taxSrc; ?>', // this file will be called with 'id' parameter, JSON data must be returned
            choose: function (level) {
                if (level < 1)
                    return 'Select Main Category';
                else
                    return 'Select Sub Category';
            },
            loading_image: WPWOOFspiner, //'<?php echo home_url( '/wp-includes/images/wpspin.gif');?>',
            get_parent_value_if_empty: true,
            set_value_on: 'each',
            preselect: {'wpwoof_google_category': WPWOOFpreselect} /* <?php echo $preselect; ?> */
        };

        var WPWOOFdisplayParents = function () {
            var labels = []; // initialize array
            var IDs = []; // initialize array
            $(this).siblings('select') // find all select
                .find(':selected') // and their current options
                .each(function () {
                    if ($(this).text() != 'Select Main Category' && $(this).text() != 'Select Sub Category') {
                        if ($(this).val() != '') {
                            labels.push($(this).text());
                            IDs.push($(this).val());
                        }
                    }
                }); // and add option text to array
            var elmparent = $(this).parent();
            if (elmparent.children('div [id^=\'feed_google_text_category_\']').length > 0) {
                elmparent.children('div [id^=\'feed_google_text_category\']').text(labels.join(' > '));
            }
            elmparent.children('input[name^=\'feed_google_category\']').val(labels.join(' > '));
            elmparent.children('input[name^=\'feed_google_category_id\']').val(IDs.join(','));

        }
        if ($(elm).length) {

            $.getJSON(WPWOOFtaxSrc, function (tree) { // '<?php echo $taxSrc; ?>' initialize the tree by loading the file first
                $(elm).optionTree(tree, WPWOOFoptions).change(WPWOOFdisplayParents);
            });
        }
    }
    if ($('#ID-feed_type').length) { /*show and hide fields for feed type */
        $('[class|="stl"],[class*=" stl-"]').hide();
        $(".stl-" + $('#ID-feed_type').val()).show();
    }
    var switches = document.querySelectorAll('input[type="checkbox"].ios-switch');
    for (var i = 0, sw; sw = switches[i++];) {
        var divSwitcher = $(sw).parent().find('div.switch');
        if (divSwitcher.length == 0) {
            var div = document.createElement('div');
            div.className = 'switch';
            sw.parentNode.insertBefore(div, sw.nextSibling);
        }
    }

    //custom value field
    $(document).on('change', "td.input-cell div.fstSingleMode select,td.addfeed-top-value div.fstSingleMode select", function () {
        let fieldname = this.name.substring(6, this.name.length - 8);
        if (this.value == 'custom_value') {
            $("[name='" + this.name.replace("[value]", "[custom_value]") + "']").show();
            if (wp.editor) {
                wp.editor.remove('wpwoof-editor-' + fieldname);
                $('textarea#wpwoof-editor-' + fieldname).hide();
            }
        } else if ((this.value == 'custom_value_editor')) {
            $('textarea#wpwoof-editor-' + fieldname).show();
            $('textarea#wpwoof-editor-' + fieldname).val($("[name='" + this.name.replace("[value]", "[custom_value]") + "']").val());
            initWPEditor(fieldname);
            $("[name='" + this.name.replace("[value]", "[custom_value]") + "']").hide();
        } else {
            $("[name='" + this.name.replace("[value]", "[custom_value]") + "']").hide();
            if (wp.editor) {
                wp.editor.remove('wpwoof-editor-' + fieldname);
                $('textarea#wpwoof-editor-' + fieldname).hide();
            }
        }
    });
    $(document).on('click', "input.remove-extra-field-btn", function () {
        $(this).parent().parent().remove();
        storeWpWoofdata();
    });
    $(document).on('click', "input.remove-extra-field-product-btn", function () {
        $(this).closest('div').remove();
    });
    $(document).on('change', "[name^='extra[']", function () {
        storeWpWoofdata();
    });

    function get_next_repeated_name(tag_name) {
        let tag_max_number = 0;
        $('[name^="extra[' + tag_name + '-"][name$="][custom_value]').each(function () {
            let regex = new RegExp('extra\\[' + tag_name + '-(\\d*)\\]', 'g');
            let curArr, curId;
            if ((curArr = regex.exec($(this).attr("name"))) !== null) {
                curId = parseInt(curArr[1]);
                if (curId >= tag_max_number) tag_max_number = curId + 1;
            }

        });
        return tag_name + '-' + tag_max_number;
    }

    $(document).on('click', "input#add-extra-field-btn", function () {
        let extraRow = $('table#wpwoof-def-extra-row tr').clone();
        let tag_name_original = $('#extraFieldList').val().replace('wpwoofattr_', '');
        let tag_name_for_show = $('#extraFieldList option:selected').data("title")
        let is_repeated = !!$('#extraFieldList option:selected').data("repeated");
        tag_name = is_repeated ? get_next_repeated_name(tag_name_original) : tag_name_original;
        if (tag_name !== "custom_extra_field") {
            $('input[name="wpwoof-def[custom_tag_name]"]', extraRow).remove();
            $('div.extra-input__item', extraRow).remove();
            $('label', extraRow).remove();
            $('b#wpwoof-def-title', extraRow).html(tag_name_for_show + ":");
            $('div.extra-link-2-wrapper-dashboard', extraRow).html(wpwoof_help_links[tag_name_original]);
            if (typeof wpwoof_select_values[tag_name] !== 'undefined') {
                $('textarea#wpwoof-editor-def', extraRow).remove();
                $('option[value="custom_value_editor"]', extraRow).remove();
                $('input[name="wpwoof-def[custom_value]"]', extraRow).remove();
                $.each(wpwoof_select_values[tag_name], function (i, item) {
                    $('select[name="wpwoof-def[custom_value]"]', extraRow).append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            } else {
                $('select[name="wpwoof-def[custom_value]"]', extraRow).remove();
            }
        } else {
            $('b#wpwoof-def-title', extraRow).remove();
            $('select[name$="[custom_value]"]', extraRow).remove();
            $('div.extra-link-2-wrapper-dashboard', extraRow).remove();
            let maxExtraFieldNumber = 0;
            $('[name^="extra[custom-extra-field-"][name$="][custom_tag_name]').each(function () {
                var regex = /extra\[custom-extra-field-(\d*)]/;
                if ((curArr = regex.exec($(this).attr("name"))) !== null) {
                    curId = parseInt(curArr[1]);
                    if (curId >= maxExtraFieldNumber) maxExtraFieldNumber = curId + 1;
                }

            });
            tag_name = 'custom-extra-field-' + maxExtraFieldNumber;
        }
        $('[name^="wpwoof-def"]', extraRow).each(function () {
            let fullTagName = $(this).attr("name").replace('wpwoof-def', "extra[" + tag_name + "]");
            $('[for="' + $(this).attr("name") + '"]', extraRow).attr("for", fullTagName);
            $(this).attr("id", fullTagName);
            $(this).attr("name", fullTagName);

            if ($(this).is('select[name$="[value]"')) $(this).fastselect();
        });
        $('textarea#wpwoof-editor-def', extraRow).attr("id", "wpwoof-editor-" + tag_name);
        $(extraRow).insertBefore('tr#tr-befor-add-new-field');


    });
    $(document).on('click', "input#add-extra-field-product-btn", function () {
        let loop = $(this).data("loop");
        let loopStr = (typeof loop === 'undefined') ? "" : "[" + loop + "]";
        let loopStrEditor = (typeof loop === 'undefined') ? "" : loop + '_';
        var div = $(this).parent().parent();
        let div_product = div.parent(".product-catalog-feed-pro");
        if (div_product.length === 0) div_product = div.parent("#woof_add_extra_fields");
        let rowWrapper = $("<div>");
        let select = $('#extraFieldList', div);
        let tagName = select.val().replace('wpwoofattr_', '');
        let tag_name_for_show = $('#extraFieldList option:selected', div).data("title");
        let is_repeated = !!$('option:selected', select).data("repeated");
        let repeated_str = is_repeated ? '[]' : '';
        let needEditor = tagName == "custom_extra_field_editor";
        let row = $("<p>", {
            class: "form-field custom_field_type add-extra-fields " + tagName,
            style: (needEditor && typeof loop !== 'undefined' ? 'display: inline;' : '')
        });
        if (tagName == "custom_extra_field" || tagName == "custom_extra_field_editor") {
            let maxExtraFieldNumber = 0;
            $('[name^="wpfoof-box-media[extra]' + loopStr + '[custom-extra-field-"][name$="][custom_tag_name]"').each(function () {
                var regex = /\[custom-extra-field-(\d*)]/;
                if ((curArr = regex.exec($(this).attr("name"))) !== null) {
                    curId = parseInt(curArr[1]);
                    if (curId >= maxExtraFieldNumber) maxExtraFieldNumber = curId + 1;
                }

            });
            tagName = 'custom-extra-field-' + maxExtraFieldNumber;
            row.append($("<input>", {
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][custom_tag_name]",
                style: "margin-left: -150px;width: 140px;",
                type: "text",
                class: (needEditor && typeof loop !== 'undefined' ? "" : "catalog-pro-custom-extra-field"),
                placeholder: "Field name"
            }));
            if (needEditor) {
                row.append($("<textarea>", {
                    placeholder: "Custom value",
                    class: "short wc_input_" + tagName,
                    id: "wpwoof-editor-" + loopStrEditor + tagName
                }));
                row.append($("<input>", {
                    name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][type]",
                    value: 'editor',
                    type: "hidden"
                }));
            }
            row.append($("<input>", {
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][value]" + repeated_str,
                placeholder: "Custom value",
                class: "short wc_input_" + tagName,
                type: needEditor ? "hidden" : "text"
            }));

            row.append($("<input>", {type: "button", class: "button remove-extra-field-product-btn", value: "remove"}));
//            row.append($("<span>", { class:"extra-link-2-wrapper"}).html(wpwoof_help_links[tagName]));

            var $linkWrap = $('<span>', {class: "extra-link-wrapper"});


            var $linkFacebook = $('<div>', {class: "extra-link__item"});
            $linkFacebook.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][facebook]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][facebook]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkFacebook.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][facebook]"}).html('Facebook'));
            $linkWrap.append($linkFacebook);

            var $linkGoogle = $('<div>', {class: "extra-link__item"});
            $linkGoogle.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][google]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][google]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkGoogle.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][google]"}).html('Google Merchant'));
            $linkWrap.append($linkGoogle);

            var $linkAds = $('<div>', {class: "extra-link__item"});
            $linkAds.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][adsensecustom]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][adsensecustom]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkAds.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][adsensecustom]"}).html('Google Custom Remarketing'));
            $linkWrap.append($linkAds);

            var $linkPinterest = $('<div>', {class: "extra-link__item"});
            $linkPinterest.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][pinterest]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][pinterest]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkPinterest.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][pinterest]"}).html('Pinterest'));
            $linkWrap.append($linkPinterest);

            var $linkTiktok = $('<div>', {class: "extra-link__item"});
            $linkTiktok.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][tiktok]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][tiktok]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkTiktok.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][tiktok]"}).html('TikTok'));
            $linkWrap.append($linkTiktok);

            $linkWrap.append($("<br>"));

            var $linkMapping = $('<div>', {class: "extra-link__item"});
            $linkMapping.append($("<input>", {
                id: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][mapping]",
                name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][mapping]",
                type: "checkbox",
                checked: "checked"
            }));
            $linkMapping.append($("<label>", {for: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][feed_type][mapping]"}).html('Use for mapping (limited to 100 chars if mapped to custom labels)'));
            $linkWrap.append($linkMapping);

            row.append($linkWrap);
        } else {
            row.append($("<label>").html(tag_name_for_show + ":"));
            if (typeof wpwoof_select_values[tagName] !== 'undefined') {
                $select = $("<select>", {
                    name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][value]" + repeated_str,
                    class: "select short"
                });
                $.each(wpwoof_select_values[tagName], function (i, item) {
                    $($select).append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
                row.append($select);
            } else {
                row.append($("<input>", {
                    name: "wpfoof-box-media[extra]" + loopStr + "[" + tagName + "][value]" + repeated_str,
                    placeholder: "Custom value",
                    class: "short wc_input_" + tagName,
                    type: "text"
                }));
            }
            row.append($("<input>", {type: "button", class: "button remove-extra-field-product-btn", value: "remove"}));
            row.append($("<span>", {class: "extra-link-2-wrapper"}).html(wpwoof_help_links[tagName]));
            if (tagName == 'installmentmonths') {
                //row.append($("<br>"));
                let $w1 = $("<p>", {class: "installmentamount-wrapper form-field custom_field_type add-extra-fields"});
                $w1.append($("<label>").html("installmentamount:"));
                $w1.append($("<input>", {
                    name: "wpfoof-box-media[extra]" + loopStr + "[installmentamount][value]",
                    placeholder: "Custom value",
                    class: "short wc_input_installmentamount",
                    type: "text"
                }));
                rowWrapper.append(row);
                rowWrapper.append($w1);
            }
        }
        if (tagName != 'installmentmonths') {
            rowWrapper.append(row);
        }
        $(rowWrapper).insertBefore($('hr#hr-befor-add-new-field', div_product));
        if (needEditor) {
            initWPEditor(loopStrEditor + tagName);
        }

    });

    $(document).ready(function () {
        $('#woof_add_extra_fields').closest('.panel-wrap').css('overflow', 'visible');
    });

    $(document).on('click', '.wpwoof-notice-active .notice-dismiss', function () {
        let element = $(this).parent('.wpwoof-notice-active').data('name');
        if (typeof element !== "string" || !element.length) {
            return false;
        }
        jQuery.post({
            url: ajaxurl,
            data: {
                action: 'wpwoof_notice_action',
                '_ajax_nonce': WPWOOF.nonce,
                element
            }
        })

    })

    /**
     * Submits a form to create localize feed
     * @param {string} url - The URL where the form will be submitted.
     * @return {void}
     */
    function create_localize_form_submit(url) {
        var $form = $('<form/>', {
            action: url,
            method: 'POST'
        }).append($('<input/>', {
            type: 'hidden',
            name: 'main-feed',
            value: wpwoof_create_localize_data.main_feed
        })).append($('<input/>', {
            type: 'hidden',
            name: '_wpnonce',
            value: wpwoof_create_localize_data._wpnonce
        })).append($('<input/>', {
            type: 'hidden',
            name: 'lang',
            value: $('select#feed_localize_lang').val()
        }));
        if ($('select#feed_localize_currency').length > 0) {
            $form.append($('<input/>', {
                type: 'hidden',
                name: 'currency',
                value: $('select#feed_localize_currency').val()
            }));
        }
        $('body').append($form);

        $form.submit();
    }

    /**
     * Fills the file name placeholder from the feed name input.
     *
     * @returns {void}
     */
    function file_name_placeholder_auto_fill_from_feed_name() {

        set_placeholders_for_localized_feed();

        $('[name="feed_use_currency"], [name="feed_use_lang"]').on('change', function () {
            set_placeholders_for_localized_feed();
        });

        $('#idFeedName').on('input', function () {
            let feed_name = $(this).val();
            let feed_file_name = feed_name.trim().replace(/ /g, '-').toLowerCase();
            $('#feed_file_name').attr('placeholder', feed_file_name);
        });
    }

    function set_placeholders_for_localized_feed() {
        let feed_name_field = $('#idFeedName');
        if (feed_name_field.val().length || !feed_name_field.data('main-feed-name')) {
            return false;
        }
        let placeholder_feed_name = feed_name_field.data('main-feed-name');
        if (['fb_localize', 'fb_country', 'google_local_inventory'].includes($('#ID-feed_type').val())) {
            let lang = $('[name="feed_use_lang"]').val();
            if (typeof (lang) !== "undefined") {
                placeholder_feed_name += ' - ' + lang;
            }
            let currency = $('[name="feed_use_currency"]').val();
            if (typeof (currency) !== "undefined") {
                placeholder_feed_name += ' - ' + currency;
            }
        }
        feed_name_field.attr('placeholder', placeholder_feed_name);
        let feed_file_name = placeholder_feed_name.trim().replace(/ /g, '-').replace(/-+/g, '-').toLowerCase();
        $('#feed_file_name').attr('placeholder', feed_file_name);
        return true;
    }

    var COG_disabled_in_feed, valid_ID_for_GAD;

    function enable_cost_of_goods_sold_in_feed() {
        COG_disabled_in_feed = !$('#field_enable_cost_of_goods_sold').is(':checked');
        $('#field_enable_cost_of_goods_sold').on('change', function () {
            if ($(this).is(':checked')) {
                $('#COG_disabled_in_feed_warning').hide();
                COG_disabled_in_feed = false;
            } else {
                $('#COG_disabled_in_feed_warning').show();
                COG_disabled_in_feed = true;
            }
            show_or_hide_auto_pricing_min_price_settings_in_feed();
        });
        check_feed_ID_for_GAD();
        $('select[name="field_mapping[id][value]"], input[name="field_mapping[id_prefix]"], input[name="field_mapping[id_postfix]"]').on('change', check_feed_ID_for_GAD);
    }

    function check_feed_ID_for_GAD() { //check that ID configured by default
        var selectValue = $('select[name="field_mapping[id][value]"]').val();
        var prefixValue = $('input[name="field_mapping[id_prefix]"]').val();
        var postfixValue = $('input[name="field_mapping[id_postfix]"]').val();

        if (selectValue === 'id' && prefixValue === '' && postfixValue === '') {
            valid_ID_for_GAD = true;
            $('#GAD_warning_id_changed').hide();
        } else {
            valid_ID_for_GAD = false;
            $('#GAD_warning_id_changed').show();
        }
        show_or_hide_auto_pricing_min_price_settings_in_feed();
    }

    function show_or_hide_auto_pricing_min_price_settings_in_feed() {
        if (COG_disabled_in_feed || !valid_ID_for_GAD) {
            $('table#auto_pricing_min_price_settings_in_feed').hide()
        } else {
            $('table#auto_pricing_min_price_settings_in_feed').show()
        }
    }

});

function loadTaxomomy(elem, onchange) {
    if (!elem) return;
    var elm = jQuery(elem);
    if (elm.length != 1) return;
    var elmClassName = elm.attr("class");
    var parent = elm.parent();

    var onchangecallback = onchange;

    var elmWithNames = parent.children("." + elmClassName + '_name');
    var WPWOOFnames = htmlDecode(elmWithNames.val()).split(" > ");
    elmWithNames.val("");

    var changeTaxonomy = function (selector) {
        var level = jQuery(selector).attr('data-level');

        parent.children("select.wpwoofeed_g_category").each(function () {
            if (jQuery(this).data("level") > level) {
                if (jQuery(this).next().is("p") && jQuery(this).next().children().length == 0) { //remove "<p></p>"
                    jQuery(this).next().remove();
                }
                jQuery(this).remove();
            }
        });

        WPWOOFnames = elmWithNames.val().split(" > ").slice(0, level);


        if (jQuery(selector).val() != "") {
            WPWOOFnames = jQuery(selector).find("option:selected").val().split(" > ");
        }
        parseMyInputs();
        if (jQuery(selector).val() != "") {
            loadTaxonomy(level);
        }
    }

    function parseMyInputs() {
        elmWithNames.val(WPWOOFnames.join(' > '));
        if (onchangecallback) onchangecallback();
    }

    function loadTaxonomy(i) {
        i = parseInt(i);
        var elmspinner = "<img class='wpwoofeed_g_spinner' src='/wp-includes/images/wpspin.gif' />";
        elm.before(elmspinner);
        let taxonomy = WPWOOFnames.length ? WPWOOFnames.join(" > ") : "";
        if (typeof (wpwoof_taxonomyPreLoad[taxonomy === '' ? 'root' : taxonomy]) !== "undefined") {
            processAnsw(wpwoof_taxonomyPreLoad[taxonomy === '' ? 'root' : taxonomy], i);
//            console.log('preload');
        } else {
            jQuery.post(ajaxurl, {
                'action': 'wpwoofgtaxonmy',
                'id': i,
                'taxonomy': taxonomy,
            }, function (answ) {
                wpwoof_taxonomyPreLoad[taxonomy] = answ;
                processAnsw(answ, i);
            }, "JSON");
        }
    }

    function processAnsw(answ, i) {
        parent.children("img.wpwoofeed_g_spinner").remove();
        var max = i === -1 ? WPWOOFnames.length : i + 1;
        var start = i === -1 ? 0 : i + 1;
        if (answ) {
            for (var ilvl = start; ilvl <= max; ilvl++) {
                if (typeof (answ[ilvl]) == "undefined" || Object.keys(answ[ilvl]).length === 0) {
                    break;
                }
                var selval = WPWOOFnames[ilvl] ? WPWOOFnames[ilvl] : "";
                let fullValue = WPWOOFnames.slice(0, ilvl).join(" > ");
                var DropDownElement = "<select class='wpwoofeed_g_category " + elmClassName + "_" + ilvl + " selTaxonomy short' data-level='" + ilvl + "'  >"; //onchange='oTaxonony.change("+k+",this);'
                DropDownElement += "<option value='' " + (selval == "" ? "selected='selected'" : "") + ">select</option>";
                for (var idx in answ[ilvl]) {
                    let tmpFV = ilvl ? fullValue + " > " + answ[ilvl][idx] : answ[ilvl][idx];
                    DropDownElement += "<option value='" + tmpFV + "' " + (selval == answ[ilvl][idx] ? "selected='selected'" : "") + ">" + answ[ilvl][idx] + "</option>";
                }
                DropDownElement += "</select><p></p>";
                var ddDrop = jQuery(DropDownElement);
                ddDrop.change(function () {
                    changeTaxonomy(this);
                });
                elm.before(ddDrop);
                parseMyInputs();
            }
        }
    }

    function htmlDecode(value) {
        return jQuery('<div/>').html(value).text();
    }

    loadTaxonomy(-1);

    return {
        change: changeTaxonomy
    };
}

function copyWoofLink(buffer) {
    const el = document.createElement('textarea');
    el.value = buffer;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    return false;
}

wpwoof_taxonomyPreLoad = {};

jQuery(function ($) {

    $('.wpoof-settings-accordion-btn').on('click', function (event) {
        event.preventDefault();
        let accordionContainer = $(this).closest('.wpoof-settings-accordion');

        if (accordionContainer) {
            accordionContainer.toggleClass('wpoof-settings-accordion-active');

            if (accordionContainer.hasClass('wpoof-settings-accordion-active')) {
                accordionContainer.find('.wpoof-settings-accordion-content').slideDown();
            } else {
                accordionContainer.find('.wpoof-settings-accordion-content').slideUp();
            }
        }
    })
});

