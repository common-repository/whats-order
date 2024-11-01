<?php
namespace SOSIDEE_WHATS_ORDER\SOS\WP;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Email
{

    public static function send( $to, $subject, $message ) {

        if ( !is_array($to) ) {
            $tos = $to;
        } else {
            $tos = implode( ',', $to );
        }

        $local = strpos ( $tos, '@localhost') !== false || strpos ( $tos, '@127.0.0.1') !== false;

        if ( !$local ) {
            return wp_mail( $tos, $subject, $message );
        } else {
            return mail( $tos, $subject, $message );
        }
    }

}