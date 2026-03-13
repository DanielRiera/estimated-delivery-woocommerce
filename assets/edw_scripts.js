    var EDW = {
        show: function(product, position) {
            EDW.getDates(product, function(result){
                var data_position = JSON.parse(position);
                if(!result || !result.html) {
                    return;
                }

                if(data_position[1] == 'after') {
                    jQuery(data_position[0]).after(result.html);
                }else if(data_position[1] == 'before') {
                    jQuery(data_position[0]).before(result.html);
                }else if(data_position[1] == 'inside') {
                    jQuery(data_position[0]).append(result.html);
                }
            });
            
        },
        getDates: function(product, callback, type){
            if(!type) {
                var type = 'product';
            }

            jQuery.ajax({
                type: "post",
                url: edwConfig.url,
                data: "action=edw_get_estimate_dates&product="+product+"&type="+type,
                success: function(result){
                    callback(result);
                    
                }, error: function() {
                }
            });
        },
        loadPlaceholders: function() {
            jQuery('.edw_date_placeholder[data-product-id]').each(function(){
                var placeholder = jQuery(this);
                var productId = placeholder.data('product-id');

                if (!productId || placeholder.data('loaded')) {
                    return;
                }

                placeholder.data('loaded', true);
                EDW.getDates(productId, function(result){
                    if(result && result.html) {
                        placeholder.replaceWith(result.html);
                    } else {
                        placeholder.remove();
                    }
                });
            });
        }
    
    };

jQuery(function($){
     EDW.loadPlaceholders();

     jQuery( 'form.variations_form' ).on( 'found_variation', 
         function( event, variation ){
            EDW.getDates(variation.variation_id, function(result){
                if(result && result.html) {
                    jQuery(".edw_date").replaceWith(result.html);
                }
            }, 'variation');
         } 
     );
});
