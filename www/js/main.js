jQuery(document).ready(function(){
    /* Init NETTE AJAX */
    $.nette.init();
    $.nette.ext("sidebar", {
        before: function(){
            jQuery("#status-modal").show();
        },
        complete: function(data){
            jQuery("#status-modal").fadeOut(500);
        },
        success: function(data){
            if('product_id' in data){
                var product_id = "#sidebar-product-id-" + data.product_id.toString();
            }
            jQuery(product_id).fadeTo('fast', 0.3).fadeTo(1000, 1.0); // zvyrazneni zmeneneho produktu
        },
        error: function(request, textStatus, errorThrown){
            // informovani uzivatele, ze doslo k chybe
            jQuery("#snippet--flash").html("<span class=\"alert alert-error\"><i class=\"icon-warning-sign\"></i>" +
                "Při požadavku došlo k neočekávané chybě! Zkuste prosím akci zopakovat. " +
                "V případě přetrvání problémů kontaktujte administrátora.</span>");
        }
    });
});
