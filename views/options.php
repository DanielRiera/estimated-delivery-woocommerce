<?php
if(!defined('ABSPATH')) { exit; }

/**Actions */
if(isset($_POST['action'])) {
    if ( (isset($_POST['save_option_nonce']) && wp_verify_nonce(  sanitize_text_field($_POST['save_option_nonce']), 'edw_nonce' )) || (isset($_POST['add_sub_nonce']) && wp_verify_nonce(  sanitize_text_field($_POST['add_sub_nonce']), 'edw_nonce' ) )) {
        if(sanitize_text_field($_POST['action']) == 'save_options') {
            if(isset($_POST['_edw_disabled_days']) and is_array($_POST['_edw_disabled_days'])) {
                //Sanitize disabled days
                $disabledDays = array_map('sanitize_text_field', $_POST['_edw_disabled_days']);
                update_option('_edw_disabled_days', $disabledDays );
            }else{
                update_option('_edw_disabled_days', [] );
            }
            update_option('_edw_position',sanitize_text_field( $_POST['_edw_position'] ));
            update_option('_edw_max_days',sanitize_text_field( $_POST['_edw_max_days'] ));
            update_option('_edw_days',sanitize_text_field( $_POST['_edw_days'] ));
            update_option('_edw_mode',sanitize_text_field( $_POST['_edw_mode'] ));
            update_option('_edw_days_outstock',sanitize_text_field( $_POST['_edw_days_outstock'] ));
            update_option('_edw_max_days_outstock',sanitize_text_field( $_POST['_edw_max_days_outstock'] ));
            update_option('_edw_days_backorders',sanitize_text_field( $_POST['_edw_days_backorders'] ));
            update_option('_edw_max_days_backorders',sanitize_text_field( $_POST['_edw_max_days_backorders'] ));
            update_option('_edw_max_hour', sanitize_text_field($_POST['_edw_max_hour']));
            if(isset($_POST['_edw_relative_dates'])) {
                update_option('_edw_relative_dates', '1');
            }else{
                update_option('_edw_relative_dates', '0');
            }

            if(isset($_POST['_edw_same_day'])) {
                update_option('_edw_same_day', '1');
            }else{
                update_option('_edw_same_day', '0');
            }

            if(isset($_POST['_edw_cache'])) {
                update_option('_edw_cache', '1');
            }else{
                update_option('_edw_cache', '0');
            }
            
            if(isset($_POST['edw_save_date_order'])) {
                update_option('edw_save_date_order', '1');
            }else{
                update_option('edw_save_date_order', '0');
            }
            if(isset($_POST['edw_show_list'])) {
                update_option('edw_show_list', '1');
            }else{
                update_option('edw_show_list', '0');
            }
            
        }

        if ( isset($_POST['action']) && isset($_POST['add_sub_nonce']) && $_POST['action'] == 'adsub' && wp_verify_nonce(  $_POST['add_sub_nonce'], 'edw_nonce' ) ) {
            $sub = wp_remote_post( 'https://mailing.danielriera.net', [
                'method'      => 'POST',
                'timeout'     => 2000,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(),
                'body'        => array(
                    'm' => $_POST['action'],
                    'd' => base64_encode(json_encode($_POST))
                ),
                'cookies'     => array()
            ]);
            $result = json_decode($sub['body'],true);

            if($result['error']) {
                $class = 'notice notice-error';
                $message = __( 'An error has occurred, try again.', 'estimated-delivery-for-woocommerce' );
                printf( '<div class="%s"><p>%s</p></div>', $class, $message );
            }else{
                $class = 'notice notice-success';
                $message = __( 'Welcome to newsletter :)', 'estimated-delivery-for-woocommerce' );
                
                printf( '<div class="%s"><p>%s</p></div>', $class, $message );
    
                update_option('estimated-delivery-newsletter' , '1');
            }
        }
    }
}
$newsletterEstimatedDelivery = get_option('estimated-delivery-newsletter', '0');
$user = wp_get_current_user();
$disabledDays = get_option('_edw_disabled_days', []);
$currentPosition = get_option('_edw_position','woocommerce_after_add_to_cart_button');
?>
<style>
form#new_subscriber {
    background: #FFF;
    padding: 10px;
    margin-bottom: 50px;
    border-radius: 12px;
    border: 1px solid #CCC;
    width: 23%;
    text-align: center;
}

