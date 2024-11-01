<?php
namespace SOSIDEE_WHATS_ORDER\FORM;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\SOS\WP\DATA as DATA;
use \SOSIDEE_WHATS_ORDER\SRC as SRC;


class IO extends Base
{


    private const RECORD_MODE_NONE = 0;
    private const RECORD_MODE_OVERWRITE = 1;
    private const RECORD_MODE_SKIP = 2;
    private const RECORD_MODE_DELETE = 3;

    private static $RECORD_MODES = [
       self::RECORD_MODE_OVERWRITE => 'overwritten if already present'
      ,self::RECORD_MODE_SKIP => 'skipped if already present'
      ,self::RECORD_MODE_DELETE => 'all deleted before import'
    ];

    private $folder;
    private $upload;
    private $file;

    private $skipFirstRow;
    private $colDelimiter;
    private $charEncoding;

    private $oldRecordMode;

    public $dataUploaded;
    public $canImport;

    public $rows;

    public function __construct() {
        parent::__construct( 'IO', [$this, 'onSubmit'] );

        $tmp = $this->_plugin->getTempFolder();
        if ( $tmp !== false ) {
            $this->folder = $tmp['basedir'];
        } else {
            self::msgErr( 'Cannot get a temporary folder.' );
        }

        $this->upload = $this->addFilePicker('file_csv');
        $this->file = $this->addHidden('file_uploaded');

        $this->skipFirstRow = $this->addCheckBox('skip_first_row', true);
        $this->skipFirstRow->cached = true;

        $this->colDelimiter = $this->addSelect('column_delimiter', ',');
        $this->colDelimiter->cached = true;

        $this->charEncoding = $this->addSelect('character_encoding', 'Windows-1252');
        $this->charEncoding->cached = true;

        $this->oldRecordMode = $this->addSelect('old_record_mode', self::RECORD_MODE_SKIP);
        $this->oldRecordMode->cached = true;

        $this->dataUploaded = false;
        $this->canImport = false;
        $this->rows = [];
    }

    public function htmlBrowse() {
        $this->upload->html( ['accept' => '.csv'] );
    }

    public function htmlUpload() {
        $this->htmlButton( 'upload', 'upload file' );
    }

    public function htmlDownload() {
        $plugin = $this->_plugin;
        $url = $plugin::$url . '/assets/data/template.csv';
        echo DATA\FormTag::get( 'input', [
                'type' => 'button'
                ,'id' => null
                ,'name' => null
                ,'value' => 'download template'
                ,'class' => 'button button-secondary'
                ,'style' => null
                ,'onclick' => "window.open('{$url}', '_blank', 'popup=1');"
                ,'title' => 'click to download a template CSV file'
            ]
        );
    }

    public function htmlImport() {
        $this->htmlButton( 'import', 'import data', DATA\FormButton::STYLE_SUCCESS );
    }

    public function htmlFile() {
        $this->file->html();
    }

    public function htmlOldRecordMode() {
        $this->oldRecordMode->html( [ 'options' => self::$RECORD_MODES ] );
    }

    public function htmlColumnDelimiter() {
        $this->colDelimiter->html( [ 'options' => SRC\Csv::getDelimiters() ] );
    }

    public function htmlCharacterEncoding() {
        $this->charEncoding->html( [ 'options' => SRC\Csv::getEncodings() ] );
    }

    public function htmlSkipFirstRow() {
        $this->skipFirstRow->html( ['label' => 'skip the first row of the file'] );
    }

    protected function initialize() {
        //$this->skipFirstRow->value = true;
    }


