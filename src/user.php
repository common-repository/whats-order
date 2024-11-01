<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP as SOSWP;

class User extends \SOSIDEE_WHATS_ORDER\SOS\WP\User
{

    private static $wp_user = null;

    public static function getId() {
        if ( is_null(self::$wp_user) ) {
            self::$wp_user = SOSWP\User::get();
        }
        return self::$wp_user->id;
    }

    public static function getFakeId() {
        if ( is_null(self::$wp_user) ) {
            self::$wp_user = SOSWP\User::get();
        }
        return 321 * self::$wp_user->id + 123;
    }

    public static function getRealId( $id ) {
        return ($id - 123) / 321;
    }

}