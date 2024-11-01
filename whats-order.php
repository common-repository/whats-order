<?php
/*
Plugin Name: What's Order
Version: 0.1.0
Description: Creates a shopping cart from the images in pages. Orders are sent by Whatsapp.
Author: SOSidee.com srl
Author URI: https://sosidee.com
Text Domain: whats-order
Domain Path: /languages
*/
namespace SOSIDEE_WHATS_ORDER;
( defined( 'ABSPATH' ) and defined( 'WPINC' ) ) or die( 'you were not supposed to be here' );
defined('SOSIDEE_WHATS_ORDER') || define( 'SOSIDEE_WHATS_ORDER', true );

//use \SOSIDEE_WHATS_ORDER\SOS\WP as SOSWP;
use SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;

require_once "loader.php";

\SOSIDEE_CLASS_LOADER::instance()->add( __NAMESPACE__, __DIR__ );

/**
 * Class of This Plugin *
 *
**/
class SosPlugin extends SOS\WP\Plugin
{

    const IMG_ITEM_ATTRIBUTE = 'wso-item';

    //pages
    private $pageConfig;
    private $pageIO;
    public $pageItemList;
    public $pageItemEdit;
    //private $pageOrderList; //???

    //database
    public $database;
    public $config;

    //forms
    public $formItemList;
    public $formItemEdit;
    public $formIO;
    //public $formOrderList;

    //API
    private $apiSaveCart;
    private $apiSaveOrder;

    private $cart;
    private $order;


    protected function __construct() {
        parent::__construct();

        //PLUGIN KEY & NAME 
        $this->key = 'sos-whats-order';
        $this->name = "What's Order";

        //if necessary, enable localization
        //$this->internationalize( 'whats-order' ); //Text Domain
    }

    protected function initialize() {
        parent::initialize();

        // settings
        $cluster = $this->addGroup('config', 'Settings');
        $this->config = new FORM\Config( $cluster );

        // database: custom tables for the plugin
        $this->database = new DB\Database();


    }

    protected function initializeBackend() {

        $this->pageItemList = $this->addPage('items' );
        $this->pageItemEdit = $this->addPage('item' );
        $this->pageIO = $this->addPage('io' );
        $this->pageConfig = $this->addPage('config' );

        //assign data cluster to page
        $this->config->setPage( $this->pageConfig );

        //menu
        $this->menu->icon = '-products';

        $this->menu->add( $this->pageItemList, 'Items' );
        $this->menu->addHidden( $this->pageItemEdit );
        $this->menu->add( $this->pageIO, 'Data import' );
        $this->menu->add( $this->pageConfig, 'Settings' );

        $this->formItemList = new FORM\ItemList();
        $this->formItemList->addToPage( $this->pageItemList );

        $this->formItemEdit = new FORM\ItemEdit();
        $this->formItemEdit->addToPage( $this->pageItemEdit );

        $this->formIO = new FORM\IO();
        $this->formIO->addToPage( $this->pageIO );

        $this->addStyle('admin');//->addToPage( $this->pageItemList, $this->pageItemEdit, $this->pageIO );

        $this->addScript('admin')->addToPage( $this->pageItemEdit );

        $this->addGoogleIcons();
    }

    protected function initializeFrontend() {
        add_action( 'init', array($this->cart, 'loadByCookie') );

        $this->addShortCode( SRC\Shortcode::TAG, array($this, 'handleShortcode') );

        $this->addGoogleIcons();
    }

    protected function initializeApi() {
        $this->cart = new SRC\Cart();
        $this->order = new SRC\Order();

        $this->apiSaveCart = $this->addApiPost('whats-order/cart', [$this, 'apiSaveCart'], 0 );
        //$this->apiSaveCart->nonceDisabled = true;
        $this->apiSaveOrder = $this->addApiPost('whats-order/order', [$this, 'apiSaveOrder'], 0 );
    }

