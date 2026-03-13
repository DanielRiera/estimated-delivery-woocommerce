<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap edw-admin-page">
    <?php if (!empty($context['notice'])): ?>
        <div class="notice notice-<?php echo esc_attr($context['notice']['type']); ?> is-dismissible">
            <p><?php echo esc_html($context['notice']['message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="max-w-6xl mx-auto p-6 space-y-8" x-data="{ tab: 'general' }">
        <div class="flex items-center flex-row bg-white shadow rounded-2xl p-6">
            <div class="mr-4">
                <img src="<?php echo esc_url($context['logo_url']); ?>" alt="<?php esc_attr_e('Estimated Delivery Logo', 'estimated-delivery-for-woocommerce'); ?>">
            </div>
            <div>
                <h4 class="!text-3xl font-bold mb-2"><?php esc_html_e('Estimated Delivery for WooCommerce', 'estimated-delivery-for-woocommerce'); ?></h4>
                <p class="text-gray-600"><?php esc_html_e('Show the estimated or guaranteed delivery for the product', 'estimated-delivery-for-woocommerce'); ?></p>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="border-b border-gray-200 px-6 pt-6">
                <nav class="-mb-px flex space-x-6 edw-tab-nav">
                    <?php foreach ($context['tabs'] as $tab_key => $tab_label): ?>
                        <button type="button" @click="tab = '<?php echo esc_attr($tab_key); ?>'" :class="{ 'border-blue-500 text-blue-600': tab === '<?php echo esc_attr($tab_key); ?>' }" class="cursor-pointer whitespace-nowrap pb-4 px-1 border-b-2 text-sm font-bold">
                            <?php echo esc_html($tab_label); ?>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>

            <form method="post" class="p-6 space-y-6 edw-options-form">
                <?php wp_nonce_field('edw_nonce', 'save_option_nonce'); ?>
                <input type="hidden" name="action" value="save_options">
                <input type="hidden" name="_edw_location_rules" value="<?php echo esc_attr(wp_json_encode($context['values']['_edw_location_rules'])); ?>" class="edw-location-rules-input">

                <div x-show="tab === 'general'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php require EDW_PATH . 'views/admin/partials/general-tab.php'; ?>
                </div>

                <div x-show="tab === 'stock'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php require EDW_PATH . 'views/admin/partials/stock-tab.php'; ?>
                </div>

                <div x-show="tab === 'format'" class="space-y-6">
                    <?php require EDW_PATH . 'views/admin/partials/format-tab.php'; ?>
                </div>

                <div x-show="tab === 'other'" class="space-y-6">
                    <?php require EDW_PATH . 'views/admin/partials/other-tab.php'; ?>
                </div>

                <div x-show="tab === 'location'" class="space-y-6">
                    <?php require EDW_PATH . 'views/admin/partials/location-tab.php'; ?>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-200 ease-in-out mt-6 flex items-center justify-center gap-2">
                    <?php esc_html_e('Save', 'estimated-delivery-for-woocommerce'); ?>
                </button>
            </form>
        </div>
    </div>
</div>
