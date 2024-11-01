<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Dictionary
{
    const NONE = 0;
    const NO_ITEM_CODE = 1;
    const ITEM_PRESENT = 2;
    const ITEM_NOT_FOUND = 3;
    const ITEM_ADDED = 4;
    const CART_EMPTY = 5;
    const CART_TEMP = 6;


    private static function getList() {
        return [
            self::NONE => ''
            ,self::NO_ITEM_CODE => 'Warning: this image has no item code.'
            ,self::CART_EMPTY => 'Your cart is empty.'
            ,self::CART_TEMP => 'Please update your cart before ordering.'
            ,self::ITEM_PRESENT => 'Your cart already contains this item.'
            ,self::ITEM_NOT_FOUND => 'No item found with this code: '
            ,self::ITEM_ADDED => 'This item has been added to your cart.'
        ];
    }

    public static function getHtmlList() {
        $template = '<div id="wso-dictionary-$" style="display:none;">§TEXT§</div>';
        $items = self::getList();
        $ret = '';
        foreach ( $items as $key => $value ) {
            $ret .= "\n" . str_replace( ['$', '§TEXT§'], [$key, $value], $template );
        }
        return $ret;
    }

    public static function getMessage( $key ) {
        $ret = '?';
        $list = self::getList();
        if ( array_key_exists( $key, $list ) ) {
            $ret = $list[$key];
        }
        return $ret;
    }

}