
var wso_cart;

function jsWso_SwitchCart() {
    wso_cart.switch();
}

function jsWso_AddToCart(code) {
    if ( wso_cart.enabled === false ) {
        return;
    }
    if ( code !== '') {
        wso_cart.message.hide();
        wso_cart.add( code, 1, true );
    } else {
        let msg = jsWso_LoadDic(wso_dic.no_item_code);
        jsSos_Alert(msg);
    }
}
function jsWso_RemoveItem(code) {
    if ( wso_cart.enabled === false ) {
        return;
    }
    wso_cart.clear(code);
    jsWso_UpdateCart();
}

function jsWso_UpdateCart() {
    if ( wso_cart.enabled === false ) {
        return;
    }
    wso_cart.update();
    let data = {
         key: wso_api.key
        ,id: wso_api.id
        ,content: wso_cart.json()
    };
    wso_cart.message.hide();
    wso_cart.loading = true;
    wso_cart.enabled = false;
    jsWso_ApiSaveCart( JSON.stringify(data), function(response) {
        let message = response.body;
        if ( response.code !== wso_api.exception ) {
            message = jsWso_LoadApiResponse( response.code );
        }
        wso_cart.message.show( message );
        if ( !response.error ) {
            wso_cart.temp = false;
        }
        wso_cart.loading = false;
        wso_cart.enabled = true;
    });

}

function jsWso_OrderCart() {
    if ( wso_cart.enabled === false ) {
        return;
    }
    if ( wso_cart.temp ) {
        jsWso_UpdateCart();
    }
        if ( !wso_cart.empty ) {
            let data = {
                 key: wso_api.key
                ,id: wso_api.id
                ,content: wso_cart.order()
            };
            wso_cart.message.hide();
            wso_cart.loading = true;
            jsWso_ApiSaveOrder( JSON.stringify(data), function(response) {
                let message = response.body;
                if ( response.code !== wso_api.exception ) {
                    message = jsWso_LoadApiResponse( response.code );
                }
                wso_cart.message.show( message );
                wso_cart.loading = false;
                if ( !response.error ) {
                    jsWso_SendOrder();
                }
            });
        } else {
            let msg = jsWso_LoadDic(wso_dic.cart_empty);
            jsSos_Alert(msg);
        }
    /*
    } else {
        let msg = jsWso_LoadDic(wso_dic.cart_temp);
        jsSos_Alert(msg);
    }
     */
}

function jsWso_SendOrder() {
    let text = wso_cart.text();
    //let url = wso_pars.url + wso_pars.phone + '&text=' + encodeURIComponent(text);
    let url = wso_pars.url + wso_pars.phone + '&text=' + text;
    window.open(url , '_blank');
    //wso_cart.reset();
}

function jsWso_LoadApiResponse(code) {
    let ret = 'code not found: ' + code;
    let msg = document.getElementById('wso-api-response-' + code );
    if ( msg ) {
        ret = msg.innerHTML;
    }
    return ret;
}

function jsWso_LoadDic(code) {
    let ret = 'code not found: ' + code;
    let msg = document.getElementById('wso-dictionary-' + code );
    if ( msg ) {
        ret = msg.innerHTML.replace(/\|/g, '\n'); // character | becomes a new line
    }
    return ret;
}

