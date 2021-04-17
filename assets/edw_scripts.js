    var EDW = {
        loading: false,
        show: function(product, position) {
            var self = this;
            if(self.loading) {
                console.log("Loading is true");
                return;
            }
            self.loading = true;
            jQuery.ajax({
                type: "post",
                url: edwConfig.url,
                data: "action=edw_get_estimate_dates&product="+product,
                success: function(result){
    
                    self.loading = false;
    
                    var data_position = JSON.parse(position);
                    if(data_position[1] == 'after') {
                        jQuery(data_position[0]).after(result.html);
                    }else if(data_position[1] == 'before') {
                        jQuery(data_position[0]).before(result.html);
                    }else if(data_position[1] == 'inside') {
                        jQuery(data_position[0]).append(result.html);
                    }
                }
            });
        },
    
    }