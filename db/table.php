<?php

namespace SOSIDEE_WHATS_ORDER\DB;

class Table
{
    protected static $db = null;
    protected $table;

    public $id;
    public $cancelled;
    public $creation;

    public function __construct( $name ) {
        $this->table = self::$db->addTable($name);

        $this->id = $this->table->addID();
        $this->creation = $this->table->addDateTime('creation')->setDefaultValueAsCurrentDateTime();
        $this->cancelled = $this->table->addBoolean('cancelled')->setDefaultValue(false);
    }

    protected function saveRecord($data, $id) {
        if ( $id > 0 ) {
            return $this->table->update( $data, [ 'id' => $id ] );
        } else {
            return $this->table->insert( $data );
        }
    }

    public static function setDb( $database ) {
        self::$db = $database;
    }

}