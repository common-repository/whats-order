<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

use \SOSIDEE_WHATS_ORDER\FORM as FORM;

class ImportItem
{

    public const STATUS_UNKNOWN = 0;
    public const STATUS_OK = 1;
    public const STATUS_DOUBLE = 2;
    public const STATUS_SKIPPED = 3;
    public const STATUS_ERROR = 6;

    const DATA_CODE_INDEX = 0;
    const DATA_DESCRIPTION_INDEX = 1;
    const DATA_PRICE_INDEX = 2;
    const DATA_UNIT_INDEX = 3;

    public $id;
    public $code;
    public $description;
    public $price;
    public $unit;
    public $status;

    /***
     * @param $data array [code, description, price, unit]
     *      unit: * = get from settings
     */
    public function __construct( $data ) {
        $this->id = 0;
        $this->code = isset($data[self::DATA_CODE_INDEX]) ? trim($data[self::DATA_CODE_INDEX]) : '';
        $this->description = isset($data[self::DATA_DESCRIPTION_INDEX]) ? trim($data[self::DATA_DESCRIPTION_INDEX]) : '';
        $price = $data[self::DATA_PRICE_INDEX] ?? 0;
        $this->price = floatval($price);
        $this->unit = isset($data[self::DATA_UNIT_INDEX]) ? trim($data[self::DATA_UNIT_INDEX]) : '*';
        $this->status = self::STATUS_UNKNOWN;
    }

    public static function getStatusDescription( $value ) {
        $ret = '';
        switch ($value) {
            case self::STATUS_OK:
                $ret = 'ok';
                break;
            case self::STATUS_DOUBLE:
                $ret = 'double';
                break;
            case self::STATUS_SKIPPED:
                $ret = 'skipped';
                break;
            case self::STATUS_ERROR:
                $ret = 'error';
                break;
        }
        return $ret;
    }

    public static function getStatusList( $caption = false ) {
        $ret = array();

        if ($caption !== false) {
            $ret[self::STATUS_UNKNOWN] = $caption;
        }

        $ret[self::STATUS_OK] = self::getStatusDescription(self::STATUS_OK);
        $ret[self::STATUS_DOUBLE] = self::getStatusDescription(self::STATUS_DOUBLE);
        $ret[self::STATUS_SKIPPED] = self::getStatusDescription(self::STATUS_SKIPPED);
        $ret[self::STATUS_ERROR] = self::getStatusDescription(self::STATUS_ERROR);

        return $ret;
    }

    public static function getStatusIcon( $value ) {
        $ret = '';
        switch ($value) {
            case self::STATUS_UNKNOWN:
                $ret = FORM\Base::getIcon('question_mark', 'grey', self::getStatusDescription($value));
                break;
            case self::STATUS_OK:
                $ret = FORM\Base::getIcon('check_circle', 'green', self::getStatusDescription($value));
                break;
            case self::STATUS_DOUBLE:
                $ret = FORM\Base::getIcon('flag', 'darkorange', self::getStatusDescription($value));
                break;
            case self::STATUS_SKIPPED:
                $ret = FORM\Base::getIcon('filter_alt', 'blue', self::getStatusDescription($value));
                break;
            case self::STATUS_ERROR:
                $ret = FORM\Base::getIcon('error', 'red', self::getStatusDescription($value));
                break;
        }
        return $ret;
    }

    public static function check( &$data ) {
        for ( $n=0; $n<count($data); $n++ ) {
            $item = &$data[$n];
            if ( $item->code == '') {
                $item->status = self::STATUS_ERROR;
            }
            unset($item);
        }
        for ( $n=0; $n<count($data)-1; $n++ ) {
            $item1 = &$data[$n];
            if ( $item1->status == self::STATUS_UNKNOWN ) {
                for ( $k=$n+1; $k<count($data); $k++ ) {
                    $item2 = &$data[$k];
                    if ( $item2->status == self::STATUS_UNKNOWN ) {
                        if ( sosidee_strcasecmp($item2->code, $item1->code) == 0 ) {
                            $item2->status = self::STATUS_DOUBLE;
                            $item1->status = self::STATUS_DOUBLE;
                        }
                    }
                    unset($item2);
                }
                if ( $item1->status == self::STATUS_UNKNOWN ) {
                    $item1->status = self::STATUS_OK;
                }
            }
            unset($item1);
        }
        $item = &$data[count($data)-1];
        if ( $item->status == self::STATUS_UNKNOWN ) {
            $item->status = self::STATUS_OK;
        }
        unset($item);
    }


}