
<script defer src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<?php
if(!defined('ABSPATH')) { exit; }

/**Actions */

if (isset($_POST['_edw_location_rules'])) {
    $decoded = json_decode(stripslashes($_POST['_edw_location_rules']), true);
    if (is_array($decoded)) {
        update_option('_edw_location_rules', $decoded);
    }
}

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
            update_option('_edw_holidays_dates', sanitize_textarea_field($_POST['_edw_holidays_dates']));
            update_option('_edw_icon', sanitize_text_field($_POST['_edw_icon']));
            update_option('_edw_custom_message', sanitize_text_field($_POST['_edw_custom_message']));

            //Format dates
            update_option('_edw_date_format_1_0', sanitize_textarea_field($_POST['_edw_date_format_1_0']));
            update_option('_edw_date_format_1_1', sanitize_textarea_field($_POST['_edw_date_format_1_1']));
            update_option('_edw_date_format_2_0', sanitize_textarea_field($_POST['_edw_date_format_2_0']));
            update_option('_edw_date_format_2_1', sanitize_textarea_field($_POST['_edw_date_format_2_1']));
            update_option('_edw_date_format_3_0', sanitize_textarea_field($_POST['_edw_date_format_3_0']));
            update_option('_edw_date_format_3_1', sanitize_textarea_field($_POST['_edw_date_format_3_1']));


            if(isset($_POST['_edw_relative_dates'])) {
                update_option('_edw_relative_dates', '1');
            }else{
                update_option('_edw_relative_dates', '0');
            }

            if(isset($_POST['_edw_icon']) and sanitize_text_field($_POST['_edw_icon']) != '' and isset($_POST['_edw_fontawesome'])) {
                update_option('_edw_fontawesome','1');
            }else{
                update_option('_edw_fontawesome','0');
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
$displayCustom = 'none';

if(get_option('_edw_mode', '1') === '3') {
    $displayCustom = 'block';
}
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
<div class="max-w-6xl mx-auto p-6 space-y-8">
    <?php
    edw_get_delivery_days_by_location()
    ?>
    <!-- Header -->
    <div class="flex items-center flex-row bg-white shadow rounded-2xl p-6">
        <div class="mr-4">
            <img src="<?php echo plugin_dir_url( __DIR__ ); ?>assets/logo.png"  alt="Estimated Delivery Logo"/>
        </div>
        <div>
            <h1 class="text-3xl font-bold mb-2"><?php echo __('Estimated Delivery for WooCommerce', 'estimated-delivery-for-woocommerce'); ?></h1>
            <p class="text-gray-600"><?php echo __('Show the estimated or guaranteed delivery for the product', 'estimated-delivery-for-woocommerce'); ?></p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div x-data="{ tab: 'general' }" class="bg-white shadow rounded-2xl">
        <div class="border-b border-gray-200 px-6 pt-6">
            <nav class="-mb-px flex space-x-6">
                <button @click="tab = 'general'" :class="{ 'border-blue-500 text-blue-600': tab === 'general' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                    <?php echo __('General Settings', 'estimated-delivery-for-woocommerce'); ?>
                </button>
                <button @click="tab = 'stock'" :class="{ 'border-blue-500 text-blue-600': tab === 'stock' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                    <?php echo __('Stock & Backorders', 'estimated-delivery-for-woocommerce'); ?>
                </button>
                <button @click="tab = 'format'" :class="{ 'border-blue-500 text-blue-600': tab === 'format' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                    <?php echo __('Date Format & Appearance', 'estimated-delivery-for-woocommerce'); ?>
                </button>
                <button @click="tab = 'other'" :class="{ 'border-blue-500 text-blue-600': tab === 'other' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                    <?php echo __('Other Settings', 'estimated-delivery-for-woocommerce'); ?>
                </button>

                <button @click="tab = 'location'" :class="{ 'border-blue-500 text-blue-600': tab === 'location' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                    <?php echo __('By Location', 'estimated-delivery-for-woocommerce'); ?>
                </button>
            </nav>
        </div>

        <form method="post" class="p-6 space-y-6">
            <?php wp_nonce_field('edw_nonce', 'save_option_nonce'); ?>
            <input type="hidden" name="action" value="save_options" />

            <!-- Tab Content -->
            <div x-show="tab === 'general'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Use AJAX -->
                <div>
                    <label class="block font-semibold">
                        <input type="checkbox" value="1" name="_edw_cache" <?php checked('1', get_option('_edw_cache', '0')); ?> class="mt-2">
                        <?php echo __('Use AJAX', 'estimated-delivery-for-woocommerce'); ?>
                    </label>
                    <p class="text-sm text-gray-500"><?php echo __('If your site use cache system, active this option.', 'estimated-delivery-for-woocommerce'); ?></p>

                </div>
                <!-- Delivery same day -->
                <div>
                    <label class="block font-semibold">
                        <input type="checkbox" value="1" name="_edw_same_day" <?php checked('1', get_option('_edw_same_day', '0')); ?> class="mt-2">
                        <?php echo __('Delivery same day', 'estimated-delivery-for-woocommerce'); ?>
                    </label>
                    <p class="text-sm text-gray-500"><?php echo __('When you set 0 in any option the estimated delivery is disabled, activate this option to allow setting 0 and displaying the estimated date.', 'estimated-delivery-for-woocommerce'); ?></p>

                </div>
                <!-- Estimated or Guaranteed -->
                <div>
                    <label class="block font-semibold"><?php echo __('Estimated or Guaranteed', 'estimated-delivery-for-woocommerce'); ?></label>
                    <p class="text-sm text-gray-500"><?php echo __('The message will change.', 'estimated-delivery-for-woocommerce'); ?></p>
                    <select name="_edw_mode" class="input input-bordered w-full mt-2">
                        <option value="1" <?php selected("1", get_option('_edw_mode', '1')); ?>><?php echo __('Estimated', 'estimated-delivery-for-woocommerce'); ?></option>
                        <option value="2" <?php selected("2", get_option('_edw_mode')); ?>><?php echo __('Guaranteed', 'estimated-delivery-for-woocommerce'); ?></option>
                        <option value="3" <?php selected("3", get_option('_edw_mode')); ?>><?php echo __('Custom', 'estimated-delivery-for-woocommerce'); ?></option>
                    </select>
                </div>
                <div class="edw_block_custom_message" style="display: <?php echo $displayCustom ?>">
                        <label class="block font-semibold"><?php echo __('Custom Message', 'estimated-delivery-for-woocommerce'); ?></label>
                        <p class="text-sm text-gray-500"><?php echo __('The custom message', 'estimated-delivery-for-woocommerce'); ?></p>
                        <input type="text" placeholder="<?php echo __('Custom message', 'estimated-delivery-for-woocommerce') ?>" class="block mt-4 p-4 w-full"  name="_edw_custom_message" value="<?php echo get_option('_edw_custom_message', ''); ?>" />
                </div>
                <!-- Relative Dates -->
                <div>
                    <label class="block font-semibold">
                        <input type="checkbox" value="1" name="_edw_relative_dates" <?php checked('1', get_option('_edw_relative_dates', '0')); ?> class="mt-2">
                        <?php echo __('Use Relative Dates', 'estimated-delivery-for-woocommerce'); ?>
                    </label>
                    <p class="text-sm text-gray-500"><?php echo __('Only work with current and next week', 'estimated-delivery-for-woocommerce'); ?></p>

                </div>
                <!-- Max Hour -->
                <div>
                    <label class="block font-semibold">
                        <?php echo __('Maximum Time', 'estimated-delivery-for-woocommerce'); ?>
                    </label>
                    <p class="text-sm text-gray-500"><?php echo sprintf(__('Maximum time to consider an extra day of shipping (Server time) HH:mm now is %s', 'estimated-delivery-for-woocommerce'), wp_date('Y-m-d H:i')); ?></p>
                    <input type="time" name="_edw_max_hour" value="<?php echo get_option('_edw_max_hour', '') ?>" class="input input-bordered w-full">
                </div>
            </div>

            <div x-show="tab === 'stock'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Days & Max Days per scenario (regular, outstock, backorder) -->
                <?php
                $fields = [
                    ['_edw_days', __('Days for Delivery', 'estimated-delivery-for-woocommerce')],
                    ['_edw_max_days', __('Max Days for Delivery', 'estimated-delivery-for-woocommerce')],
                    ['_edw_days_outstock', __('Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')],
                    ['_edw_max_days_outstock', __('Max Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')],
                    ['_edw_days_backorders', __('Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')],
                    ['_edw_max_days_backorders', __('Max Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')],
                ];
                foreach ($fields as [$name, $label]) {
                    $value = get_option($name, '');
                    echo "<div>
                            <label class=\"block font-semibold\">{$label}</label>
                            <input type=\"number\" name=\"{$name}\" value=\"{$value}\" min=\"0\" max=\"99999\" class=\"input input-bordered w-full\">
                          </div>";
                }
                ?>
            </div>

            <div x-show="tab === 'format'" class="space-y-6">
                <!-- Date Formats -->
                <div>
                    <label class="block font-semibold"><?php echo __('Date Formats', 'estimated-delivery-for-woocommerce'); ?></label>
                    <div class="grid md:grid-cols-2 gap-4 mt-2">
                        <div>
                            <p class="text-sm text-gray-500"><?php echo __('Same month and year, different day (00 - 00 MM, YYYY)', 'estimated-delivery-for-woocommerce'); ?></p>
                            <input type="text" name="_edw_date_format_1_0" value="<?php echo get_option('_edw_date_format_1_0', 'j'); ?>" class="input input-bordered w-full">
                            <input type="text" name="_edw_date_format_1_1" value="<?php echo get_option('_edw_date_format_1_1', 'j F, Y'); ?>" class="input input-bordered w-full mt-2">
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php echo __('Same year, different day and month (00 MM - 00 MM, YYYY)', 'estimated-delivery-for-woocommerce'); ?></p>
                            <input type="text" name="_edw_date_format_2_0" value="<?php echo get_option('_edw_date_format_2_0', 'j F'); ?>" class="input input-bordered w-full">
                            <input type="text" name="_edw_date_format_2_1" value="<?php echo get_option('_edw_date_format_2_1', 'j F, Y'); ?>" class="input input-bordered w-full mt-2">
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php echo __('All different (00 MM YYYY - 00 MM YYYY)', 'estimated-delivery-for-woocommerce'); ?></p>
                            <input type="text" name="_edw_date_format_3_0" value="<?php echo get_option('_edw_date_format_3_0', 'j F Y'); ?>" class="input input-bordered w-full">
                            <input type="text" name="_edw_date_format_3_1" value="<?php echo get_option('_edw_date_format_3_1', 'j F, Y'); ?>" class="input input-bordered w-full mt-2">
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'other'" class="space-y-6">
                <!-- Disabled Days -->
                <div>
                    <label class="block font-semibold"><?php echo __('Days disabled', 'estimated-delivery-for-woocommerce'); ?></label>
                    <p class="text-sm text-gray-500 mb-2"><?php echo __('Select the days that NO shipments are made.', 'estimated-delivery-for-woocommerce'); ?></p>
                    <div class="flex flex-wrap gap-4">
                        <?php
                        $days = [
                            'Mon' => __('Mon', 'estimated-delivery-for-woocommerce'),
                            'Tue' => __('Tue', 'estimated-delivery-for-woocommerce'),
                            'Wed' => __('Wed', 'estimated-delivery-for-woocommerce'),
                            'Thu' => __('Thu', 'estimated-delivery-for-woocommerce'),
                            'Fri' => __('Fri', 'estimated-delivery-for-woocommerce'),
                            'Sat' => __('Sat', 'estimated-delivery-for-woocommerce'),
                            'Sun' => __('Sun', 'estimated-delivery-for-woocommerce')
                        ];
                        foreach ($days as $value => $day) {
                            echo "<label class=\"flex items-center gap-2\">
                        <input type=\"checkbox\" name=\"_edw_disabled_days[]\" value=\"{$value}\"" . (in_array($value, $disabledDays) ? ' checked="checked"' : '') . ">
                        ".$day."</label>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Icon & FontAwesome -->
                <div>
                    <label class="block font-semibold"><?php echo __('Icon', 'estimated-delivery-for-woocommerce'); ?></label>
                    <p class="text-sm text-gray-500"><?php echo __('Only class name from FontAwesome (ex: fas fa-truck)', 'estimated-delivery-for-woocommerce'); ?></p>
                    <input type="text" name="_edw_icon" value="<?php echo get_option('_edw_icon', '') ?>" class="input input-bordered w-full">
                </div>

                <div>
                    <label class="block font-semibold"><?php echo __('¿Problem with Icon?', 'estimated-delivery-for-woocommerce'); ?></label>
                    <input type="checkbox" name="_edw_fontawesome" value="1" <?php checked('1', get_option('_edw_fontawesome', '0')); ?> class="mt-2">
                </div>

                <!-- Holidays -->
                <div>
                    <label class="block font-semibold"><?php echo __('Holidays Dates', 'estimated-delivery-for-woocommerce'); ?></label>
                    <p class="text-sm text-gray-500"><?php echo __('Dates with comma separated, YYYY/MM/DD. Use XXXX for dynamic year (e.g., XXXX/12/31)', 'estimated-delivery-for-woocommerce'); ?></p>
                    <textarea name="_edw_holidays_dates" rows="6" class="input input-bordered w-full"><?php echo get_option('_edw_holidays_dates', '') ?></textarea>
                </div>

                <!-- Position -->
                <div>
                    <label class="block font-semibold"><?php echo __('Position', 'estimated-delivery-for-woocommerce'); ?></label>
                    <select name="_edw_position" class="input input-bordered w-full mt-2">
                        <?php
                        foreach(EDWCore::$positions as $key => $pos) {
                            echo '<option value="' . $key . '" ' . selected($key, $currentPosition, false) . '>' . $pos . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div x-show="tab === 'location'" x-data="deliveryConfig()" class="space-y-6">
                <h2 class="text-xl font-bold mb-2"><?php echo __('Location-based Delivery Settings', 'estimated-delivery-for-woocommerce'); ?></h2>
                <p class="text-gray-600"><?php echo __('Define custom delivery times per country and state. You can leave the state blank to apply the rule to all states in that country.', 'estimated-delivery-for-woocommerce'); ?></p>

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-1/3">
                        <label class="block font-semibold"><?php echo __('Country', 'estimated-delivery-for-woocommerce'); ?></label>
                        <select x-model="newCountry" class="input input-bordered w-full edw-country-select">
                            <option value=""><?php echo __('Select a country', 'estimated-delivery-for-woocommerce'); ?></option>
                            <?php foreach (WC()->countries->get_countries() as $code => $label): ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="w-full md:w-1/3">
                        <label class="block font-semibold"><?php echo __('State (optional)', 'estimated-delivery-for-woocommerce'); ?></label>
                        <select x-model="newState" class="input input-bordered w-full edw-state-select">
                            <option value=""><?php echo __('Select a state (optional)', 'estimated-delivery-for-woocommerce'); ?></option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/3 flex items-end">
                        <button type="button" @click="addRule()" class="bg-purple-200 p-3 rounded text-white w-full">
                            <?php echo __('Add Location Rule', 'estimated-delivery-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>

                <template x-for="(states, country) in countries" :key="country">
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-bold text-lg mb-2" x-text="country + ' - ' + (countryLabels[country] || '')"></h3>
                        <template x-for="(rule, state) in states" :key="state">
                            <div class="mb-3 p-4 bg-white rounded shadow-sm flex flex-col md:flex-row items-center gap-4">
                                <div class="flex-1">
                                    <span class="font-semibold text-sm">
                                      <template x-if="state === 'default'">
                                        <span><?php echo __('All states', 'estimated-delivery-for-woocommerce'); ?></span>
                                      </template>
                                      <template x-if="state !== 'default'">
                                        <span x-text="state + ' - ' + (stateLabels[country]?.[state] || '')"></span>
                                      </template>
                                    </span>
                                </div>
                                <div class="flex flex-col md:flex-row gap-2">
                                    <label class="flex items-center gap-2">
                                        <span><?php echo __('Days', 'estimated-delivery-for-woocommerce'); ?></span>
                                        <input type="number" min="0" max="999" class="input input-sm input-bordered w-20" x-model.number="rule.days">
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <span><?php echo __('Max Days', 'estimated-delivery-for-woocommerce'); ?></span>
                                        <input type="number" min="0" max="999" class="input input-sm input-bordered w-24" x-model.number="rule.max_days">
                                    </label>
                                    <button type="button" @click="removeRule(country, state)" class="text-red-600 hover:underline">
                                        <?php echo __('Remove', 'estimated-delivery-for-woocommerce'); ?>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <button type="button" @click="save($refs.configInput)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200 ease-in-out mt-6 flex items-center justify-center gap-2"><?php echo __('Save All', 'estimated-delivery-for-woocommerce'); ?></button>
                <div class="pt-4">
                    <input type="hidden" name="_edw_location_rules" x-ref="configInput">
                </div>
                <input type="hidden" name="_edw_location_rules" x-ref="configInput">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200 ease-in-out mt-6 flex items-center justify-center gap-2"><?php echo __('Save', 'estimated-delivery-for-woocommerce'); ?></button>
        </form>
    </div>
</div>

<script>
    function deliveryConfig() {
        return {
            countries: <?php echo json_encode(get_option('_edw_location_rules', []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            countryLabels: <?php echo json_encode(WC()->countries->get_countries()); ?>,
            stateLabels: <?php echo json_encode(WC()->countries->get_states()); ?>,
            newCountry: '',
            newState: '',
            addRule() {
                const country = this.newCountry.toUpperCase().trim();
                const state = this.newState.toUpperCase().trim() || 'default';
                if (!country) return;

                if (!this.countries[country]) {
                    this.countries[country] = {};
                }

                this.countries[country][state] = {
                    days: 2,
                    max_days: 5
                };

                // Trigger reactivity
                this.countries = Object.assign({}, this.countries);

                this.newCountry = '';
                this.newState = '';
            },
            removeRule(country, state) {
                delete this.countries[country][state];
                if (Object.keys(this.countries[country]).length === 0) {
                    delete this.countries[country];
                }

                // Trigger reactivity
                this.countries = Object.assign({}, this.countries);
            },
            save(ref) {
                ref.value = JSON.stringify(this.countries);
                ref.closest('form').submit();
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const countriesSelect = document.querySelector('.edw-country-select');
        const statesSelect = document.querySelector('.edw-state-select');
        const modeMessage = document.querySelector("select[name='_edw_mode']")
        const custonMessageInput = document.querySelector(".edw_block_custom_message")
        modeMessage?.addEventListener("change", function() {
           if(this.value === '3') {
               custonMessageInput.style.display = 'block';
           }else{
               custonMessageInput.style.display = 'none';
           }
        });
        countriesSelect?.addEventListener('change', function () {
            const country = this.value;
            const states = edwStates[country] || {};

            statesSelect.innerHTML = '<option value=""><?php echo __('All states', 'estimated-delivery-for-woocommerce'); ?></option>';

            Object.entries(states).forEach(([code, name]) => {
                const option = document.createElement('option');
                option.value = code;
                option.textContent = name;
                statesSelect.appendChild(option);
            });
        });
    });

    const edwStates = <?php echo json_encode(WC()->countries->get_states()); ?>;
</script>


