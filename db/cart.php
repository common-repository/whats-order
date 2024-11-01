<?php
namespace SOSIDEE_WHATS_ORDER\DB;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Cart extends Table
{

    public $user_id;
    public $cookie_key;
    public $content;

    public function __construct() {
        parent::__construct( 'carts' );

        $this->user_id = $this->table->addInteger('user_id');
        $this->cookie_key = $this->table->addVarChar('cookie_key', 255);
        $this->content = $this->table->addVarChar('content', 512);
    }

    public function save( $data, $id = 0 ) {
        return $this->saveRecord( $data, $id );
    }

    public function loadByCookie( $cookie_key ) {
        $table = $this->table;
        return $table->select( [
             $table->cookie_key->name => $cookie_key
            ,$table->cancelled->name => false
        ] );
    }

    public function cancel( $id ) {
        $table = $this->table;
        $data = [ $table->cancelled->name => true ];
        return $this->saveRecord( $data, $id );
    }

    /*
    public function loadByUser( $user_id ) {
        $table = $this->table;
        return $table->select( [
             $table->user_id->name => $user_id
            ,$table->cancelled->name => false
        ] );
    }
    */

    public function load( $id ) {
        $table = $this->table;

        $results = $table->select( [
            $table->id->name => $id
        ] );

        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                sosidee_log("DB\Cart.load($id) :: WpTable.select() returned a wrong array length: " . count($results) . " (requested: 1)" );
                return false;
            }
        } else {
            return false;
        }
    }


}