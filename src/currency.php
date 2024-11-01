<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Currency
{
    private static $LIST = [];
    private static $_initialized = false;

    public $symbol;
    public $code;
    public $name;

    public function __construct($code, $name, $symbol) {
        $this->code = $code;
        $this->name = $name;
        $this->symbol = $symbol;
    }

    public static function get( $code ) {
        $ret = false;
        for ( $n=0; $n<count(self::$LIST); $n++ ) {
            if ( self::$LIST[$n]->code == $code ) {
                $ret = self::$LIST[$n];
                break;
            }
        }
        return $ret;
    }

    public static function initialize() {
        if (self::$_initialized) {
            return;
        }

        self::$LIST[] = new self('EUR', 'Euro', '€');
        self::$LIST[] = new self('USD', 'US Dollar', '$');
        self::$LIST[] = new self('GBP', 'Pounds Sterling', '£');
        self::$LIST[] = new self('CHF', 'Swiss Franc', 'CHF');
        self::$LIST[] = new self('ZAR', 'South African Rand', 'R');
        self::$LIST[] = new self('CNY', 'Chinese Yuan', '¥');
        self::$LIST[] = new self('JPY', 'Japanese Yen', '¥');
        self::$LIST[] = new self('INR', 'Indian Rupee', '₹');

        self::$_initialized = true;
    }

    public static function getSymbol( $code ) {
        $ret = '?';
        for ( $n=0; $n<count(self::$LIST); $n++ ) {
            $item = self::$LIST[$n];
            if ( $item->code == $code )
            {
                $ret = $item->symbol;
                break;
            }
        }
        return $ret;
    }

    public static function getList() {
        $ret = [];
        for ( $n=0; $n<count(self::$LIST); $n++ ) {
            $item = self::$LIST[$n];
            $ret[$item->code] = $item->name;
        }
        return $ret;
    }

}
Currency::initialize();