<?php
namespace SOSIDEE_WHATS_ORDER\FORM;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SRC as SRC;
use \SOSIDEE_WHATS_ORDER\DB as DB;
use \SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;

class ItemEdit extends Base
{
    const QS_ID = 'swo-id';

    private $id;
    private $code;
    private $description;
    private $price;
    private $unit;

    public function __construct() {
        parent::__construct( 'itemEdit', [$this, 'onSubmit'] );

        $this->table = $this->_database->items;

        $this->id = $this->addHidden('id', 0);
        $this->code = $this->addTextBox('code', '');
        $this->description = $this->addTextBox('description', '');
        $this->price = $this->addTextBox('price', 0);
        $this->unit = $this->addTextBox('unit', '');

        $this->reset();
    }

    private function reset() {
        $this->id->value = 0;
        $this->code->value = '';
        $this->description->value = '';
        $this->price->value = 0;
        $config = $this->_plugin->config;
        $config->itemUnit->load();
        $this->unit->value = $config->itemUnit->value;
    }

    public function getId() {
        return $this->id->value;
    }

    public function htmlId() {
        $this->id->html();
    }
    public function htmlCode() {
        $this->code->html( ['maxlength' => DB\Item::CODE_LENGTH] );
    }
    public function htmlDescription() {
        $this->description->html( ['maxlength' => DB\Item::DESCRIPTION_LENGTH, 'class' => 'regular-text'] );
    }
    public function htmlPrice() {
        $this->price->html( [
             'class' => 'currency'
            ,'onkeydown' => 'return jsCheckNumber(event);'
        ] );

        $currCode = $this->_plugin->config->currencySymbol->getValue();
        $currSymbol = SRC\Currency::getSymbol( $currCode );
        echo " " . wp_kses_post($currSymbol);
    }
    public function htmlUnit() {
        $this->unit->html( [
             'maxlength' => DB\Item::UNIT_LENGTH
            ,'class' => 'small-text'
        ] );
    }

    protected function initialize() {
        if ( !$this->_posted ) {
            $id = sosidee_get_query_var(self::QS_ID, 0);
            if ( $id > 0 ) {
                $this->load( $id );
            }
        }
    }

    public function load( $id ) {
        if ( $id > 0 ) {
            $data = $this->table->load( $id );
            if ( $data !== false ) {
                $this->id->value = $data->id;
                $this->code->value = $data->code;
                $this->description->value = $data->description;
                $this->price->value = $data->price;
                $this->unit->value = $data->unit;
            } else {
                self::msgErr( "A problem occurred while reading the database." );
            }
        } else {
            self::msgErr( "A problem occurred: record id is zero." );
        }
    }

    public function onSubmit() {
        $table = $this->table;
        $this->id->value = intval( $this->id->value );

        if ( $this->_action == 'save' ) {
            $save = true;

            $this->code->value = preg_replace('/[^a-zA-Z0-9_\-]+/',  '', $this->code->value );
            if ( $this->code->value == '' ) {
                $save = false;
                self::msgErr( 'Code is empty.' );
            } else {
                if ( $table->codeExists( $this->code->value, $this->id->value ) ) {
                    $save = false;
                    self::msgErr( 'This code has been used for another item.' );
                }
            }

            if ( $save ) {

                $price = str_replace( ',', '.', trim( $this->price->value ) );

                $data = [
                     $table->code->name => trim( $this->code->value )
                    ,$table->description->name => trim( $this->description->value )
                    ,$table->price->name => floatval( $price )
                    ,$table->unit->name => $this->unit->value
                ];

                $result = $table->save( $data, $this->id->value );
                if ( $result !== false ) {
                    if ( $result === true ) {
                        self::msgOk( 'Data have been saved.' );
                        $this->load( $this->id->value );
                    } else {
                        $id = intval($result);
                        if ( $id > 0 ) {
                            $this->load( $id );
                            self::msgOk( 'Data have been added.' );
                        } else {
                            self::msgErr( 'A problem occurred while adding the data.' );
                        }
                    }
                } else {
                    self::msgErr( 'A problem occurred while saving the data.' );
                }

            }

        } else if ( $this->_action == 'delete' ) {

            if ( $this->id->value > 0 ) {

                if ( $table->cancel( $this->id->value ) !== false ) {
                    $this->reset();
                    self::msgOk( 'Data have been deleted.' );
                } else {
                    self::msgErr( 'A problem occurred while deleting the item.' );
                }

            } else {
                self::msgWarn( "Cannot delete data not saved yet." );
            }

        } else {
            self::msgErr( "Invalid form action: {$this->_action}." );
        }
    }

    private function loadItem( $id ) {
        if ( $id > 0 ) {
            $item= $this->table->load( $id );
            if ( $item !== false ) {
                $this->id->value = $item->id;
                $this->code->value = $item->code;
                $this->description->value = $item->description;
                $this->price->value = $item->price;
                $this->unit->value = $item->unit;

            } else {
                self::msgErr( "A problem occurred while reading the database." );
            }

        } else {
            self::msgErr( "A problem occurred: record id is zero." );
        }
    }

    /*
    public function getUrl( $id = 0 ) {
        return $this->_plugin->pageItemEdit->getUrl( [self::QS_ID => $id] );
    }
    */

    public function htmlButtonLink( $id ) {
        $url = $this->_plugin->pageItemEdit->getUrl( [self::QS_ID => $id] );
        if ( $id == 0 ) {
            parent::htmlLinkButton( $url, 'create new' );
        } else {
            parent::htmlLinkButton( $url, 'edit', DATA\FormButton::STYLE_SUCCESS );
        }
    }

    public function htmlButtonBack() {
        $this->_plugin->formItemList->htmlButtonLink('back to item list');
        //$url = $this->_plugin->pageItemList->getUrl();
        //parent::htmlLinkButton2( $url, 'back to item list', 'min-width:120px;' );
    }

}