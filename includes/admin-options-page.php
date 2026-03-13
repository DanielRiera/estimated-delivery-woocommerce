<?php
if (!defined('ABSPATH')) { exit; }

$GLOBALS['edw_admin_options_notice'] = null;

function edw_admin_get_setting_definitions() {
    return [
        '_edw_disabled_days' => ['type' => 'array_text', 'default' => []],
        '_edw_position' => ['type' => 'text', 'default' => 'woocommerce_after_add_to_cart_button'],
        '_edw_max_days' => ['type' => 'text', 'default' => ''],
        '_edw_days' => ['type' => 'text', 'default' => ''],
        '_edw_mode' => ['type' => 'text', 'default' => '1'],
        '_edw_days_outstock' => ['type' => 'text', 'default' => ''],
        '_edw_max_days_outstock' => ['type' => 'text', 'default' => ''],
        '_edw_days_backorders' => ['type' => 'text', 'default' => ''],
        '_edw_max_days_backorders' => ['type' => 'text', 'default' => ''],
        '_edw_max_hour' => ['type' => 'text', 'default' => ''],
        '_edw_holidays_dates' => ['type' => 'textarea', 'default' => ''],
        '_edw_icon' => ['type' => 'text', 'default' => ''],
        '_edw_custom_message' => ['type' => 'text', 'default' => ''],
        '_edw_date_format_1_0' => ['type' => 'textarea', 'default' => 'j'],
        '_edw_date_format_1_1' => ['type' => 'textarea', 'default' => 'j F, Y'],
        '_edw_date_format_2_0' => ['type' => 'textarea', 'default' => 'j F'],
        '_edw_date_format_2_1' => ['type' => 'textarea', 'default' => 'j F, Y'],
        '_edw_date_format_3_0' => ['type' => 'textarea', 'default' => 'j F Y'],
        '_edw_date_format_3_1' => ['type' => 'textarea', 'default' => 'j F, Y'],
        '_edw_relative_dates' => ['type' => 'checkbox', 'default' => '0'],
        '_edw_fontawesome' => ['type' => 'checkbox', 'default' => '0'],
        '_edw_same_day' => ['type' => 'checkbox', 'default' => '0'],
        '_edw_cache' => ['type' => 'checkbox', 'default' => '0'],
        'edw_save_date_order' => ['type' => 'checkbox', 'default' => '0'],
        'edw_show_list' => ['type' => 'checkbox', 'default' => '0'],
        '_edw_location_rules' => ['type' => 'location_rules', 'default' => []],
    ];
}

function edw_admin_get_settings_values() {
    $values = [];

    foreach (edw_admin_get_setting_definitions() as $option_name => $definition) {
        $values[$option_name] = get_option($option_name, $definition['default']);
    }

    return $values;
}

function edw_admin_sanitize_location_rules($raw_value) {
    $decoded = json_decode(wp_unslash((string) $raw_value), true);
    if (!is_array($decoded)) {
        return [];
    }

    $rules = [];
    foreach ($decoded as $country => $states) {
        $country_code = strtoupper(sanitize_text_field($country));
        if (!$country_code || !is_array($states)) {
            continue;
        }

        foreach ($states as $state => $rule) {
            $state_code = $state === 'default' ? 'default' : strtoupper(sanitize_text_field($state));
            if (!$state_code || !is_array($rule)) {
                continue;
            }

            $rules[$country_code][$state_code] = [
                'days' => isset($rule['days']) ? intval($rule['days']) : 0,
                'max_days' => isset($rule['max_days']) ? intval($rule['max_days']) : 0,
            ];
        }
    }

    return $rules;
}

