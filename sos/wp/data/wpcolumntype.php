<?php
namespace SOSIDEE_WHATS_ORDER\SOS\WP\DATA;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );


class WpColumnType
{
    const BOOLEAN = 'bit';

    const INTEGER = 'int';
    const TINY_INTEGER = 'tinyint';
    const SMALL_INTEGER = 'smallint';

    const FLOAT = 'float';
    const DOUBLE = 'double';
    const DECIMAL = 'decimal';
    const CURRENCY = 'decimal';

    const DATETIME = 'datetime';
    const TIMESTAMP = 'timestamp';
    const TIME = 'time';

    const VARCHAR = 'varchar';
    const TEXT = 'text';
}