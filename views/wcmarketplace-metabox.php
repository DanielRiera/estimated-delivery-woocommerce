<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $product;
$disabledDays = get_post_meta($product->get_id(),'_edw_disabled_days', true);
if($disabledDays == "") { $disabledDays = []; }
?>
<input type="hidden" value="1" name="_edw_overwrite" /></label>
<div role="tabpanel" class="tab-pane fade" id="edw_estimate_delivery"> <!-- just make sure tabpanel id should replace with your added tab target -->
    <div class="row-padding">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?=__('Days for Delivery', 'estimated-delivery-for-woocommerce')?>
                </th>
                <td>
                    <label>
                    <input type="number" min="0" max="99999" name="_edw_days" value="<?=get_post_meta($product->get_id(), '_edw_days', true)?>" /></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?=__('Max Days for Delivery', 'estimated-delivery-for-woocommerce')?>
                <p class="description"><?=__('Set 0 for disable. If this set more than 0 days, it will show a range.','estimated-delivery-for-woocommerce')?></p>
                </th>
                <td>
                    <label>
                    <input type="number" min="0" max="99999" name="_edw_max_days" value="<?=get_post_meta($product->get_id(), '_edw_max_days', true)?>" /></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?=__('Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')?>
                </th>
                <td>
                    <label>
                    <input type="number" min="0" max="99999" name="_edw_days_outstock" value="<?=get_post_meta($product->get_id(), '_edw_days_outstock', true)?>" /></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?=__('Max Days for Delivery out of stock', 'estimated-delivery-for-woocommerce')?>
                <p class="description"><?=__('Set 0 for disable. If this set more than 0 days, it will show a range.','estimated-delivery-for-woocommerce')?></p>
                </th>
                <td>
                    <label>
                    <input type="number" min="0" max="99999" name="_edw_max_days_outstock" value="<?=get_post_meta($product->get_id(), '_edw_max_days_outstock', true)?>" /></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?=__('Estimated or Guaranteed', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('The message will change.','estimated-delivery-for-woocommerce')?></p>
                </th>
                <td>
                    <label>
                    <select name="_edw_mode" style="height: 34px !important;">
                        <option value="1" <?php selected("1", get_post_meta($product->get_id(), '_edw_mode', true)) ?>><?=__('Estimated','estimated-delivery-for-woocommerce');?></option>
                        <option value="2" <?php selected("2", get_post_meta($product->get_id(), '_edw_mode', true)) ?>><?=__('Guaranteed','estimated-delivery-for-woocommerce');?></option>
                    </select>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?=__('Days disabled', 'estimated-delivery-for-woocommerce')?>
                    <p class="description"><?=__('Select the days that NO shipments are made.','estimated-delivery-for-woocommerce')?></p>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Mon" <?= (in_array('Mon', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Monday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Tue" <?= (in_array('Tue', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Tuesday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Wed" <?= (in_array('Wed', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Wednesday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Thu" <?= (in_array('Thu', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Thursday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Fri" <?= (in_array('Fri', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Friday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Sat" <?= (in_array('Sat', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Saturday','estimated-delivery-for-woocommerce');?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="_edw_disabled_days[]" value="Sun" <?= (in_array('Sun', $disabledDays) == true) ? 'checked="checked"' : ''; ?> />
                        <?=__('Sunday','estimated-delivery-for-woocommerce');?>
                    </label>
                </td>
            </tr>
        </table>
    </div>
</div>
