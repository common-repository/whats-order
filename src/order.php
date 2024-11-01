<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SosPlugin;


class Order
{

    const URL = 'https://api.whatsapp.com/send/?phone=';

    private $plugin;

    private $user_id;
    private $cart_id;

    public $cookie_key;
    public $json;

    public function __construct() {
        $this->plugin = SosPlugin::instance();
        $this->cookie_key = '';
        $this->user_id = 0;
        $this->cart_id = 0;
        $this->json = '[]';

    }

    public function loadByApi( $request ) {
        $ret = false;
        $table = $this->plugin->database->carts;
        $results = $table->loadByCookie( $request->key );
        if ( is_array($results) ) {
            if ( count($results) > 0 ) {
                $data = $results[0];
                $this->cart_id = $data->id;
                $this->cookie_key = $data->cookie_key;
            } else {
                sosidee_log("SRC\Order.loadByApi() carts.loadByCookie($request->key) did not return data.");
            }
            $this->json = $request->content;
            $this->user_id = User::getRealId( $request->id );
            $ret = true;
        } else {
            sosidee_log("SRC\Order.loadByApi() did not return an array.");
        }
        return $ret;
    }

    public function save() {
        $table = $this->plugin->database->orders;
        $data = [
             $table->user_id->name => $this->user_id
            ,$table->cookie_key->name => $this->cookie_key
            ,$table->cart_id->name => $this->cart_id
            ,$table->content->name => $this->json
        ];
        return $table->save($data, 0);
    }

    public function cancelCart() {
        $table = $this->plugin->database->carts;
        return $table->cancel( $this->cart_id );
    }

}