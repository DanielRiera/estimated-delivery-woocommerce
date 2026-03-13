<?php
if (!defined('ABSPATH')) { exit; }

foreach ($context['general_fields'] as $field) {
    if (!empty($field['wrapper_class']) && $field['wrapper_class'] === 'edw_block_custom_message' && !$context['mode_is_custom']) {
        $field['wrapper_style'] = 'display:none';
    }

    edw_admin_render_field($field, $context['values']);
}