function WSO_Item( code, description, price, unit ) {
    this.code = code;
    this.description = description.replace(/\&#34;/g, '"').replace(/\&#39;/g, "'");
    this.price = parseFloat(price);
    this.unit = unit.replace(/\&#34;/g, '"').replace(/\&#39;/g, "'");
}
WSO_Item.prototype = {
     code: ''
    ,description: ''
    ,price: 0
    ,unit: ''
}

function WSO_Store() {

    this.load = function( json ) {
        this.rows = [];
        let items  = JSON.parse(json);
        for ( let n=0; n<items.length; n++ ) {
            let item = items[n];
            let row = new WSO_Item( item.code, item.description, item.price, item.unit );
            this.rows.push( row );
        }
        return this.rows.length;
    }

}
WSO_Store.prototype = {
    rows: []
    ,index: function( code ) {
        let ret = -1;
        code = code.toLowerCase();
        for ( let n=0; n<this.rows.length; n++ ) {
            if ( this.rows[n].code.toLowerCase() === code ) {
                ret = n;
                break;
            }
        }
        return ret;
    }
    ,find: function( code ) {
        let i = this.index( code );
        if ( i >= 0 ) {
            return this.rows[i];
        } else {
            return false;
        }
    }
}

function WSO_Row( code, description, price, unit, qty = 0 ) {
    this.__proto__.__proto__ = WSO_Item.prototype;
    WSO_Item.call( this, code, description, price, unit );
    this.layout = null;
    this.quantity = qty;
}
WSO_Row.prototype = {
    _qty: 0
    ,get quantity() {
        return this._qty;
    }
    ,set quantity(value) {
        this._qty = value;
    }
    ,get amount() {
        return parseFloat( (this.price * this.quantity).toFixed(wso_pars.decimal) );
    }
    ,get amountAsString() {
        return this.amount.toString() + ' ' + wso_pars.currency;
    }
}

function WSO_Cart() {
    this.__proto__.__proto__ = WSO_Store.prototype;
    this.temp = false;
    this.store = new WSO_Store();
    this.layout = new WSO_Cart_Layout();
    this.message = new WSO_Msg_Layout();

    this.initialize = function( json ) {
        this.load(json);
        if ( this.temp ) {
            this.layout.total = '---';
        } else {
            this.layout.total = this.amount;
        }
        this.message.empty = this.empty;
        this.loading = false;
        this.enabled = true;
    }
    this.reset = function() {
        this.layout.reset();
        this.rows = [];
        this.message.empty = this.empty;
        /*
        let msg = jsWso_LoadDic(wso_dic.cart_empty);
        this.message.show(msg);
        */
    }

    this.load = function( json ) {
        try {
            let items  = JSON.parse(json);
            if ( items.length > 0 ) {
                this.rows = [];
                for ( let n=0; n<items.length; n++ ) {
                    let item = items[n];
                    this.add( item.cod, item.qty );
                }
            } else {
                this.reset();
            }
        } catch (e) {
        }
        return this.rows.length;
    }

    this.json = function() {
        let items = [];
        for ( let n=0; n<this.rows.length; n++ ) {
            let row = this.rows[n];
            let item = {
                 cod: row.code
                ,qty: row.quantity
            };
            items.push( item );
        }
        return JSON.stringify(items);
    }

    this.text = function() {
        let ret = '';
        for ( let n=0; n<this.rows.length; n++ ) {
            if ( n>0 ) {
                ret += '\n';
            }
            let row = this.rows[n];
            //ret += wso_pars.unit + ' ' + row.quantity + ' ' + row.description + ' ' + row.amountAsString;
            ret += row.unit + ' ' + row.quantity + ' ' + row.description + ' ' + row.amountAsString;
        }
        return encodeURIComponent(ret);
    }

    this.order = function() {
        let items = [];
        for ( let n=0; n<this.rows.length; n++ ) {
            let row = this.rows[n];
            let item = {
                 unit: row.unit
                ,quantity: row.quantity
                ,code: row.code
                ,description: row.description
                ,amount: row.amount
            };
            items.push( item );
        }
        return JSON.stringify(items);
    }

    this.add = function( code, qty, verbose = false ) { //verbose is true if raised by user
        let i = this.index( code );
        if ( i >= 0 ) {
            if ( verbose ) {
                let msg = jsWso_LoadDic(wso_dic.item_present);
                jsSos_Alert(msg);
            }
            return false;
        } else {
            let item = this.store.find( code );
            if ( item !== false ) {
                let row = new WSO_Row( item.code, item.description, item.price, item.unit, qty );
                i = this.rows.push( row ) - 1;
                let lay = this.layout.add( row );
                if ( lay ) {
                    row.layout = lay;
                } else {
                    jsSos_Alert('Your browser has experienced an unexpected error with the code: ' + code);
                    return false;
                }
                if ( verbose ) {
                    this.temporary();
                    this.message.empty = this.empty;
                    let msg = jsWso_LoadDic(wso_dic.item_added);
                    jsSos_Alert(msg);
                }
            } else {
                let msg = jsWso_LoadDic(wso_dic.item_not_found);
                jsSos_Alert(msg + code);
                return false;
            }
            return true;
        }
    }

    this.update = function() {
        for ( let n=this.rows.length-1; n>=0; n-- ) {
            let code = this.rows[n].code;
            this.rows[n].quantity = this.rows[n].layout.quantity;
            if ( this.rows[n].quantity > 0 ) {
                this.rows[n].layout.amount = this.rows[n].amountAsString;
            } else {
                this.layout.remove( code );
                this.rows.splice(n, 1);
            }
        }
        this.layout.total = this.amount;
        this.message.empty = this.empty;
    }

    this.clear = function( code ) {
        this.layout.clear(code);
    }


    this.temporary = function( code = '' ) {
        if ( code !== '' ) {
            let row = this.find( code );
            if ( row !== false ) {
                this.layout.temporary( row );
            }
        }
        this.layout.total = 'none';
        this.temp = true;
    }

    this.switch = function() {
        this.layout.switch();
    }
}

WSO_Cart.prototype = {
     enabled: false
    ,get amount() {
        let ret = 0;
        for ( let n=0; n<this.rows.length; n++ ) {
            ret += this.rows[n].amount;
        }
        return ret;
    }
    /*
    ,get amountAsString() {
        return this.amount.toFixed(wso_pars.decimal) + ' ' + wso_pars.currency;
    }
     */
    ,get empty() {
         return this.rows.length === 0;
    }
    ,set loading(value){
        this.layout.image.visibility = value;
    }
}

function WSO_Row_Layout( code = '$' ) {
    this.code = code;
    this._container = document.getElementById('wso-cart-row-' + code );
    this._description = document.getElementById('wso-cart-desc-' + code );
    this._amount = document.getElementById('wso-cart-amnt-' + code );
    this._quantity = document.getElementById('wso-cart-qty-' + code );
    this._unit = document.getElementById('wso-cart-unit-' + code );

    this.replace = function( code ) {
        this.code = code;
        this._container.id = this._container.id.replace('$', code);
        this._description.id = this._description.id.replace('$', code);
        this._quantity.id = this._quantity.id.replace('$', code);
        this._quantity.addEventListener('keydown', jsWso_OnlyNumbers);
        this._quantity.addEventListener('keyup', jsWso_NoComma);
        this._amount.id = this._amount.id.replace('$', code);
        this._unit.id = this._unit.id.replace('$', code);
        this._quantity.setAttribute('onchange', "wso_cart.temporary('" + code  + "');");

        let icon = document.getElementById('wso-cart-remove-$');
        icon.id = icon.id.replace('$', code);
        icon.setAttribute('onclick', "jsWso_RemoveItem('" + code  + "');");
    }

    this.setTempAmount = function( price ) {
        this.amount = this.quantity.toString() + '&#215;' + price.toFixed(wso_pars.decimal) + ' ' + wso_pars.currency;
    }

    this.clear = function() {
        this.quantity = 0;
        this.amount = 0;
    }

}
WSO_Row_Layout.prototype = {
    get description() {
        return  this._description.innerHTML;
    }
    ,set description( value ) {
        this._description.innerHTML = value;
    }
    ,get amount() {
        return  this._amount.innerHTML;
    }
    ,set amount( value ) {
        this._amount.innerHTML = value;
    }
    ,get quantity() {
        return  this._quantity.value;
    }
    ,set quantity( value ) {
        this._quantity.value = value;
    }
    ,get unit() {
        return  this._unit.innerHTML;
    }
    ,set unit( value ) {
        this._unit.innerHTML = value;
    }
}

function WSO_Cart_Layout() {
    this._container = document.getElementById('wso-cart-container-id');
    this._body = document.getElementById('wso-cart-rows-id');
    this._template = document.getElementById('wso-cart-tmpl-id');
    this._total = document.getElementById('wso-cart-total-amount-id');

    this.total = 'none';
    this.image = new WSO_Img_Layout();
    this.rows = [];

    this.index = function( code ) {
        let ret = -1;
        code = code.toLowerCase();
        for ( let n=0; n<this.rows.length; n++ ) {
            if ( this.rows[n].code.toLowerCase() === code ) {
                ret = n;
                break;
            }
        }
        return ret;
    }

    this.add = function( item ) {
        let row;
        let code = item.code;
        let i = this.index( code );
        if ( i < 0 ) {
            let lay = this._template.content.cloneNode(true);
            this._body.appendChild(lay);
            row = new WSO_Row_Layout();
            row.description = item.description;
            row.unit = item.unit;
            row.amount = item.amountAsString;
            row.replace( code );
            i = this.rows.push( row ) - 1;
        } else {
            row = this.rows[i];
        }
        this.rows[i].quantity = item.quantity;
        return row;
    }

    this.remove = function( code ) {
        let i = this.index( code );
        if ( i >= 0 ) {
            this.rows.splice(i, 1);
        }
        let lay = document.getElementById('wso-cart-row-' + code);
        if ( lay ) {
            this._body.removeChild(lay);
        }
    }

    this.clear = function( code ) {
        let i = this.index( code );
        if ( i >= 0 ) {
            this.rows[i].clear();
        }
    }

    this.temporary = function( item ) {
        let i = this.index( item.code );
        if ( i >= 0 ) {
            this.rows[i].setTempAmount( item.price );
        }
    }

    this.reset = function() {
        this._body.replaceChildren();
        this.rows = [];
        this.total = 'none';
    }

    this.switch = function() {
        this._container.style.display = (this._container.style.display == 'none') ? 'block' : 'none';
    }
}
WSO_Cart_Layout.prototype = {
    set total( value ) {
        if ( isNaN(value) ) {
            this._total.innerHTML = '---';
        } else {
            this._total.innerHTML = value.toFixed(wso_pars.decimal) + ' ' + wso_pars.currency;
        }
    }
}

function WSO_Msg_Layout() {
    this._container = document.getElementById('wso-cart-message-id');
    this._body = document.getElementById('wso-cart-text-id');
    this._empty = document.getElementById('wso-cart-empty-id');

    this.hide = function() {
        this.visibility = false;
        this.text = '';
    }
    this.show = function( value = '' ) {
        if ( value !== '') {
            this.text = value;
        }
        this.visibility = true;
    }
}
WSO_Msg_Layout.prototype = {
    set text( value ) {
        this._body.innerHTML = value;
    }
    ,set visibility( value ) {
        this._container.style.display = (value) ? 'block' : 'none';
    }
    ,set empty( value ) {
        this._empty.style.display = (value) ? 'block' : 'none';
    }
}

function WSO_Img_Layout() {
    this._tag = document.getElementById('wso-img-loading-id');
}
WSO_Img_Layout.prototype = {
    set visibility( value ) {
        this._tag.style.display = (value) ? 'inline' : 'none';
    }
}

function jsWso_NoComma(e) {
    if ( e.target.value.includes(',') ) {
        let ctrl = e.target;
        let caret = ctrl.value.indexOf(',');
        caret++;
        ctrl.value = ctrl.value.replace(/\,/g, '.');
        if (ctrl.selectionStart) {
            ctrl.setSelectionRange(caret, caret);
        } else if ( ctrl.createTextRange ) {
            let range = ctrl.createTextRange();
            range.move('character', caret);
            range.select();
        }
    }
}

function jsWso_OnlyNumbers(e) {
    if (e) {
        const keys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight']
        let regex = /[0-9]|\.|,/;
        if( !regex.test(e.key) && !keys.includes(e.key) ) {
            e.returnValue = false;
            if(e.preventDefault) {
                e.preventDefault();
            }
        }
    } else {
        return false;
    }
}
