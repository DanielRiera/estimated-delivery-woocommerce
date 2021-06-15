<?php
/**
 * Plugin Name: Estimated Delivery for WooCommerce
 * Description: Show estimated / guaranteed delivery, simple and easy
 * Author: TaxarPro
 * Author URI: https://taxarpro.com
 * Version: 1.1.3
 * Text Domain: estimated-delivery-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 3.0
 * WC tested up to: 5.2.2
 * Required WP: 5.0
 * Tested WP: 5.7.1
 */
if(!defined('ABSPATH')) { exit; }

define('EDW_PATH', dirname(__FILE__).'/');
define('EDW_POSITION_SHOW', get_option('_edw_position', 'woocommerce_after_add_to_cart_button'));
define('EDW_USE_JS', get_option('_edw_cache', '0'));
define('EDW_Version', '1.1.3');

require_once EDW_PATH . 'class.api.php';

if(!defined('EDWCore')) {
    class EDWCore {

        static public $positions = array();

        public $positionsClass = array(
            'disabled' => 'none',
            'woocommerce_after_add_to_cart_button' => 'form.cart|inside',
            'woocommerce_before_add_to_cart_button' => 'form.cart|before',
            'woocommerce_product_meta_end' => 'div.product_meta|after',
            'woocommerce_before_single_product_summary' => 'div.woocommerce-notices-wrapper|after',
            'woocommerce_after_single_product_summary' => 'div.woocommerce-tabs|after',
            'woocommerce_product_thumbnails' => 'div.woocommerce-product-gallery|inside',
        );

        function __construct(){
            add_action('admin_menu', array($this, 'edw_menu'));
            add_action('plugins_loaded', array($this, 'edw_load_textdomain'));
            
            if(EDW_USE_JS == '0') {
                add_action(EDW_POSITION_SHOW, array($this, 'edw_show_message'));
            }else{
                add_action( 'wp_footer', array($this, 'edw_show_js'), 99 );
            }
            add_action( 'wp_enqueue_scripts', array($this, 'edw_load_style') );
            add_action('save_post_product', array($this, 'edw_save_product'), 10, 3);
            add_action( 'add_meta_boxes', array($this, 'edw_create_metabox_products') );
            add_action( 'init', array($this, 'edw_add_shortcode'));

            //WCMP Compatiblity
            add_filter( 'wcmp_product_data_tabs', array($this, 'edw_wcmp_compatibility_filter_tabs') );
            add_action( 'wcmp_product_tabs_content', array($this, 'edw_wcmp_compatibility_content_tab'), 10, 3 );
            add_action( 'wcmp_process_product_object', array($this, 'edw_save_product_data'), 10, 2 );
        }

        function edw_wcmp_compatibility_filter_tabs($tabs) {
            $tabs['edw_estimate_delivery'] = array(
                'label'    => __('Estimated Delivery', 'estimated-delivery-for-woocommerce'),
                'target'   => 'edw_estimate_delivery',
                'class'    => array(),
                'priority' => 100,
            );
            return $tabs;
        }

        function edw_wcmp_compatibility_content_tab( $pro_class_obj, $product, $post ) {
            $GLOBALS["product"] = $product;
            require_once(EDW_PATH . 'views/wcmarketplace-metabox.php');     
        }

        function edw_save_product_data( $product, $post_data ) {
            if(isset($_POST['_edw_max_days'])) {
                if(isset($_POST['_edw_disabled_days']) and is_array($_POST['_edw_disabled_days'])) {
                    //Sanitize disabled days
                    $disabledDays = array_map('sanitize_text_field', $_POST['_edw_disabled_days']);
                    update_post_meta($post_data['post_ID'], '_edw_disabled_days', $disabledDays );
                }else{
                    update_post_meta($post_data['post_ID'], '_edw_disabled_days', [] );
                }
                update_post_meta($post_data['post_ID'], '_edw_max_days',sanitize_text_field( $_POST['_edw_max_days'] ));
                update_post_meta($post_data['post_ID'], '_edw_max_days',sanitize_text_field( $_POST['_edw_max_days'] ));
                update_post_meta($post_data['post_ID'], '_edw_days',sanitize_text_field( $_POST['_edw_days'] ));
                update_post_meta($post_data['post_ID'], '_edw_days_outstock',sanitize_text_field( $_POST['_edw_days_outstock'] ));
                update_post_meta($post_data['post_ID'], '_edw_max_days_outstock',sanitize_text_field( $_POST['_edw_max_days_outstock'] ));
                update_post_meta($post_data['post_ID'], '_edw_mode',sanitize_text_field( $_POST['_edw_mode'] ));
                
                if(isset($_POST['_edw_overwrite'])) {
                    update_post_meta($post_data['post_ID'], '_edw_overwrite','1');
                }else{
                    update_post_meta($post_data['post_ID'], '_edw_overwrite','0');
                }
            }
         }


        function edw_add_shortcode() {
            add_shortcode( 'estimate_delivery', array($this, 'edw_prepare_shortcode') );
        }

        function edw_prepare_shortcode($atts) {
            global $product;

            $atts = shortcode_atts( array(
                'product' => $product,                
            ), $atts, 'estimate_delivery' );

            return $this->edw_show_message($product);
        }
        
        function edw_create_metabox_products() {
            add_meta_box( 'edw_data_product', __('Estimated Delivery', 'estimated-delivery-for-woocommerce'), array($this, 'edw_content_metabox_products'), 'product', 'normal', 'high' );  
        }

        function edw_content_metabox_products($post) {
            require_once(EDW_PATH . 'views/metabox-product.php');     
        }

        function edw_save_product( $post_id, $post, $update ) {
            if(isset($_POST['_edw_max_days'])) {
                if(isset($_POST['_edw_disabled_days']) and is_array($_POST['_edw_disabled_days'])) {
                    //Sanitize disabled days
                    $disabledDays = array_map('sanitize_text_field', $_POST['_edw_disabled_days']);
                    update_post_meta($post_id, '_edw_disabled_days', $disabledDays );
                }else{
                    update_post_meta($post_id, '_edw_disabled_days', [] );
                }
                update_post_meta($post_id, '_edw_max_days',sanitize_text_field( $_POST['_edw_max_days'] ));
                update_post_meta($post_id, '_edw_max_days',sanitize_text_field( $_POST['_edw_max_days'] ));
                update_post_meta($post_id, '_edw_days',sanitize_text_field( $_POST['_edw_days'] ));
                update_post_meta($post_id, '_edw_days_outstock',sanitize_text_field( $_POST['_edw_days_outstock'] ));
                update_post_meta($post_id, '_edw_max_days_outstock',sanitize_text_field( $_POST['_edw_max_days_outstock'] ));
                update_post_meta($post_id, '_edw_mode',sanitize_text_field( $_POST['_edw_mode'] ));
                
                if(isset($_POST['_edw_overwrite'])) {
                    update_post_meta($post_id, '_edw_overwrite','1');
                }else{
                    update_post_meta($post_id, '_edw_overwrite','0');
                }
            }
        }

        function edw_show_js() {
            if(is_product() === true) {
                global $post;
                $selectPosition = explode('|', $this->positionsClass[EDW_POSITION_SHOW]);
                echo '<script>jQuery(document).ready(function($) { EDW.show('.$post->ID.',\''.json_encode($selectPosition).'\'); });</script>';
            }
        }

        function edw_load_style() {
            if(is_product() === false) { return; }
            wp_enqueue_script( 'edw-scripts', plugins_url('assets/edw_scripts.js?edw=true&v='.EDW_Version, __FILE__), array('jquery'));

            wp_localize_script( 'edw-scripts', 'edwConfig', array(
                'url'    => admin_url( 'admin-ajax.php' )
            ) );
        }

        function edw_load_textdomain(){
            load_plugin_textdomain( 'estimated-delivery-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }
        function edw_menu() {
            add_submenu_page('woocommerce',__('Estimated Delivery','estimated-delivery-for-woocommerce'),__('Estimated Delivery','estimated-delivery-for-woocommerce') , 'manage_options', 'edw-options', array($this, 'edw_view_page_options'));
        }

        function edw_view_page_options() {

            self::$positions = array(
                'disabled' => __('Disabled, use shortcode','estimated-delivery-for-woocommerce'),
                'woocommerce_after_add_to_cart_button' => __('After cart button','estimated-delivery-for-woocommerce'),
                'woocommerce_before_add_to_cart_button' => __('Before cart button','estimated-delivery-for-woocommerce'),
                'woocommerce_product_meta_end' => __('After product meta','estimated-delivery-for-woocommerce'),
                'woocommerce_before_single_product_summary' => __('Before product summary','estimated-delivery-for-woocommerce'),
                'woocommerce_after_single_product_summary' => __('After product summary','estimated-delivery-for-woocommerce'),
                'woocommerce_product_thumbnails' => __('Product Thumbnail (may not work)','estimated-delivery-for-woocommerce'),
            );

            self::$positions = apply_filters( 'edw_positions', self::$positions );

            require_once(EDW_PATH . 'views/options.php');
        }

        private function edw_get_date($disabledDays, $daysEstimated, $dateCheck = false){
            if(count($disabledDays) == 7) {
                return false;
            }
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
        function edw_show_message($productParam = false){
            global $product;
            $returnResult = false;
            if($productParam) {
                $product = wc_get_product($productParam);
                $returnResult = true;
            }
            
            if($product == NULL) {
               
                return '';   
            }
            
            if(isset($_POST['type']) and $_POST['type'] == 'variation') {
                $product_id = $product->get_parent_id();
            }else{
                $product_id = $product->get_id();
            }
            $productActive = get_post_meta($product_id, '_edw_overwrite', true);
            
            if($productActive == '1') {
                $mode = get_post_meta($product_id,'_edw_mode', true);
            }else{
                $mode = get_option('_edw_mode');
            }
            
            
            if(!$mode) {
                return;
            }
            /**
             * Hide for out stock products
             * @since 1.0.3
             */
            
            if(!$product->is_in_stock() || $product->is_on_backorder()) {
                if($productActive == '1') {
                    $days = intval(get_post_meta($product_id,'_edw_days_outstock', true));
                    $maxDays = intval(get_post_meta($product_id,'_edw_max_days_outstock', true));
                    $disabledDays = get_post_meta($product_id,'_edw_disabled_days', true);
                }else{
                    $maxDays = intval(get_option('_edw_max_days_outstock'));
                    $days = intval(get_option('_edw_days_outstock'));
                    $disabledDays = get_option('_edw_disabled_days');
                    
                }

            }else{
                //Check Product configuration

                if($productActive == '1') {
                    $days = intval(get_post_meta($product_id,'_edw_days', true));
                    $maxDays = intval(get_post_meta($product_id,'_edw_max_days', true));
                    $disabledDays = get_post_meta($product_id,'_edw_disabled_days', true);
                    
                }else{
                    $days = intval(get_option('_edw_days'));
                    $maxDays = intval(get_option('_edw_max_days'));
                    $disabledDays = get_option('_edw_disabled_days');
                }

            }
           
        
            
            $minDate = $this->edw_get_date($disabledDays, $days);
            $maxDate = $this->edw_get_date($disabledDays, $maxDays);

            

            if($minDate && $maxDate) {
                $date_format = get_option('date_format');
                $useRelativeDates = get_option('_edw_relative_dates', false);
                $elon = __(" on", "estimated-delivery-for-woocommerce");
                $date = date_i18n("{$date_format}", strtotime($minDate));
                if($maxDays > 0) {
                    list($d, $m, $y) = $this->checkDates($minDate, $maxDate);
    
                    if(!$d && !$m && !$y) {
                        $thisWeek = date('W');
                        if( $useRelativeDates && $thisWeek == date('W', strtotime($minDate)) ) {
                            $elon = "";
                            $date = sprintf( __("this %s, %s", "estimated-delivery-for-woocommerce"), date_i18n("l", strtotime($minDate)), date_i18n("j F", strtotime($minDate)));
                        }elseif( $useRelativeDates && ($thisWeek+1) == date('W', strtotime($minDate)) ) {
                            $elon = "";
                            $date = sprintf( __("the next %s, %s", "estimated-delivery-for-woocommerce"), date_i18n("l", strtotime($minDate)), date_i18n("j F", strtotime($minDate)));
                        }
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
                }else{
                    $thisWeek = date('W');
                    if( $useRelativeDates &&  $thisWeek == date('W', strtotime($minDate)) ) {
                        $elon = "";
                        $date = sprintf( __("this %s, %s", "estimated-delivery-for-woocommerce"), date_i18n("l", strtotime($minDate)), date_i18n("j F", strtotime($minDate)));
                    }elseif( $useRelativeDates && ($thisWeek+1) == date('W', strtotime($minDate)) ) {
                        $elon = "";
                        $date = sprintf( __("the next %s, %s", "estimated-delivery-for-woocommerce"), date_i18n("l", strtotime($minDate)), date_i18n("j F", strtotime($minDate)));
                    }
                }
                
               

                if($mode == "1") {
                    $string = '<div class="edw_date">'.sprintf(__('Estimated delivery%s %s','estimated-delivery-for-woocommerce'), $elon, $date).'</div>';
                }else{
                    $string = '<div class="edw_date">'.sprintf(__('Guaranteed delivery%s %s','estimated-delivery-for-woocommerce'), $elon, $date).'</div>';
                }
    
                if($returnResult) {
                    return $string;
                }
    
                echo $string;
            }

            return '';

        }
    }

}
$EDWCore = new EDWCore();
?>
