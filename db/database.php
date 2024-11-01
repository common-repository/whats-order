<?php
namespace SOSIDEE_WHATS_ORDER\DB;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;

class Database
{
    private $native;

    public $items;
    public $carts;
    public $orders;

    public function __construct() {


        $this->native = new DATA\WpDatabase('sos_wso_');
        Table::setDb( $this->native );

        // TABLE ITEMS
        $this->items = new Item();

        // TABLE CARTS
        $this->carts = new Cart();

        // TABLE ORDERS
        $this->orders = new Order();

        $this->native->create();
    }

    private function saveRecord($table, $data, $id) {
        if ( $id > 0 ) {
            return $table->update( $data, [ 'id' => $id ] );
        } else {
            return $table->insert( $data );
        }
    }

    /*
    public function saveItem( $data, $id = 0 ) {
        return $this->saveRecord($this->native->items, $data, $id);
    }

    public function loadItem( $id ) {
        $table = $this->native->items;

        $results = $table->select( [
            $table->id->name => $id
        ] );

        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                sosidee_log("Database.loadItem($id) :: WpTable.select() returned a wrong array length: " . count($results) . " (requested: 1)" );
                return false;
            }
        } else {
            return false;
        }
    }

    public function loadItems() {
        $table = $this->native->items;

        $filters = [ $table->cancelled->name => false ];
        $orders = [ $table->code->name ];

        return $table->select( $filters, $orders );
    }

    public function checkItemCode( $code, $id ) {
        $table = $this->native->items;

    }

    */

    public function saveCart( $data, $id = 0 ) {
        return $this->saveRecord($this->native->carts, $data, $id);
    }

    public function loadCartByKey( $key ) {
        $table = $this->native->carts;
        return $table->select( [
             $table->cookie_key->name => $key
            ,$table->cancelled->name => false
        ] );
    }

    public function loadCartByUser( $id ) {
        $table = $this->native->carts;
        return $table->select( [
             $table->user_id->name => $id
            ,'cancelled' => false
        ] );
    }


    public function loadOrders( $filters = [], $orders = ['creation' => 'DESC'] ) {
        $table = $this->native->orders;
        $where = [];
        if ( key_exists('user_id', $filters) && $filters['user_id'] != 0  ) {
            $where[ $table->user_id->name ] = $filters['user_id'];
        }
        if ( key_exists('cart_id', $filters) && $filters['cart_id'] != 0  ) {
            $where[ $table->cart_id->name ] = $filters['cart_id'];
        }

        if ( !key_exists($table->cancelled->name, $filters) ) {
            $where[ $table->cancelled->name ] = false;
        } else {
            $where[ $table->cancelled->name ] = boolval( $filters['cancelled'] );
        }

        return $table->select( $where, $orders );
    }

}