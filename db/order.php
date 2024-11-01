<?php
namespace SOSIDEE_WHATS_ORDER\DB;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Order extends Table
{

    public $user_id;
    public $cart_id;
    public $cookie_key;
    public $content;

    public function __construct() {
        parent::__construct( 'orders' );

        $this->user_id = $this->table->addInteger('user_id');
        $this->cart_id = $this->table->addInteger('cart_id');
        $this->cookie_key = $this->table->addVarChar('cookie_key', 255);
        $this->content = $this->table->addVarChar('content', 1024);
    }

    public function save( $data, $id = 0 ) {
        return $this->saveRecord($data, $id);
    }


}