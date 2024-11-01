<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class Response
{
    const UNKNOWN = 0;
    const SUCCESS = 1;
    const EXCEPTION = 9;
    const INVALID_COOKIE = 31;
    const INVALID_DATA = 32;
    const ERROR_GENERIC = 60;
    const ERROR_READ = 61;
    const ERROR_WRITE = 62;

    private static function getList() {
        return [
            self::UNKNOWN => 'An unhandled problem occurred.'
            ,self::SUCCESS => 'Your cart has been successfully saved.'
            ,self::INVALID_DATA => 'Item(s) data are not valid.'
            ,self::INVALID_COOKIE => 'Invalid data: the cookie sent by your browser was empty.'
            ,self::ERROR_GENERIC => 'A unspecified error occurred.'
            ,self::ERROR_READ => 'A problem occurred while reading data from the database.'
            ,self::ERROR_WRITE => 'A problem occurred while writing data to the database.'
        ];
    }

    public static function getHtmlList() {
        $template = '<div id="wso-api-response-$" style="display:none;">§TEXT§</div>';
        $items = self::getList();
        $ret = '';
        foreach ( $items as $key => $value ) {
            $ret .= "\n" . str_replace( ['$', '§TEXT§'], [$key, $value], $template );
        }
        return $ret;
    }



}