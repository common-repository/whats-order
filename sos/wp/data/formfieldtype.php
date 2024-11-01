<?php
namespace SOSIDEE_WHATS_ORDER\SOS\WP\DATA;
defined( 'SOSIDEE_WHATS_ORDER' ) or die( 'you were not supposed to be here' );

class FormFieldType
{
    const TEXT = 1;
    const TEXTAREA = 2;
    const CHECK = 3;
    const SELECT = 4;
    const COLOR = 5;
    const NUMBER = 6;
    const DATE = 7;
    const TIME = 8;
    const HIDDEN = 9;
    const COMBOBOX = 10;
    const FILE = 11;
}