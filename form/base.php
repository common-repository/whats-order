<?php
namespace SOSIDEE_WHATS_ORDER\FORM;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP as SOS_WP;

class Base extends \SOSIDEE_WHATS_ORDER\SOS\WP\DATA\Form
{
    protected $_database;
    protected $table;

    public function __construct($name, $callback = null) {
        parent::__construct( $name, $callback );

        $this->_database = $this->_plugin->database;
        $this->table = null;

    }

    public function htmlRowCount( $count ) {
        if ( is_int($count) ) {
            echo '<div style="text-align:right;margin-right:2em;">' . wp_kses_post($count) . ' row(s)</div>';
        }
    }

    public static function getIcon( $label, $color = "", $title = "" ) {
        $color = $color != "" ? " color:$color;" : "";
        return '<i title="' . esc_attr($title) .'" class="material-icons" style="vertical-align: bottom; max-width: 1em; font-size: inherit; line-height: inherit;' . esc_attr($color) . '">' . esc_textarea($label) .'</i>';
    }

}