function edw_admin_save_settings($post_data) {
    if (!current_user_can('manage_options')) {
        return [
            'type' => 'error',
            'message' => __('You do not have permission to manage these settings.', 'estimated-delivery-for-woocommerce'),
        ];
    }

    $definitions = edw_admin_get_setting_definitions();
    foreach ($definitions as $option_name => $definition) {
        switch ($definition['type']) {
            case 'checkbox':
                update_option($option_name, isset($post_data[$option_name]) ? '1' : '0');
                break;
            case 'array_text':
                $value = isset($post_data[$option_name]) && is_array($post_data[$option_name])
                    ? array_map('sanitize_text_field', wp_unslash($post_data[$option_name]))
                    : [];
                update_option($option_name, $value);
                break;
            case 'textarea':
                update_option($option_name, sanitize_textarea_field(wp_unslash($post_data[$option_name] ?? '')));
                break;
            case 'location_rules':
                update_option($option_name, edw_admin_sanitize_location_rules($post_data[$option_name] ?? ''));
                break;
            default:
                update_option($option_name, sanitize_text_field(wp_unslash($post_data[$option_name] ?? '')));
                break;
        }
    }

    if (empty($post_data['_edw_icon']) || !isset($post_data['_edw_fontawesome'])) {
        update_option('_edw_fontawesome', '0');
    }

    return [
        'type' => 'success',
        'message' => __('Settings saved.', 'estimated-delivery-for-woocommerce'),
    ];
}

function edw_admin_subscribe_newsletter($post_data) {
    if (!current_user_can('manage_options')) {
        return [
            'type' => 'error',
            'message' => __('You do not have permission to manage these settings.', 'estimated-delivery-for-woocommerce'),
        ];
    }

    $response = wp_remote_post('https://mailing.danielriera.net', [
        'method' => 'POST',
        'timeout' => 2000,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => [],
        'body' => [
            'm' => sanitize_text_field($post_data['action'] ?? ''),
            'd' => base64_encode(wp_json_encode(wp_unslash($post_data))),
        ],
        'cookies' => [],
    ]);

    if (is_wp_error($response)) {
        return [
            'type' => 'error',
            'message' => __('An error has occurred, try again.', 'estimated-delivery-for-woocommerce'),
        ];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['error'])) {
        return [
            'type' => 'error',
            'message' => __('An error has occurred, try again.', 'estimated-delivery-for-woocommerce'),
        ];
    }

    update_option('estimated-delivery-newsletter', '1');

    return [
        'type' => 'success',
        'message' => __('Welcome to newsletter :)', 'estimated-delivery-for-woocommerce'),
    ];
}