    private function loadCsv( $path ) {
        $ret = false;
        $this->rows = [];
        $delimiter = $this->colDelimiter->value;
        $in_charset = $this->charEncoding->value;
        $skip1row = $this->skipFirstRow->value;
        $data = SRC\Csv::load($path, [
             'delimiter' => $delimiter
            ,'in_charset' => $in_charset
            ,'skip_first_row' => $skip1row
        ]);
        if ( $data !== false) {
            if ( count($data) > 0 ) {
                $config = $this->_plugin->config;
                $config->itemUnit->load();
                $this->dataUploaded = true;
                for ( $n=0; $n<count($data); $n++ ) {
                    $item = $data[$n];
                    $row = new SRC\ImportItem( $item );
                    if ( $row->unit == '*') {
                        $row->unit = $config->itemUnit->value;
                    }
                    $this->rows[] = $row;
                }
                SRC\ImportItem::check( $this->rows );
                $totDbl = 0;
                $totErr = 0;
                for ( $n=0; $n<count($this->rows); $n++ ) {
                    if ( $this->rows[$n]->status == SRC\ImportItem::STATUS_DOUBLE ) {
                        $totDbl++;
                    } else if ( $this->rows[$n]->status == SRC\ImportItem::STATUS_ERROR ) {
                        $totErr++;
                    }
                }
                if ( $totDbl > 0) {
                    self::msgWarn( "$totDbl items are doubled." );
                }
                if ( $totErr > 0) {
                    if ( $totErr == 1 ) {
                        self::msgErr( "$totErr item is incorrect." );
                    } else {
                        self::msgErr( "$totErr items are incorrect." );
                    }
                }
                if ( count($data) == count($this->rows) ) {
                    $ret = ($totErr + $totDbl) == 0;
                } else {
                    self::msgErr( 'Uploaded file could not be read correctly.' );
                }
            } else {
                self::msgErr( 'The file does not contains data rows.' );
            }
        } else {
            self::msgErr( 'Cannot read the file content.' );
        }
        return $ret;
    }

    public function onSubmit() {
        if ( $this->_action == 'upload' ) {

            $data = $this->upload->data;
            if ( $data->error == UPLOAD_ERR_OK ) {
                $res = $data->moveTo( $this->folder );
                if ( $res !== false ) {
                    $this->file->value = $res;
                    if ( $this->loadCsv($res) ) {
                        $this->canImport = true;
                        self::msgInfo( 'File uploaded: click the import button to insert the data.' );
                    }
                } else {
                    self::msgErr( 'Uploaded file could not be saved.' );
                }
            } else {
                self::msgErr( DATA\FormFile::getErrorDescription($data->error) );
            }

        } else if ( $this->_action == 'import' ) {

            $path = realpath( $this->file->value );
            if ( file_exists($path) ) {
                $this->canImport = true;
                if ( $this->loadCsv($path) ) {
                    $table = $this->_database->items;
                    $mode = $this->oldRecordMode->value;
                    if ( $mode == self::RECORD_MODE_DELETE ) {
                        if ( $table->clear() ) {
                            self::msgInfo( 'Old records have been removed.' );
                        } else {
                            $mode = self::RECORD_MODE_NONE;
                            self::msgErr( 'A problem occurred while removing the old records.' );
                        }
                        $old_items = [];
                    } else {
                        $old_items = $this->_database->items->list();
                        if ( $old_items === false ) {
                            $mode = self::RECORD_MODE_NONE;
                            self::msgErr( 'A problem occurred while reading the old records.' );
                        }
                    }
                    if ( $mode != self::RECORD_MODE_NONE ) {
                        for ( $n=0; $n<count($this->rows); $n++ ) {
                            $item = &$this->rows[$n];
                            for ( $k=0; $k<count($old_items); $k++ ) {
                                $old_item = $old_items[$k];
                                if ( sosidee_strcasecmp($old_item->code, $item->code) == 0 ) {
                                    if ( $mode == self::RECORD_MODE_SKIP ) {
                                        $item->status = SRC\ImportItem::STATUS_SKIPPED;
                                    } else if ( $mode == self::RECORD_MODE_OVERWRITE ) {
                                        $item->id = $old_item->id;
                                    }
                                    break;
                                }
                            }
                            unset($item);
                        }
                        $totInsert = 0;
                        $totError = 0;
                        for ( $n=0; $n<count($this->rows); $n++ ) {
                            $item = &$this->rows[$n];
                            if ( $item->status == SRC\ImportItem::STATUS_OK ) {
                                $data = [
                                     $table->code->name => $item->code
                                    ,$table->description->name => $item->description
                                    ,$table->price->name => $item->price
                                    ,$table->unit->name => $item->unit
                                ];
                                if ( $table->save( $data, $item->id ) !== false ) {
                                    $totInsert++;
                                } else {
                                    $item->status = SRC\ImportItem::STATUS_ERROR;
                                    $totError++;
                                }
                            }
                            unset($item);
                        }
                        if ( $totInsert > 0 ) {
                            if ( $totInsert == 1) {
                                self::msgOK( "$totInsert item has been successfully imported." );
                            } else {
                                self::msgOK( "$totInsert data have been successfully imported." );
                            }
                        } else {
                            self::msgInfo( 'No data have been imported.' );
                        }
                        if ( $totError > 0 ) {
                            self::msgErr( 'A problem occurred while importing the data.' );
                        }
                    }
                }
            } else {
                self::msgErr( 'Uploaded file is not available any more.' );
            }

        }

    }

}