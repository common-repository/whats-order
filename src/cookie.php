<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP as SOSWP;
use \SOSIDEE_WHATS_ORDER\SosPlugin;

class Cookie
{
    const NAME = 'dynamicqrcode';

    private $plugin;

    public function __construct() {
        $this->plugin = SosPlugin::instance();
    }

    public function get() {
        return SOSWP\Cookie::get( self::NAME );
    }

    public function set( $value ) {
        $this->plugin->config->cookieDuration->load();
        $expiry = intval($this->plugin->config->cookieDuration->value) * 24 * 60 * 60;
        SOSWP\Cookie::set( self::NAME, $value, $expiry );
    }

    public function getNewKey() {
        return base_convert(time(), 10, 36) . '_' . bin2hex( random_bytes(16) );
    }

}