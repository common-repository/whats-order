<?php
namespace SOSIDEE_WHATS_ORDER\FORM;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SRC as SRC;
use \SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;

class ItemList extends Base
{
    public $items;

    public function __construct() {
        parent::__construct( 'itemList', [$this, 'onSubmit'] );

        $this->table = $this->_database->items;

        $this->items = [];
    }

    protected function initialize() {
        if ( !$this->_posted ) {
            $this->loadItems();
        }
    }

    public function onSubmit() {

        if ( $this->_action == 'delete' ) {
            $table = $this->_database->items;
            if ( $table->clear() ) {
                self::msgOk( 'All data have been removed.' );
            } else {
                self::msgErr( 'A problem occurred while removing the data.' );
            }

        }


    }

    public function loadItems() {
        $this->items = [];

        $results = $this->table->list();

        if ( is_array($results) ) {
            if ( count($results) > 0 ) {
                for ( $n=0; $n<count($results); $n++ ) {
                    /*
                    $results[$n]->creation_string = $results[$n]->creation->format( "Y/m/d H:i:s" );
                    $results[$n]->url_api = $this->_plugin->getApiUrl( $results[$n]->code );
                    $results[$n]->status_icon = SRC\QrCodeSearchStatus::getStatusIcon( !$results[$n]->disabled );
                    */
                }
            } else {
                self::msgInfo( "There's no items in the database." );
            }
            $this->items = $results;
        } else {
            self::msgErr( 'A problem occurred.' );
        }
    }

    public function htmlButtonLink( $label ) {
        $url = $this->_plugin->pageItemList->getUrl();
        parent::htmlLinkButton2( $url, $label, 'min-width:120px;' );
    }

    public function htmlButtonNew() {
        $this->_plugin->formItemEdit->htmlButtonLink( 0 );
    }
    public function htmlButtonEdit( $id ) {
        $this->_plugin->formItemEdit->htmlButtonLink( $id );
    }


}