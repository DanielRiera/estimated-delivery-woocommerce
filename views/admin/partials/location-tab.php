<?php
if (!defined('ABSPATH')) { exit; }
?>
<div>
    <label class="block font-semibold"><?php esc_html_e('Location based rules', 'estimated-delivery-for-woocommerce'); ?></label>
    <p class="text-sm text-gray-500"><?php esc_html_e('Configure delivery times per country and state. You can leave the state blank to apply the rule to all states in that country.', 'estimated-delivery-for-woocommerce'); ?></p>
</div>

<div class="flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/3">
        <label class="block font-semibold"><?php esc_html_e('Country', 'estimated-delivery-for-woocommerce'); ?></label>
        <select class="input input-bordered w-full edw-country-select">
            <option value=""><?php esc_html_e('Select a country', 'estimated-delivery-for-woocommerce'); ?></option>
            <?php foreach ($context['country_labels'] as $code => $label): ?>
                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="w-full md:w-1/3">
        <label class="block font-semibold"><?php esc_html_e('State (optional)', 'estimated-delivery-for-woocommerce'); ?></label>
        <select class="input input-bordered w-full edw-state-select">
            <option value=""><?php esc_html_e('Select a state (optional)', 'estimated-delivery-for-woocommerce'); ?></option>
        </select>
    </div>
    <div class="w-full md:w-1/3 flex items-end">
        <button type="button" class="bg-blue-600 hover:bg-blue-700 p-3 rounded text-white w-full edw-add-location-rule">
            <?php esc_html_e('Add Location Rule', 'estimated-delivery-for-woocommerce'); ?>
        </button>
    </div>
</div>

<div class="edw-location-rules-list">
    <?php foreach ((array) $context['values']['_edw_location_rules'] as $country => $states): ?>
        <div class="border rounded-lg p-4 bg-gray-50 edw-location-country" data-country="<?php echo esc_attr($country); ?>">
            <h3 class="font-bold text-lg mb-2">
                <?php echo esc_html($country . ' - ' . ($context['country_labels'][$country] ?? '')); ?>
            </h3>
            <?php foreach ((array) $states as $state => $rule): ?>
                <div class="mb-3 p-4 bg-white rounded shadow-sm flex flex-col md:flex-row items-center gap-4 edw-location-rule" data-country="<?php echo esc_attr($country); ?>" data-state="<?php echo esc_attr($state); ?>">
                    <div class="flex-1">
                        <span class="font-semibold text-sm">
                            <?php if ($state === 'default'): ?>
                                <?php esc_html_e('All states', 'estimated-delivery-for-woocommerce'); ?>
                            <?php else: ?>
                                <?php echo esc_html($state . ' - ' . ($context['state_labels'][$country][$state] ?? '')); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex flex-col md:flex-row gap-2">
                        <label class="flex items-center gap-2">
                            <span><?php esc_html_e('Days', 'estimated-delivery-for-woocommerce'); ?></span>
                            <input type="number" min="0" max="999" class="input input-sm input-bordered w-20 edw-rule-days" value="<?php echo esc_attr(isset($rule['days']) ? intval($rule['days']) : 0); ?>">
                        </label>
                        <label class="flex items-center gap-2">
                            <span><?php esc_html_e('Max Days', 'estimated-delivery-for-woocommerce'); ?></span>
                            <input type="number" min="0" max="999" class="input input-sm input-bordered w-24 edw-rule-max-days" value="<?php echo esc_attr(isset($rule['max_days']) ? intval($rule['max_days']) : 0); ?>">
                        </label>
                        <button type="button" class="text-red-600 hover:underline edw-remove-location-rule">
                            <?php esc_html_e('Remove', 'estimated-delivery-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>