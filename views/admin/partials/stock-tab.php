<?php
if (!defined('ABSPATH')) { exit; }

foreach ($context['stock_fields'] as $field) {
    $value = $context['values'][$field['name']] ?? '';
    ?>
    <div>
        <label class="block font-semibold"><?php echo esc_html($field['label']); ?></label>
        <input type="number" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($value); ?>" min="0" max="99999" class="input input-bordered w-full">
    </div>
    <?php
}
