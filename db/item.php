<?php
namespace SOSIDEE_WHATS_ORDER\DB;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Item extends Table
{

    const CODE_LENGTH = 16;
    const DESCRIPTION_LENGTH = 255;
    const UNIT_LENGTH = 16;

    public $code;
    public $description;
    public $price;
    public $unit;

    public function __construct() {
        parent::__construct( 'items' );

        $this->code = $this->table->addVarChar('code', self::CODE_LENGTH);
        $this->description = $this->table->addVarChar('description', self::DESCRIPTION_LENGTH);
        $this->price = $this->table->addCurrency('price');
        $this->unit = $this->table->addVarChar('unit', self::UNIT_LENGTH);

    }

    public function save( $data, $id = 0 ) {
        return $this->saveRecord($data, $id);
    }

    public function load( $id ) {
        $table = $this->table;

        $results = $table->select( [
            $table->id->name => $id
        ] );

        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                sosidee_log("DB\Item.load($id) :: WpTable.select() returned a wrong array length: " . count($results) . " (requested: 1)" );
                return false;
            }
        } else {
            return false;
        }
    }

    public function list( $filters = [] ) {
        $table = $this->table;

        $where = [ $table->cancelled->name => false ];
        foreach ($filters as $key => $value) {
            $where[$key] = $value;
        }
        $orders = [ $table->code->name ];

        return $table->select( $where, $orders );
    }

    public function codeExists( $code, $id ) {
        $ret = false;
        $table = $this->table;
        $items = $this->list( [ $table->code->name => $code ] );
        if ( is_array($items) ) {
            for ( $n=0; $n<count($items); $n++ ) {
                if ( sosidee_strcasecmp( $items[$n]->code, $code) == 0 && $items[$n]->id != $id ) {
                    $ret = true;
                    break;
                }
            }
        } else {
            self::msgErr( "A problem occurred while reading the database." );
            sosidee_log("DB\Item.codeExists($code,$id): this.list() returned false.");
        }
        return $ret;
    }

    public function cancel( $id ) {
        $table = $this->table;
        return $table->update( [ $table->cancelled->name => true ], [ $table->id->name => $id ] );
    }

    public function clear() {
        $table = $this->table;
        $field = $table->cancelled->name;
        return $table->update( [ $field => true ], [ $field => false ] );
    }

}