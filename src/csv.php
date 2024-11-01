<?php
namespace SOSIDEE_WHATS_ORDER\SRC;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

/***
 * https://www.phptutorial.net/php-tutorial/php-csv/
 */
class Csv
{

    public static function getDelimiters() {
        return [
             ',' => ', (comma)'
            ,';' => '; (semicolon)'
        ];
    }

    public static function getEncodings() {
        return [
            'Windows-1252' => 'Windows-1252'
            ,'UTF-8' => 'UTF-8'
        ];
    }

    public static function load( $path, $parameters = array() ) {
        $ret = false;
        $plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();

        try {

            if ( file_exists($path) ) {
                $skip_first_row = false;
                $delimiter = ',';
                $enclosure = '"';
                $escape = "\\";
                $length = 0;
                $row_max = 0;
                $in_charset = 'Windows-1252';
                $out_charset = 'UTF-8';

                extract($parameters, EXTR_IF_EXISTS);

                $skipped = !$skip_first_row;
                if ( ($handle = fopen($path, "r")) !== false ) {
                    $ret = array();
                    while ( ($data = fgetcsv($handle, $length, $delimiter, $enclosure, $escape)) !== false ) {
                        if ( $skipped ) {
                            for ( $n=0; $n < count($data); $n++ ) {
                                $data[$n] = iconv( $in_charset, "$out_charset//TRANSLIT", $data[$n] );
                            }
                            $ret[] = $data;
                        } else {
                            $skipped = true;
                        }
                        if ( $row_max > 0 && count($ret) >= $row_max ) {
                            break;
                        }
                    }
                    fclose($handle);
                }
            } else {
                sosidee_log("SRC\Csv.load() file not found: $path" );
                $plugin::msgErr( "File not found:<br>$path" );
            }

        } catch ( \Exception $ex ) {
            sosidee_log($ex);
            $plugin::msgErr( $ex->getMessage() );
        }
        return $ret;
    }


    public static function save( $path, $lines, $parameters = array() ) {
        $ret = false;
        $plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();

        try {

            $delimiter = ',';
            $enclosure = '"';
            $escape = "\\";
            $out_charset = 'Windows-1252';
            $in_charset = 'UTF-8';

            extract($parameters, EXTR_IF_EXISTS);

            if ( ($handle = fopen($path, "w")) !== false ) {
                $ret = true;
                for ($i=0; $i<count($lines); $i++) {
                    $data = $lines[$i];
                    if ( $in_charset != $out_charset ) {
                        for ($j=0; $j<count($data); $j++) {
                            $data[$j] = iconv( $in_charset, "$out_charset//TRANSLIT", $data[$j] );
                        }
                    }
                    $res = fputcsv($handle, $data, $delimiter, $enclosure, $escape);
                    if ( $res === false ) {
                        $ret = false;
                        sosidee_log("SRC\Csv.save() fputcsv() returned false for data=" . print_r($data, true) );
                    }
                }
                fclose($handle);
            }

        } catch ( \Exception $ex ) {
            sosidee_log($ex);
            $plugin::msgErr( $ex->getMessage() );
        }
        return $ret;
    }

}