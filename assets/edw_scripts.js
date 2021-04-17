    var EDW = {
        loading: false,
        show: function(product, position) {
            var self = this;
            if(self.loading) {
                console.log("Loading is true");
                return;
            }

            EDW.getDates(product, function(result){
                var data_position = JSON.parse(position);
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
            var self = this;

            if(!type) {
                var type = 'product';
            }
            self.loading = true;

            jQuery.ajax({
                type: "post",
                url: edwConfig.url,
                data: "action=edw_get_estimate_dates&product="+product+"&type="+type,
                success: function(result){
    
                    self.loading = false;
                    callback(result);
                    
                }, error: function() {
                    self.loading = false;
                }
            });
        }
    
    }

jQuery(function($){
     jQuery( 'form.variations_form' ).on( 'found_variation', 
         function( event, variation ){
            EDW.getDates(variation.variation_id, function(result){
                jQuery(".edw_date").replaceWith(result.html);
            }, 'variation');
         } 
     );
});