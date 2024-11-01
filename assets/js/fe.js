let wso_api = {
     url: {
         cart: '§API_CART_URL§'
        ,order: '§API_ORDER_URL§'
     }
    ,nonce: '§API_NONCE§'
    ,id: '§API_ID§'
    ,key: '§API_KEY§'
    ,exception: '§API_CODE_EX§'
}
let wso_pars = {
     currency: '§PAR_CURRENCY§'
    ,decimal: '§PAR_DECIMAL§'
    ,unit: '§PAR_UNIT§'
    ,url: '§PAR_WA_URL§'
}
let wso_dic = {
     no_item_code: '§MSG_NO_ITEM_CODE§'
    ,cart_empty: '§MSG_CART_EMPTY§'
    ,cart_temp: '§CART_TEMP§'
    ,item_present: '§ITEM_PRESENT§'
    ,item_not_found: '§ITEM_NOT_FOUND§'
    ,item_added: '§ITEM_ADDED§'
}

if(window.jQuery){
    (function($){
        'use strict';
        $(document).ready(function() {
            if ( 'content' in document.createElement('template') ) {

                const wso_attr = '§IMG_ITEM_ATTR§';
                let empty_code = false;
                $('*[' + wso_attr + ']').each(function(_,e) {
                    let code = $(e).attr(wso_attr).trim();
                    if ( code === '' ) {
                        empty_code = true;
                    }
                    let tag = e.tagName.toLowerCase();
                    if (tag === 'img') {
                        $(e).addJs(code);
                    } else {
                        $(e).find('img').each( function(_,i) {
                            $(i).addJs(code);
                        });
                    }
                });
                if ( empty_code ) {
                    jsSos_Alert("§MSG_CODE_EMPTY§");
                }

                wso_cart = new WSO_Cart();
                wso_cart.store.load('§STORE_JSON§');
                wso_cart.initialize('§CART_JSON§');

                let icon = document.getElementById('wso-cart-icon-id');
                if ( icon ) {
                    icon.style.display = 'inline';
                } else {
                    jsSos_Alert("§MSG_NO_CART_ICON§");
                }

            } else {
                jsSos_Alert("§MSG_TEMPLATE_DENIED§");
            }
        });
    })(jQuery);
}
(function($) {
    $.fn.addJs = function( code ) {
        /*
        if (code == '') {
            code = $(this).attr('alt');
        }
        */
        $(this).attr( 'onclick', "jsWso_AddToCart('" + code + "');" ).css( 'cursor', 'pointer');//.attr('title', 'add to cart');
    };
})(jQuery);