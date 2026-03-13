<?php
if (!defined('ABSPATH')) { exit; }
?>
<div>
    <label class="block font-semibold"><?php esc_html_e('Days disabled', 'estimated-delivery-for-woocommerce'); ?></label>
    <p class="text-sm text-gray-500 mb-2"><?php esc_html_e('Select the days that NO shipments are made.', 'estimated-delivery-for-woocommerce'); ?></p>
    <div class="flex flex-wrap gap-4">
        <?php foreach ($context['disabled_days_map'] as $day_value => $day_label): ?>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="_edw_disabled_days[]" value="<?php echo esc_attr($day_value); ?>" <?php checked(in_array($day_value, (array) $context['values']['_edw_disabled_days'], true)); ?>>
                <?php echo esc_html($day_label); ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<?php
foreach ($context['other_fields'] as $field) {
    edw_admin_render_field($field, $context['values']);
}
?>
