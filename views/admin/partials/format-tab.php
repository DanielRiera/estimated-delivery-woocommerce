<?php
if (!defined('ABSPATH')) { exit; }
?>
<div>
    <label class="block font-semibold"><?php esc_html_e('Date Formats', 'estimated-delivery-for-woocommerce'); ?></label>
    <div class="grid md:grid-cols-2 gap-4 mt-2">
        <?php foreach ($context['date_format_groups'] as $group): ?>
            <div>
                <p class="text-sm text-gray-500"><?php echo esc_html($group['description']); ?></p>
                <?php foreach ($group['fields'] as $index => $field): ?>
                    <?php $value = $context['values'][$field['name']] ?? $field['default']; ?>
                    <input type="text" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($value); ?>" class="input input-bordered w-full<?php echo $index > 0 ? ' mt-2' : ''; ?>">
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
