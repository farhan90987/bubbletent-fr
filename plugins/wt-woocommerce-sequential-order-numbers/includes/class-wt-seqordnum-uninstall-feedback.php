<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WT_Sequentialordnum_Uninstall_Feedback')) :

    /**
     * Class for catch Feedback on uninstall
     */

    class WT_Sequentialordnum_Uninstall_Feedback
    {
        protected $api_url='https://feedback.webtoffee.com/wp-json/wtseqordnum/v1/uninstall';
        protected $current_version=WT_SEQUENCIAL_ORDNUMBER_VERSION;
        protected $auth_key='wtseqordnum_uninstall_1234#';
        protected $plugin_id='wtseqordnum';
        public function __construct()
        {
            add_action('admin_footer', array($this,'deactivate_scripts'));
            add_action('wp_ajax_wtsequentialordnum_submit_uninstall_reason', array($this,"send_uninstall_reason"));
        }
        private function get_uninstall_reasons() 
        {

            $reasons = array(
                array(
                    'id' => 'conflict-with-order-id',
                    'text' => __('Issue with order number/order_id', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'main_reason',
                    'sub_reason'=>array(
                        array(
                            'id' => 'conflict-with-payment',
                            'text'=>__('Payment', 'wt-woocommerce-sequential-order-numbers'),
                            'type' => 'text',
                            'placeholder' => __('Which plugin/vendor?', 'wt-woocommerce-sequential-order-numbers'),
                        ),
                        array(
                            'id' => 'conflict-with-shipping',
                            'text'=>__('Shipping', 'wt-woocommerce-sequential-order-numbers'),
                            'type' => 'text',
                            'placeholder' => __('Which plugin/vendor?', 'wt-woocommerce-sequential-order-numbers'),
                        ),
                         array(
                            'id' => 'conflict-with-invoice-plugin',
                            'text'=>__('Conflict with Invoice plugin', 'wt-woocommerce-sequential-order-numbers'),
                            'type' => 'text',
                            'placeholder' => __('Which plugin/vendor?', 'wt-woocommerce-sequential-order-numbers'),
                        ),
                         array(
                            'id' => 'others',
                            'text'=>__('Other', 'wt-woocommerce-sequential-order-numbers'),
                            'type' => 'textarea',
                            'placeholder' => __('Could you tell us a bit more?', 'wt-woocommerce-sequential-order-numbers'),
                        ),
                    )
                ),
                array(
                    'id' => 'found-better-plugin',
                    'text' => __('I found a better plugin', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'text',
                    'placeholder' => __('Which plugin?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'not-have-that-feature',
                    'text' => __('The plugin is great, but I need specific feature that you don\'t support', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us more about that feature?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'looking-for-other',
                    'text' => __('It\'s not what I was looking for', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more?','wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'conflict-with-another-plugin',
                    'text' => __('A conflict with another plugin', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Which plugin?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'other',
                    'text' => __('Other', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more?', 'wt-woocommerce-sequential-order-numbers')
                ),
            );

            return $reasons;
        }

        public function deactivate_scripts()
        {
            global $pagenow;
            if('plugins.php' != $pagenow)
            {
                return;
            }
            $reasons = $this->get_uninstall_reasons();
            ?>
            <div class="wtsequentialordnum-modal" id="wtsequentialordnum-wtsequentialordnum-modal">
                <div class="wtsequentialordnum-modal-wrap">
                    <div class="wtsequentialordnum-modal-header">
                        <h3><?php esc_html_e('If you have a moment, please let us know why you are deactivating:', 'wt-woocommerce-sequential-order-numbers'); ?></h3>
                    </div>
                    <div class="wtsequentialordnum-modal-body">
                        <ul class="reasons">
                            <?php 
                            foreach ($reasons as $reason) 
                            { 
                            ?>
                                <li data-type="<?php echo esc_attr($reason['type']); ?>" data-placeholder="<?php echo esc_attr(isset($reason['placeholder']) ? $reason['placeholder'] : ''); ?>">
                                    <label><input type="radio" name="selected-reason" value="<?php echo esc_attr($reason['id']); ?>"><?php echo esc_html($reason['text']); ?></label>
                                    <?php
                                    if($reason['type']=='main_reason')
                                    {
                                        ?>
                                        <ul class="sub_reasons" data-parent="<?php echo esc_attr($reason['id']); ?>">
                                            <?php
                                            foreach($reason['sub_reason'] as $sub_reason)
                                            {
                                               ?>
                                                <li data-type="<?php echo esc_attr($sub_reason['type']); ?>" data-placeholder="<?php echo esc_attr($sub_reason['placeholder']); ?>">
                                                    <label><input type="radio" name="selected-sub-reason" value="<?php echo esc_attr($sub_reason['id']); ?>"><?php echo esc_html($sub_reason['text']); ?></label>
                                                </li>
                                               <?php 
                                            }
                                            ?>
                                        </ul>
                                        <?php

                                    }
                                    ?>
                                </li>
                            <?php 
                            } 
                            ?>
                        </ul>

                        <label style="margin-bottom: 0;">
                            <span class="wtsequentialordnum-checkbox-top-border"></span>
                            <input type="checkbox" id="wtsequentialordnum_contact_me_checkbox" name="wtsequentialordnum_contact_me_checkbox" value="1">
                            <?php esc_html_e("WebToffee can contact me about this feedback.", "wt-woocommerce-sequential-order-numbers"); ?>
                        </label>
                        <div id="wtsequentialordnum_email_field_wrap" style="display:none; margin-top:10px;">
                            <label for="wtsequentialordnum_contact_email" style="font-weight:bold;"><?php esc_html_e("Enter your email address.", "wt-woocommerce-sequential-order-numbers"); ?></label>
                            <br>
                            <input type="email" id="wtsequentialordnum_contact_email" name="wtsequentialordnum_contact_email" class="input-text" style="width:75%; height: 40px; padding:2px; margin-top:10px; border-radius:5px; border:2px solid #2874ba; padding-left:15px;" placeholder="<?php esc_attr_e("Enter email address", "wt-woocommerce-sequential-order-numbers"); ?>">
                            <div id="wtsequentialordnum_email_error" style="color:red; display:none; font-size:12px; margin-top:5px;"></div>
                        </div>

                        <div class="wtsequentialordnum_policy_infobox">
                            <?php esc_html_e("We do not collect any personal data when you submit this form. It's your feedback that we value.", "wt-woocommerce-sequential-order-numbers");?>
                            <a href="https://www.webtoffee.com/privacy-policy/" target="_blank"><?php esc_html_e('Privacy Policy', 'wt-woocommerce-sequential-order-numbers');?></a>        
                        </div>
                    </div>
                    <div class="wtsequentialordnum-modal-footer">
                        <a class="button-primary" href="https://www.webtoffee.com/contact/" target="_blank">
                            <span class="dashicons dashicons-external" style="margin-top:3px;"></span> 
                            <?php esc_html_e('Go to support', 'wt-woocommerce-sequential-order-numbers'); ?></a>
                        <button class="button-primary wtsequentialordnum-model-submit"><?php esc_html_e('Submit & Deactivate', 'wt-woocommerce-sequential-order-numbers'); ?></button>
                        <button class="button-secondary wtsequentialordnum-model-cancel"><?php esc_html_e('Cancel', 'wt-woocommerce-sequential-order-numbers'); ?></button>
                        <a href="#" class="dont-bother-me"><?php esc_html_e('I rather wouldn\'t say', 'wt-woocommerce-sequential-order-numbers'); ?></a>
                    </div>
                </div>
            </div>
            <style type="text/css">
                .wtsequentialordnum-modal {
                    position: fixed;
                    z-index: 99999;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    background: rgba(0,0,0,0.5);
                    display: none;
                }
                .wtsequentialordnum-modal.modal-active {display: block;}
                .wtsequentialordnum-modal-wrap {
                    width: 50%;
                    position: relative;
                    margin: 10% auto;
                    background: #fff;
                }
                .wtsequentialordnum-modal-header {
                    border-bottom: 1px solid #eee;
                    padding: 8px 20px;
                }
                .wtsequentialordnum-modal-header h3 {
                    line-height: 150%;
                    margin: 0;
                }
                .wtsequentialordnum-modal-body {padding: 5px 20px 5px 20px;}
                .wtsequentialordnum-modal-body .input-text,.wtsequentialordnum-modal-body textarea {width:75%;}
                .wtsequentialordnum-modal-body .input-text::placeholder,.wtsequentialordnum-modal-body textarea::placeholder{ font-size:12px; }
                .wtsequentialordnum-modal-body .reason-input {
                    margin-top: 5px;
                    margin-left: 20px;
                }
                .wtsequentialordnum-modal-footer {
                    border-top: 1px solid #eee;
                    padding: 12px 20px;
                    text-align: left;
                }
                .wtsequentialordnum_policy_infobox{font-style:italic; text-align:left; font-size:12px; color:#aaa; line-height:14px; margin-top:15px;}
                .wtsequentialordnum_policy_infobox a{ font-size:11px; color:#4b9cc3; text-decoration-color: #99c3d7; }
                .sub_reasons{ display:none; margin-left:15px; margin-top:10px; }
                a.dont-bother-me{ color:#939697; text-decoration-color:#d0d3d5; float:right; margin-top:7px; }
                .reasons li{ padding-top:5px; }
                .wtsequentialordnum-checkbox-top-border { display: block; border-top: 1px solid #ddd; margin-top: 1px; margin-bottom: 15px; width: 100%; }
            </style>
            <script type="text/javascript">
                (function ($) {
                    $(function () {
                        var modal = $('#wtsequentialordnum-wtsequentialordnum-modal');
                        var deactivateLink = '';
                        $('#the-list').on('click', 'a.wtsequentialordnum-deactivate-link', function (e) {
                            e.preventDefault();
                            modal.addClass('modal-active');
                            deactivateLink = $(this).attr('href');
                            modal.find('a.dont-bother-me').attr('href', deactivateLink);
                            modal.find('input[type="radio"]:checked').prop('checked', false);
                        });
                        modal.on('click', 'button.wtsequentialordnum-model-cancel', function (e) {
                            e.preventDefault();
                            modal.removeClass('modal-active');
                        });
                        modal.on('click', 'input[type="radio"]', function () {
                            var reason_id=$(this).val();
                            var parent = $(this).parents('li:first');
                            var inputType = parent.data('type');
                            modal.find('.reason-input').remove();
                            if($(this).attr('name')=='selected-reason')
                            {
                                modal.find('.sub_reasons').hide();
                            }
                            if(inputType=='main_reason')
                            {
                                modal.find('.sub_reasons[data-parent="'+reason_id+'"]').show();
                                modal.find('.sub_reasons[data-parent="'+reason_id+'"] input[type="radio"]:checked').trigger('click');
                            }else
                            {
                                var inputPlaceholder = parent.data('placeholder'),
                                reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';

                                if(inputType !== '')
                                {
                                    parent.append($(reasonInputHtml));
                                    parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                                }
                            }
                        });
                        modal.on('click', '#wtsequentialordnum_contact_me_checkbox', function() {
                            if ($(this).is(':checked')) {
                                $('#wtsequentialordnum_email_field_wrap').slideDown();
                            } else {
                                $('#wtsequentialordnum_email_field_wrap').slideUp();
                                $('#wtsequentialordnum_contact_email').val('');
                                $('#wtsequentialordnum_email_error').hide();
                            }
                        });
                        
                        modal.on('click', 'button.wtsequentialordnum-model-submit', function (e) {
                            e.preventDefault();
                            var button = $(this);
                            if (button.hasClass('disabled')) {
                                return;
                            }
                            var reason_id='none';
                            var reason_info='';

                            var $radio = $('input[type="radio"][name="selected-reason"]:checked', modal);
                            if($radio.length>0)
                            {
                                reason_id=$radio.val();
                                var $selected_reason = $radio.parents('li:first');
                                if($selected_reason.attr('data-type')=="main_reason")
                                {
                                    var sub_reason=$selected_reason.find('.sub_reasons');
                                    var sub_reason_input=sub_reason.find('input[type="radio"][name="selected-sub-reason"]:checked');
                                    if(sub_reason_input.length>0)
                                    {
                                        reason_id+=' | '+sub_reason_input.val();
                                        var sub_reason_info_input = sub_reason_input.parents('li:first').find('textarea, input[type="text"]');
                                        if(sub_reason_info_input.length>0)
                                        {
                                            reason_info=$.trim(sub_reason_info_input.val());
                                        }
                                    }
                                }else
                                {
                                    var reason_info_input=$selected_reason.find('textarea, input[type="text"]');
                                    if(reason_info_input.length>0)
                                    {
                                        reason_info=$.trim(reason_info_input.val());
                                    }
                                }  
                            }
                            
                            // Email validation
                            var emailCheckbox = $('#wtsequentialordnum_contact_me_checkbox');
                            var emailField = $('#wtsequentialordnum_contact_email');
                            var emailError = $('#wtsequentialordnum_email_error');
                            emailError.hide();
                            
                            if (emailCheckbox.is(':checked')) {
                                var emailVal = emailField.val();
                                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailVal || !emailPattern.test(emailVal)) {
                                    emailError.text('<?php echo esc_js(__('Please enter a valid email address.', 'wt-woocommerce-sequential-order-numbers')); ?>').show();
                                    emailField.focus();
                                    return;
                                }
                            }
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'wtsequentialordnum_submit_uninstall_reason',
                                    _wpnonce: '<?php echo esc_attr(wp_create_nonce(WT_SEQUENCIAL_ORDNUMBER_NAME));?>',
                                    reason_id: reason_id,
                                    reason_info: reason_info,
                                    user_email: $('#wtsequentialordnum_contact_me_checkbox').is(':checked') ? $('#wtsequentialordnum_contact_email').val() : ''
                                },
                                beforeSend: function () {
                                    button.addClass('disabled');
                                    button.text('Processing...');
                                },
                                complete: function () {
                                    window.location.href = deactivateLink;
                                }
                            });
                        });
                    });
                }(jQuery));
            </script>
            <?php
        }

        public function send_uninstall_reason()
        {
            global $wpdb;
            $nonce=isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : ''; 
            if(!(wp_verify_nonce($nonce,WT_SEQUENCIAL_ORDNUMBER_NAME)))
            {   
                wp_send_json_error();
            }
            if(!isset($_POST['reason_id']))
            {
                wp_send_json_error();
            }

            $data = array(
                'reason_id' => sanitize_text_field(wp_unslash($_POST['reason_id'])),
                'plugin' =>$this->plugin_id,
                'auth' =>$this->auth_key,
                'date' => gmdate("M d, Y h:i:s A"),
                'url' => '',
                'user_email' => isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '',
                'reason_info' => isset($_REQUEST['reason_info']) ? trim(stripslashes(sanitize_text_field(wp_unslash($_REQUEST['reason_info'])))) : '',
                'software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field( wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
                'php_version' => phpversion(),
                'mysql_version' => $wpdb->db_version(),
                'wp_version' => get_bloginfo('version'),
                'wc_version' => (!defined('WC_VERSION')) ? '' : WC_VERSION,
                'locale' => get_locale(),
                'languages' => implode( ",", get_available_languages() ),
                'theme' => wp_get_theme()->get('Name'),
                'multisite' => is_multisite() ? 'Yes' : 'No',
                'wtseqordnum_version' =>$this->current_version,
            );
            // Write an action/hook here in webtoffe to recieve the data
            $resp = wp_remote_post($this->api_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'body' => $data,
                'cookies' => array()
                    )
            );
            wp_send_json_success();
        }
    }
    new WT_Sequentialordnum_Uninstall_Feedback();
    
endif;