    protected function hasShortcode( $tag, $attributes ) {
        if ( $tag == SRC\Shortcode::TAG ) {
            $this->addScript('api')->html();
            $this->addScript('cart')->html();

            $this->addStyle('cart')->html();

            $this->config->customCss->load();
            if ( !empty($this->config->customCss->value) ) {
                $this->addStyleInline($this->config->customCss->value);
            }

        }
    }

    private function strReplace($haystack, $needles) {
        $searches = array_keys($needles);
        $replaces = array_values($needles);
        return str_replace($searches, $replaces, $haystack);
    }

    public function handleShortcode( $args, $content, $tag ) {
        if ( $tag != SRC\Shortcode::TAG ) {
            return null;
        }

        $ret = '';

        $this->config->load();

        $substitutions = [
             '§LOADING_IMG_SRC§' => $this->getLoaderSrc()
            ,'§LOADING_IMG_ALT§' => 'loading...'
            ,'§CART_ICON_TITLE§' => 'show/hide your cart'
            ,'§HIDE_ICON_TITLE§' => 'hide the message'
            ,'§TOTAL_TEXT§' => 'Total'
            ,'§UPDATE_ICON_TITLE§' => 'click to save changes and update the cart'
            ,'§REMOVE_ICON_TITLE§' => 'click to remove this item and update the cart'
            ,'§BTN_ORDER_TEXT§' => 'order'
            ,'§BTN_ORDER_TITLE§' => 'click to order'
            ,'§CART_EMPTY§' => SRC\Dictionary::getMessage( SRC\Dictionary::CART_EMPTY )
            ,'§API_RESPONSE§' => SRC\Response::getHtmlList()
            ,'§CART_DICTIONARY§' => SRC\Dictionary::getHtmlList()
        ];

        $htmlCart = $this->loadAsset('cart.html');
        $ret .= $this->strReplace($htmlCart, $substitutions);

        $msg_code_empty = 'Warning: one or more image codes are empty.\\nCheck the custom attribute {a}';
        $msg_code_empty = str_replace('{a}', "'" . self::IMG_ITEM_ATTRIBUTE . "'" , $msg_code_empty);

        $substitutions = [
             '§API_CART_URL§' => $this->apiSaveCart->getUrl()
            ,'§API_ORDER_URL§' => $this->apiSaveOrder->getUrl()
            ,'§API_NONCE§' => SOS\WP\API\EndPoint::getNonce()
            ,'§STORE_JSON§' => $this->cart->getStoreJson()
            ,'§CART_JSON§' => $this->cart->json
            ,"'§API_ID§'" => SRC\User::getFakeId()
            ,'§API_KEY§' => $this->cart->cookie_key
            ,'§PAR_CURRENCY§' => SRC\Currency::getSymbol( $this->config->currencySymbol->value )
            ,"'§PAR_DECIMAL§'" => $this->config->decimalDigit->value
            ,'§PAR_UNIT§' => $this->config->itemUnit->value
            ,'§PAR_WA_URL§' => $this->config->getUrl()
            ,'§IMG_ITEM_ATTR§' => self::IMG_ITEM_ATTRIBUTE
            ,'§MSG_CODE_EMPTY§' => sanitize_text_field( $msg_code_empty )
            ,'§MSG_TEMPLATE_DENIED§' => sanitize_text_field( 'Your browser does not support HTML template elements.' )
            ,'§MSG_NO_CART_ICON§' => sanitize_text_field( 'Your browser cannot find the cart icon.' )
            ,"'§API_CODE_EX§'" => SRC\Response::EXCEPTION
            ,"'§MSG_NO_ITEM_CODE§'" => SRC\Dictionary::NO_ITEM_CODE
            ,"'§MSG_CART_EMPTY§'" => SRC\Dictionary::CART_EMPTY
            ,"'§CART_TEMP§'" => SRC\Dictionary::CART_TEMP
            ,"'§ITEM_PRESENT§'" => SRC\Dictionary::ITEM_PRESENT
            ,"'§ITEM_NOT_FOUND§'" => SRC\Dictionary::ITEM_NOT_FOUND
            ,"'§ITEM_ADDED§'" => SRC\Dictionary::ITEM_ADDED
        ];
        $js = $this->loadAsset('fe.js');
        $js = $this->strReplace($js, $substitutions);
        $script = DATA\FormTag::get( 'script', [
             'type' => 'application/javascript'
            ,'content' => $js
        ]);

        $ret .= $script;

        return $ret;
    }