form#new_subscriber input.email {
    width: 100%;
    text-align: center;
    padding: 10px;
}

form#new_subscriber input[type='submit'] {
    width: 100%;
    margin-top: 10px;
    border: 0;
    background: #3c853c;
    color: #FFF;
}
table th {
    min-width:350px
}
</style>
<div class="wrap edwpanel">
    <h1><?=__('Estimated Delivery for Woocommerce', 'estimated-delivery-for-woocommerce')?></h1>
    <p><?=__('Show the estimated or guaranteed delivery for the product','estimated-delivery-for-woocommerce')?></p>
    <?php
        if($newsletterEstimatedDelivery == '0') { ?>
            <form class="simple_form form form-vertical" id="new_subscriber" novalidate="novalidate" accept-charset="UTF-8" method="post">
                <input name="utf8" type="hidden" value="&#x2713;" />
                <input type="hidden" name="action" value="adsub" />
                <?php wp_nonce_field( 'edw_nonce', 'add_sub_nonce' ); ?>
                <h3><?=__('Do you want to receive the latest?','estimated-delivery-for-woocommerce')?></h3>
                <p><?=__('Thank you very much for using our plugin, if you want to receive the latest news, offers, promotions, discounts, etc ... Sign up for our newsletter. :)', 'estimated-delivery-for-woocommerce')?></p>
                <div class="form-group email required subscriber_email">
                    <label class="control-label email required" for="subscriber_email"><abbr title="<?=__('Required', 'estimated-delivery-for-woocommerce')?>"> </abbr></label>
                    <input class="form-control string email required" type="email" name="e" id="subscriber_email" value="<?=$user->user_email?>" />
                </div>
                <input type="hidden" name="n" value="<?=bloginfo('name')?>" />
                <input type="hidden" name="w" value="<?=bloginfo('url')?>" />
                <input type="hidden" name="g" value="1" />
                <input type="text" name="anotheremail" id="anotheremail" style="position: absolute; left: -5000px" tabindex="-1" autocomplete="off" />
            <div class="submit-wrapper">
            <input type="submit" name="commit" value="<?=__('Submit', 'estimated-delivery-for-woocommerce')?>" class="button" data-disable-with="<?=__('Processing', 'estimated-delivery-for-woocommerce')?>" />
            </div>
        </form>
    <?php
        } //END Newsletter

    //Tabs
    $tab = 'general';
    if($tab == 'general') { 
        $currentPosition = get_option('_edw_position','woocommerce_after_add_to_cart_button');
    ?>
        <!--Donate button-->
        <div style="width:30%">
        <p><?=__('Developing this plugin takes time, so if you like it, we invite you to make a donation so that we can continue developing and updating, adding news, this will always be free.','estimated-delivery-for-woocommerce')?></p>
            <a href="https://www.paypal.com/donate/?hosted_button_id=EZ67DG78KMXWQ" target="_blank" style="text-decoration: none;font-size: 18px;border: 1px solid #333;padding: 10px;display: block;width: fit-content;border-radius: 10px;background: #FFF;"><?=__('Make a donation now to help development','estimated-delivery-for-woocommerce')?></a>
        </div>
        <br>

        <form method="post">
            <input type="hidden" name="action" value="save_options" />
            <?php wp_nonce_field( 'edw_nonce', 'save_option_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?=__('Use AJAX', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('If your site use cache system, active this option.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" value="1" name="_edw_cache" <?= get_option('_edw_cache', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Delivery same day', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('When you set 0 in any option the estimated delivery is disabled, activate this option to allow setting 0 and displaying the estimated date.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" value="1" name="_edw_same_day" <?= get_option('_edw_same_day', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Show date on order (Admin and Customer)', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('If you activate this option, the date will be stored with the order, the customer and you will be able to see the date on each product in the order.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" value="1" name="edw_save_date_order" <?= get_option('edw_save_date_order', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Show date Products Lists', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('If you activate this option date show on each product on list, Store, Search, etc. Check style (CSS) for this, bottom on this page.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" value="1" name="edw_show_list" <?= get_option('edw_show_list', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?=__('Maximum Time', 'estimated-delivery-for-woocommerce')?>
                    <p class="description">
                        <?= sprintf(__('Maximum time to consider an extra day of shipping (Server time) HH:mm now is %s', 'estimated-delivery-for-woocommerce'), wp_date('Y-m-d H:i'));?>
                    </p>
                    </th>
                    <td>
                        <label>
                        <input type="time" value="1" name="_edw_max_hour" <?= get_option('_edw_max_hour', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Use Relative Dates', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('Only work with current and next week','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="checkbox" value="1" name="_edw_relative_dates" <?= get_option('_edw_relative_dates', '0') == '1' ? 'checked="checked"' : '' ?> /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Days for Delivery', 'estimated-delivery-for-woocommerce')?>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_days" value="<?=get_option('_edw_days', '0')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Max Days for Delivery', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('Set 0 for disable. If this set more than 0 days, it will show a range.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_max_days" value="<?=get_option('_edw_max_days', '0')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')?>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_days_outstock" value="<?=get_option('_edw_days_outstock', '')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Max Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('Set 0 for disable. If this set more than 0 days, it will show a range.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_max_days_outstock" value="<?=get_option('_edw_max_days_outstock', '')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')?>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_days_backorders" value="<?=get_option('_edw_days_backorders', '')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Max Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('Set 0 for disable. If this set more than 0 days, it will show a range.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <input type="number" min="0" max="99999" name="_edw_max_days_backorders" value="<?=get_option('_edw_max_days_backorders', '')?>" /></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Estimated or Guaranteed', 'estimated-delivery-for-woocommerce')?>
                        <p class="description"><?=__('The message will change.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                        <select name="_edw_mode">
                            <option value="1" <?php selected("1", get_option('_edw_mode', '1')) ?>><?=__('Estimated','estimated-delivery-for-woocommerce');?></option>
                            <option value="2" <?php selected("2", get_option('_edw_mode')) ?>><?=__('Guaranteed','estimated-delivery-for-woocommerce');?></option>
                        </select>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Days disabled', 'estimated-delivery-for-woocommerce')?>
                        <p class="description"><?=__('Select the days that NO shipments are made.','estimated-delivery-for-woocommerce')?></p>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Mon" <?= (in_array('Mon', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Monday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Tue" <?= (in_array('Tue', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Tuesday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Wed" <?= (in_array('Wed', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Wednesday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Thu" <?= (in_array('Thu', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Thursday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Fri" <?= (in_array('Fri', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Friday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Sat" <?= (in_array('Sat', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Saturday','estimated-delivery-for-woocommerce');?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="_edw_disabled_days[]" value="Sun" <?= (in_array('Sun', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                            <?=__('Sunday','estimated-delivery-for-woocommerce');?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?=__('Position', 'estimated-delivery-for-woocommerce')?></th>
                    <td>
                        <label>
                            <select name="_edw_position">
                                <?php
                                    foreach(EDWCore::$positions as $key => $pos) {
                                        echo '<option value="'.$key.'" '.selected($key,$currentPosition).'>'.$pos.'</option>';
                                    }
                                ?>               
                            </select>
                        </label>
                    </td>
                </tr>
            </table>
            <input class="button" type="submit" value="<?=__('Save','estimated-delivery-for-woocommerce');?>">
        </form>
        <h2><?=__('Need style?', 'estimated-delivery-for-woocommerce')?></h2>
        <p><?=__('Enjoy! Paste this CSS code into your Customizer and edit as you like','estimated-delivery-for-woocommerce')?></p>
<pre>
.edw_date {
    margin: 10px 0px;
    padding: 10px;
    width: fit-content;
}

//For product list
ul.products .edw_date {
    font-size: 12px;
    color: #626262;
}
//For title checkout and cart
dt.variation-Estimateddelivery {

}
//For Value checkout and cart
dd.variation-Estimateddelivery {

}
</pre>
    <?php
    }
    
    ?>

</div>