function edw_admin_handle_options_page_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action'])) {
        return null;
    }

    $action = sanitize_text_field(wp_unslash($_POST['action']));

    if ($action === 'save_options') {
        if (!isset($_POST['save_option_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['save_option_nonce'])), 'edw_nonce')) {
            return [
                'type' => 'error',
                'message' => __('Security check failed.', 'estimated-delivery-for-woocommerce'),
            ];
        }

        return edw_admin_save_settings($_POST);
    }

    if ($action === 'adsub') {
        if (!isset($_POST['add_sub_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['add_sub_nonce'])), 'edw_nonce')) {
            return [
                'type' => 'error',
                'message' => __('Security check failed.', 'estimated-delivery-for-woocommerce'),
            ];
        }

        return edw_admin_subscribe_newsletter($_POST);
    }

    return null;
}

function edw_admin_set_options_notice($notice) {
    $GLOBALS['edw_admin_options_notice'] = $notice;
}

function edw_admin_get_options_notice() {
    return $GLOBALS['edw_admin_options_notice'];
}

function edw_admin_get_tabs() {
    return [
        'general' => __('General Settings', 'estimated-delivery-for-woocommerce'),
        'stock' => __('Stock & Backorders', 'estimated-delivery-for-woocommerce'),
        'format' => __('Date Format & Appearance', 'estimated-delivery-for-woocommerce'),
        'other' => __('Other Settings', 'estimated-delivery-for-woocommerce'),
        'location' => __('By Location', 'estimated-delivery-for-woocommerce'),
    ];
}

function edw_admin_get_general_fields() {
    return [
        [
            'type' => 'checkbox',
            'name' => '_edw_cache',
            'label' => __('Use AJAX', 'estimated-delivery-for-woocommerce'),
            'description' => __('Load the delivery message dynamically with AJAX on product pages, product lists and shortcode output to avoid stale cached HTML.', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'checkbox',
            'name' => '_edw_same_day',
            'label' => __('Delivery same day', 'estimated-delivery-for-woocommerce'),
            'description' => __('When you set 0 in any option the estimated delivery is disabled, activate this option to allow setting 0 and displaying the estimated date.', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'select',
            'name' => '_edw_mode',
            'label' => __('Estimated or Guaranteed', 'estimated-delivery-for-woocommerce'),
            'description' => __('The message will change.', 'estimated-delivery-for-woocommerce'),
            'options' => [
                '1' => __('Estimated', 'estimated-delivery-for-woocommerce'),
                '2' => __('Guaranteed', 'estimated-delivery-for-woocommerce'),
                '3' => __('Custom', 'estimated-delivery-for-woocommerce'),
            ],
        ],
        [
            'type' => 'text',
            'name' => '_edw_custom_message',
            'label' => __('Custom Message', 'estimated-delivery-for-woocommerce'),
            'description' => __('The custom message', 'estimated-delivery-for-woocommerce'),
            'placeholder' => __('Custom message', 'estimated-delivery-for-woocommerce'),
            'wrapper_class' => 'edw_block_custom_message',
        ],
        [
            'type' => 'checkbox',
            'name' => '_edw_relative_dates',
            'label' => __('Use Relative Dates', 'estimated-delivery-for-woocommerce'),
            'description' => __('Only work with current and next week', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'time',
            'name' => '_edw_max_hour',
            'label' => __('Maximum Time', 'estimated-delivery-for-woocommerce'),
            'description' => sprintf(__('Maximum time to consider an extra day of shipping (Server time) HH:mm now is %s', 'estimated-delivery-for-woocommerce'), wp_date('Y-m-d H:i')),
        ],
    ];
}

function edw_admin_get_stock_fields() {
    return [
        ['name' => '_edw_days', 'label' => __('Days for Delivery', 'estimated-delivery-for-woocommerce')],
        ['name' => '_edw_max_days', 'label' => __('Max Days for Delivery', 'estimated-delivery-for-woocommerce')],
        ['name' => '_edw_days_outstock', 'label' => __('Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')],
        ['name' => '_edw_max_days_outstock', 'label' => __('Max Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')],
        ['name' => '_edw_days_backorders', 'label' => __('Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')],
        ['name' => '_edw_max_days_backorders', 'label' => __('Max Days for Delivery Backorders', 'estimated-delivery-for-woocommerce')],
    ];
}

function edw_admin_get_date_format_groups() {
    return [
        [
            'description' => __('Same month and year, different day (00 - 00 MM, YYYY)', 'estimated-delivery-for-woocommerce'),
            'fields' => [
                ['name' => '_edw_date_format_1_0', 'default' => 'j'],
                ['name' => '_edw_date_format_1_1', 'default' => 'j F, Y'],
            ],
        ],
        [
            'description' => __('Same year, different day and month (00 MM - 00 MM, YYYY)', 'estimated-delivery-for-woocommerce'),
            'fields' => [
                ['name' => '_edw_date_format_2_0', 'default' => 'j F'],
                ['name' => '_edw_date_format_2_1', 'default' => 'j F, Y'],
            ],
        ],
        [
            'description' => __('All different (00 MM YYYY - 00 MM YYYY)', 'estimated-delivery-for-woocommerce'),
            'fields' => [
                ['name' => '_edw_date_format_3_0', 'default' => 'j F Y'],
                ['name' => '_edw_date_format_3_1', 'default' => 'j F, Y'],
            ],
        ],
    ];
}

function edw_admin_get_other_fields($positions) {
    return [
        [
            'type' => 'text',
            'name' => '_edw_icon',
            'label' => __('Icon', 'estimated-delivery-for-woocommerce'),
            'description' => __('Only class name from FontAwesome (ex: fas fa-truck)', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'checkbox',
            'name' => '_edw_fontawesome',
            'label' => __('Problem with Icon?', 'estimated-delivery-for-woocommerce'),
            'description' => __('Load Font Awesome on frontend', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'checkbox',
            'name' => 'edw_save_date_order',
            'label' => __('Save date on order?', 'estimated-delivery-for-woocommerce'),
            'description' => __('Add the delivery date to the order item metadata.', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'checkbox',
            'name' => 'edw_show_list',
            'label' => __('Show on loops?', 'estimated-delivery-for-woocommerce'),
            'description' => __('Show estimated delivery on shop, archive and search results.', 'estimated-delivery-for-woocommerce'),
        ],
        [
            'type' => 'textarea',
            'name' => '_edw_holidays_dates',
            'label' => __('Holidays dates', 'estimated-delivery-for-woocommerce'),
            'description' => __('Add holidays separated by commas. Use XXXX for dynamic year.', 'estimated-delivery-for-woocommerce'),
            'rows' => 6,
        ],
        [
            'type' => 'select',
            'name' => '_edw_position',
            'label' => __('Position', 'estimated-delivery-for-woocommerce'),
            'description' => __('Select where the message will be displayed on the product page.', 'estimated-delivery-for-woocommerce'),
            'options' => $positions,
        ],
    ];
}

function edw_admin_get_disabled_days_map() {
    return [
        'Mon' => __('Mon', 'estimated-delivery-for-woocommerce'),
        'Tue' => __('Tue', 'estimated-delivery-for-woocommerce'),
        'Wed' => __('Wed', 'estimated-delivery-for-woocommerce'),
        'Thu' => __('Thu', 'estimated-delivery-for-woocommerce'),
        'Fri' => __('Fri', 'estimated-delivery-for-woocommerce'),
        'Sat' => __('Sat', 'estimated-delivery-for-woocommerce'),
        'Sun' => __('Sun', 'estimated-delivery-for-woocommerce'),
    ];
}

function edw_admin_build_options_page_context($positions) {
    $values = edw_admin_get_settings_values();

    return [
        'notice' => edw_admin_get_options_notice(),
        'values' => $values,
        'tabs' => edw_admin_get_tabs(),
        'positions' => $positions,
        'general_fields' => edw_admin_get_general_fields(),
        'stock_fields' => edw_admin_get_stock_fields(),
        'date_format_groups' => edw_admin_get_date_format_groups(),
        'other_fields' => edw_admin_get_other_fields($positions),
        'disabled_days_map' => edw_admin_get_disabled_days_map(),
        'country_labels' => WC()->countries->get_countries(),
        'state_labels' => WC()->countries->get_states(),
        'logo_url' => plugins_url('assets/logo.png', EDW_PATH . 'estimated-delivery-woocommerce.php'),
        'mode_is_custom' => ($values['_edw_mode'] ?? '1') === '3',
    ];
}

function edw_admin_render_field($field, $values) {
    $name = $field['name'];
    $type = $field['type'];
    $value = $values[$name] ?? '';
    $wrapper_class = !empty($field['wrapper_class']) ? ' ' . $field['wrapper_class'] : '';
    $wrapper_style = !empty($field['wrapper_style']) ? ' style="' . esc_attr($field['wrapper_style']) . '"' : '';

    echo '<div class="edw-field' . esc_attr($wrapper_class) . '"' . $wrapper_style . '>';
    echo '<label class="block font-semibold">';

    if ($type === 'checkbox') {
        echo '<input type="checkbox" value="1" name="' . esc_attr($name) . '" ' . checked('1', $value, false) . ' class="mt-2"> ';
        echo esc_html($field['label']);
        echo '</label>';
        if (!empty($field['description'])) {
            echo '<p class="text-sm text-gray-500">' . esc_html($field['description']) . '</p>';
        }
        echo '</div>';
        return;
    }

    echo esc_html($field['label']);
    echo '</label>';

    if (!empty($field['description'])) {
        echo '<p class="text-sm text-gray-500">' . esc_html($field['description']) . '</p>';
    }

    if ($type === 'select') {
        echo '<select name="' . esc_attr($name) . '" class="input input-bordered w-full mt-2">';
        foreach ($field['options'] as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected((string) $option_value, (string) $value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') {
        $rows = isset($field['rows']) ? intval($field['rows']) : 4;
        echo '<textarea name="' . esc_attr($name) . '" rows="' . esc_attr($rows) . '" class="input input-bordered w-full mt-2">' . esc_textarea($value) . '</textarea>';
    } else {
        $input_type = $type === 'time' ? 'time' : 'text';
        $placeholder = isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : '';
        echo '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . $placeholder . ' class="input input-bordered w-full mt-2">';
    }

    echo '</div>';
}
