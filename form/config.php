<?php
namespace SOSIDEE_WHATS_ORDER\FORM;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;
use \SOSIDEE_WHATS_ORDER\SRC as SRC;

class Config
{
    private $_plugin;

    private $native;

    public $phoneNumber;
    public $currencySymbol;
    public $decimalDigit;
    public $itemUnit;
    public $cookieDuration;
    public $customCss;

    public function __construct($cluster) {

        $this->_plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();

        $this->native = $cluster;
        $cluster->validate = array($this, 'validate');

        //$this->phoneNumber = $this->native->addField( 'phone-number', 'Phone number', '3701486174', DATA\FieldType::TEXT );
        $this->phoneNumber = $this->native->addField( 'phone-number', 'Phone number', '', DATA\FieldType::TEXT );

        $this->itemUnit = $this->native->addField( 'item-unit', "Unit of measurement", 'N.', DATA\FieldType::TEXT );

        $this->currencySymbol = $this->native->addField( 'currency-symbol', 'Price currency', 'EUR', DATA\FieldType::SELECT );

        $this->decimalDigit = $this->native->addField( 'decimal-digit', 'Price decimal digit(s)', 2, DATA\FieldType::NUMBER );

        $this->cookieDuration = $this->native->addField( 'cookie-duration', 'User cookie duration (days)', 0, DATA\FieldType::NUMBER );

        $this->customCss = $this->native->addField( 'custom-css', "Cart custom CSS", '', DATA\FieldType::TEXTAREA );
    }

    private function initialize() {
        $this->phoneNumber->description = '<i>the mobile phone number which you will receive orders to</i>';
        $this->itemUnit->class = 'small-text';
        $this->itemUnit->description = '<i>used in orders (e.g. <strong>N.</strong> 3 soap Deluxe 15 &euro; )</i>';
        $this->currencySymbol->options = SRC\Currency::getList();
        $this->decimalDigit->min = 0;
        $this->decimalDigit->step = 1;
        $this->cookieDuration->min = 0;
        $this->cookieDuration->step = 1;
        $this->cookieDuration->description = "<i>set to 0 for session cookies</i>";
    }

    public function html() {
        $this->initialize();
        $this->native->html();
    }

    public function setPage($page) {
        $this->native->setPage($page);
    }

    public function getField($key) {
        return $this->native->getField($key);
    }

    public function load() {
        $this->native->load();
    }

    public function getUrl() {
        return SRC\Order::URL . trim($this->phoneNumber->value);
    }

    /***
     * @param string $cluster_key key of the data cluster
     * @param array $inputs values sent by the user ( associative array [field key => input value] )
     * @return array $outputs values to be saved ( associative array [field key => output value] )
     */
    public function validate( $cluster_key, $inputs ) {
        $outputs = array();

        foreach ( $inputs as $field_key => $field_value ) {
            $field = $this->getField($field_key);
            if ( !is_null($field) ) {
                if ( $field->type == DATA\FieldType::SELECT ) {
                    $value = trim( sanitize_text_field( $field_value ) );
                    $outputs[$field_key] = $value;
                } else if ( $field->type == DATA\FieldType::NUMBER ) {
                    //$old = $field->getValue();
                    $value = intval( $field_value );
                    if ( $value < 0) {
                        $value = $field->getValue(); //previous value
                        $this->_plugin::msgErr( "{$field->title}: value is smaller than zero." );
                    }
                    $outputs[$field_key] = $value;
                } else if ( $field->type == DATA\FieldType::TEXTAREA ) {
                    $outputs[$field_key] = sanitize_textarea_field($field_value);
                } else {
                    $outputs[$field_key] = sanitize_text_field($field_value);
                }
            } else {
                $this->_plugin::msgErr( "Field '{$field_key}': not found!" );
            }
        }

        return $outputs;
    }

}