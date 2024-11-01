<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

//use \SOSIDEE_WHATS_ORDER\SOS\WP as SOSWP;
use \SOSIDEE_WHATS_ORDER\SosPlugin;

class Cart
{
    private $plugin;

    private $record_id;
    private $user_id;

    public $cookie_key;
    public $json;

    public function __construct() {
        $this->plugin = SosPlugin::instance();
        $this->cookie_key = '';
        $this->user_id = 0;
        $this->record_id = 0;
        $this->json = '[]';
    }

    public function loadByCookie() {
        //$COOKIE_NAME = 'sosidee';
        //$this->cookie_key = SOSWP\Cookie::get($COOKIE_NAME);
        $cookie = new Cookie();
        $this->cookie_key = $cookie->get();
        if ( !is_null($this->cookie_key) ) {
            $database = $this->plugin->database;
            $results = $database->carts->loadByCookie( $this->cookie_key );
            if ( is_array($results) ) {
                if ( count($results) == 1 ) {
                    $data = $results[0];
                    $this->json = $data->content;
                    $this->record_id = $data->id;
                }
            } else {
                sosidee_log("SRC\Cart.loadByCookie() did not return an array.");
            }
        } else {
            $this->cookie_key = $cookie->getNewKey(); //$this->getNewCookieKey();
        }
        //SOSWP\Cookie::set($COOKIE_NAME, $this->cookie_key);
        $cookie->set( $this->cookie_key );

        $this->user_id = User::getId();
    }

    public function loadByApi( $request ) {
        $ret = false;
        $table = $this->plugin->database->carts;
        $results = $table->loadByCookie( $request->key );
        if ( is_array($results) ) {
            if ( count($results) > 0 ) {
                $data = $results[0];
                $this->record_id = $data->id;
                $this->cookie_key = $data->cookie_key;
            } else {
                $this->cookie_key = $request->key;
            }
            $this->json = $request->content;
            $this->user_id = User::getRealId( $request->id );
            $ret = true;
        } else {
            sosidee_log("SRC\Cart.loadByApi() did not return an array.");
        }
        return $ret;
    }

    private function escape( $value ) {
        return str_replace(['"', "'"], ['&#34;', '&#39;'], $value);
    }

    public function getStoreJson() {
        $ret = '[]';
        $table = $this->plugin->database->items;
        $results = $table->list();
        if ( is_array($results) ) {
            $items = [];
            for ( $n=0; $n<count($results); $n++ ) {
                $items[] = (object)[
                     $table->code->name => $results[$n]->{$table->code->name}
                    ,$table->description->name => $this->escape( $results[$n]->{$table->description->name} )
                    ,$table->price->name => $results[$n]->{$table->price->name}
                    ,$table->unit->name => $this->escape( $results[$n]->{$table->unit->name} )
                ];
            }
            $ret = json_encode($items);
        } else {
            sosidee_log("SRC\Cart.getStoreJson() did not return an array.");
        }
        return $ret;
    }

    /*
    private function getNewCookieKey() {
        return base_convert(time(), 10, 36) . '_' . bin2hex( random_bytes(16) );
    }
    */

    public function save() {
        $table = $this->plugin->database->carts;
        $data = [
             $table->user_id->name => $this->user_id
            ,$table->cookie_key->name => $this->cookie_key
            ,$table->content->name => $this->json
        ];
        $ret = $table->save($data, $this->record_id);
        if ( $this->record_id == 0  && $ret > 0 ) {
            $this->record_id = $ret;
        }
        return $ret;
    }

    /*
    public function getFakeId() {
        return 123 * $this->record_id + 321;
    }
    private function setRealId( $id ) {
        $this->record_id = ($id - 321) / 123;
    }
    */

}