    public function apiSaveCart( \WP_REST_Request $request ) {
        try {

            $headers = [
                 'X_Wso_Version' => $this->version
                ,'Content-Type' => 'application/json'
            ];

            $response = (object) [
                 'error' => true
                ,'code' => SRC\Response::UNKNOWN
            ];

            $body = $request->get_body();
            $data = json_decode($body);

            if ( !is_null($data) ) {
                if ( $data->key != '' ) {
                    $res = $this->cart->loadByApi( $data );
                    if ( $res !== false ) {

                        $res = $this->cart->save();
                        if ( $res !== false ) {
                            $http_status = 200;
                            $response->error = false;
                            $response->code = SRC\Response::SUCCESS;
                        } else {
                            $http_status = 500;
                            $response->code = SRC\Response::ERROR_WRITE;
                        }

                    } else {
                        $http_status = 500;
                        $response->code = SRC\Response::ERROR_READ;
                    }
                } else {
                    $http_status = 400;
                    $response->code = SRC\Response::INVALID_COOKIE;
                    sosidee_log('apiSaveCart() - Invalid HTTP request: property $data->key is null.');
                }

            } else {
                $http_status = 500;
                $response->code = SRC\Response::INVALID_DATA;
                sosidee_log('apiSaveCart() - Invalid HTTP request: variable $data is null for $body=' . print_r($body, true));
            }
            return new \WP_REST_Response( $response, $http_status, $headers );

        } catch ( \Exception $ex) {
            sosidee_log('apiSaveCart() - Server exception: ' . $ex->getMessage() );
            return new \WP_Error( SRC\Response::EXCEPTION, $ex->getMessage() );
        }
    }

    public function apiSaveOrder( \WP_REST_Request $request ) {
        try {

            $headers = [
                'X_Wso_Version' => $this->version
                ,'Content-Type' => 'application/json'
            ];

            $response = (object) [
                'error' => true
                ,'code' => SRC\Response::UNKNOWN
            ];

            $body = $request->get_body();
            $data = json_decode($body);

            if ( !is_null($data) ) {
                if ( $data->key != '' ) {
                    $res = $this->order->loadByApi( $data );
                    if ( $res !== false ) {
                        $res = $this->order->save();
                        if ( $res !== false ) {
                            /*
                            if ( $this->order->cancelCart() === false ) {
                                sosidee_log('apiSaveOrder() order.cancelCart() failed.');
                            }
                            */
                            $http_status = 200;
                            $response->error = false;
                            $response->code = SRC\Response::SUCCESS;
                        } else {
                            $http_status = 500;
                            $response->code = SRC\Response::ERROR_WRITE;
                        }
                    } else {
                        $http_status = 500;
                        $response->code = SRC\Response::ERROR_READ;
                    }
                } else {
                    $http_status = 400;
                    $response->code = SRC\Response::INVALID_COOKIE;
                    sosidee_log('apiSaveOrder() - Invalid HTTP request: property $data->key is null.');
                }

            } else {
                $http_status = 500;
                $response->code = SRC\Response::INVALID_DATA;
                sosidee_log('apiSaveOrder() - Invalid HTTP request: variable $data is null for $body=' . print_r($body, true));
            }
            return new \WP_REST_Response( $response, $http_status, $headers );

        } catch ( \Exception $ex) {
            sosidee_log('apiSaveCart() - Server exception: ' . $ex->getMessage() );
            return new \WP_Error( SRC\Response::EXCEPTION, $ex->getMessage() );
        }
    }

}


/**
 * DO NOT CHANGE BELOW UNLESS YOU KNOW WHAT YOU DO *
**/
$plugin = SosPlugin::instance(); //the class must be the one defined in this file
$plugin->run();

// this is the end (A B C)