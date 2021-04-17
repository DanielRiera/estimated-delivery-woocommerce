<?php
/**
 * Plugin Name: Estimated Delivery for WooCommerce
 * Description: Show estimated / guaranteed delivery, simple and easy
 * Author: TaxarPro
 * Author URI: https://taxarpro.com
 * Version: 1.1.0
 * Text Domain: estimated-delivery-for-woocommerce
 * Domain Path: /languages
 */
if(!defined('ABSPATH')) { exit; }

define('EDW_PATH', dirname(__FILE__).'/');
define('EDW_POSITION_SHOW', get_option('_edw_position', 'woocommerce_after_add_to_cart_button'));

if(!defined('EDWCore')) {
    class EDWCore {

        static public $positions = array();

        function __construct(){
            add_action('admin_menu', array($this, 'edw_menu'));
            add_action('plugins_loaded', array($this, 'edw_load_textdomain'));
            add_action(EDW_POSITION_SHOW, array($this, 'edw_show_message'));
        }
        function edw_load_textdomain(){
            load_plugin_textdomain( 'estimated-delivery-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }
        function edw_menu() {
            add_submenu_page('woocommerce',__('Estimated Delivery','estimated-delivery-for-woocommerce'),__('Estimated Delivery','estimated-delivery-for-woocommerce') , 'manage_options', 'edw-options', array($this, 'edw_view_page_options'));
        }

        function edw_view_page_options() {

            self::$positions = array(
                'woocommerce_after_add_to_cart_button' => __('After cart button','counter-visitor-for-woocommerce'),
                'woocommerce_before_add_to_cart_button' => __('Before cart button','counter-visitor-for-woocommerce'),
                'woocommerce_product_meta_end' => __('After product meta','counter-visitor-for-woocommerce'),
                'woocommerce_before_single_product_summary' => __('Before product summary','counter-visitor-for-woocommerce'),
                'woocommerce_after_single_product_summary' => __('After product summary','counter-visitor-for-woocommerce'),
                'woocommerce_product_thumbnails' => __('Product Thumbnail (may not work)','counter-visitor-for-woocommerce'),
            );

            self::$positions = apply_filters( 'edw_positions', self::$positions );

            require_once(EDW_PATH . 'views/options.php');
        }

        private function edw_get_date($disabledDays, $daysEstimated, $dateCheck = false){
            if(!$dateCheck) {
                $dateCheck = date('Y-m-d', strtotime(" + " . $daysEstimated . " days"));
            }else{
                $dateCheck = date('Y-m-d', strtotime($dateCheck . " + 1 days"));
            }
            $filterDisabled = date('D', strtotime($dateCheck));
            if(in_array($filterDisabled, $disabledDays)) {
                return $dateCheck = $this->edw_get_date($disabledDays, $disabledDays, $dateCheck);
            }


            return $dateCheck;
        }
        /**
         * Check dates if one element (day, month or year) change between
         *
         * @param string $date1
         * @param string $date2
         * @return array
         * @since 1.0.2
         */
        private function checkDates($date1, $date2) {
            $month = false;
            $day = false;
            $year = false;

            if(date('m', strtotime($date1)) != date('m', strtotime($date2))) {
                $month = true;
            }

            if(date('d', strtotime($date1)) != date('d', strtotime($date2))) {
                $day = true;
            }

            if(date('Y', strtotime($date1)) != date('Y', strtotime($date2))) {
                $year = true;
            }

            return [$day, $month, $year];

        }
        function edw_show_message(){
            global $product;
            $mode = get_option('_edw_mode');

            if(!$mode) {
                return;
            }

            /**
             * Hide for out stock products
             * @since 1.0.3
             */
            if(!$product->is_in_stock() || $product->is_on_backorder()) {
                return;
            }
            
            $days = intval(get_option('_edw_days'));
            $maxDays = intval(get_option('_edw_max_days'));
            $disabledDays = get_option('_edw_disabled_days');


            $minDate = $this->edw_get_date($disabledDays, $days);
            $maxDate = $this->edw_get_date($disabledDays, $maxDays);

            $date_format = get_option('date_format');
            $date = date_i18n("{$date_format}", strtotime($minDate));
            if($maxDays > 0) {
                list($d, $m, $y) = $this->checkDates($minDate, $maxDate);

                if(!$d && !$m && !$y) {
                    $date = $date;
                }else{
                    if($d && !$m && !$y) {
                        //00 - 00 MM, YYYY
                        $date = date_i18n("j ", strtotime($minDate)) . ' - ' . date_i18n("j F, Y", strtotime($maxDate));
                    }elseif($d && $m && !$y) {
                        // 00 MM - 00 MM, YYYY
                        $date = date_i18n("j F", strtotime($minDate)) . ' - ' . date_i18n("j F, Y", strtotime($maxDate));
                    }else{
                        // 00 MM YYYY - 00 MM YYYY
                        $date = date_i18n("j F Y", strtotime($minDate)) . ' - ' . date_i18n("j F Y", strtotime($maxDate));
                    }
                    
                }
            }

            if($mode == "1") {
                echo '<div class="edw_date">'.sprintf(__('Estimated delivery on %s','estimated-delivery-for-woocommerce'), $date).'</div>';
            }else{
                echo '<div class="edw_date">'.sprintf(__('Guaranteed delivery on %s','estimated-delivery-for-woocommerce'), $date).'</div>';
            }
        }
    }

}
$EDWCore = new EDWCore();
?>