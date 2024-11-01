let wso_api_enabled = false;
if(window.jQuery){
    (function($){
        'use strict';
        $(document).ready(function() {
            if ($.ajax) {
                wso_api_enabled = true;
            } else {
                jsSos_Alert( { title:'Whats Order Plugin', body:'Ajax not supported.', icon:'error'} );
            }
        });
    })(jQuery);
} else {
    jsSos_Alert( { title:'Whats Order Plugin', body:'jQuery not found.', icon:'error'} );
}

function jsWso_ApiSaveCart( data, callback ) {
    if ( !wso_api_enabled ) {
        jsSos_Alert( { title:'Whats Order Plugin', body:'jQuery not found or Ajax not supported.', icon:'error'} );
        return;
    }
    let ret = { code: 0, error: true, body: 'Unhandled Rest API problem.' };
    jQuery.ajax({
         url: wso_api.url.cart
        ,type: 'POST'
        ,data: data
        ,dataType: 'json'
        ,headers: { 'X-WP-Nonce': wso_api.nonce }
    }).done(function( response, textStatus, xhr ) {
        try {
            ret.error = Boolean(response.error);
            ret.code = response.code;
        }
        catch (ex) {
            ret.code = wso_api.exception;
            ret.body = 'Browser error: (' + ex.name + ') ' + ex.message;
        }
    }).fail(function( xhr, status, errorThrown ) {
        if ( xhr.responseJSON ) {
            ret.code = xhr.responseJSON.code;
            ret.body = xhr.responseJSON.message;
        } else {
            ret.code = wso_api.exception;
            ret.body = '(' + xhr.status + ') ' + errorThrown;
        }
    }).always( function( resp, status ) {
        callback( ret );
    });
}

function jsWso_ApiSaveOrder( data, callback ) {
    if ( !wso_api_enabled ) {
        jsSos_Alert( { body:'jQuery not found or Ajax not supported.', icon:'error'} );
        return;
    }
    let ret = { error: true, body: 'Unhandled Rest API problem.' };
    jQuery.ajax({
        url: wso_api.url.order
        ,type: 'POST'
        ,data: data
        ,dataType: 'json'
        ,headers: { 'X-WP-Nonce': wso_api.nonce }
    }).done(function( response, textStatus, xhr ) {
        try {
            ret.error = Boolean(response.error);
            ret.code = response.code;
        }
        catch (ex) {
            ret.code = wso_api.exception;
            ret.body = 'Browser exception: (' + ex.name + ') ' + ex.message;
        }
    }).fail(function( xhr, status, errorThrown ) {
        if ( xhr.responseJSON ) {
            ret.code = xhr.responseJSON.code;
            ret.body = xhr.responseJSON.message;
        } else {
            ret.code = wso_api.exception;
            ret.body = '(' + xhr.status + ') ' + errorThrown;
        }
    }).always( function( resp, status ) {
        callback( ret );
    });
}

/*
function jsSos_ApiLoadResponse(code) {
    let ret = '???';
    let msg = document.getElementById('wso-api-response-' + code );
    if ( msg ) {
        ret = msg.innerHTML;
    }
    return ret;
}
*/

function jsSos_Alert( message ) {
    if (typeof Swal !== 'undefined') {
        if ( typeof message !== 'object' ) {
            message = { body: message };
        }
        if ( !message.body.includes('\n') ) {
            Swal.fire({
                 text: message.body
                ,icon: message.icon
                ,title: message.title
            });
        } else {
            Swal.fire({
                 html: message.body.replace(/\n/g, '<br>')
                ,icon: message.icon
                ,title: message.title
            });
        }
    } else {
        let lines = [];
        if ( message.title ) {
            lines.push( message.title )
        }
        if ( message.body ) {
            lines.push( message.body )
        }
        if (lines.length > 0) {
            alert( lines.join('\n\n') );
        } else {
            alert( message );
        }
    }
}