<?php
use \SOSIDEE_WHATS_ORDER\SRC as SRC;

$plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();
$form = $plugin->formItemList;
$items = $form->items;

$currCode = $plugin->config->currencySymbol->getValue();
$currSymbol = SRC\Currency::getSymbol( $currCode );

?>
<h1>Items List</h1>

<div class="wrap">

<?php $plugin::msgHtml(); ?>

<?php $form->htmlOpen(); ?>

    <br>
    <?php $form->htmlRowCount(count($items)); ?>
    <table class="form-table wso bordered pad2p" role="presentation">
        <thead>
        <tr>
            <th scope="col" class="bordered middled centered" style="width: 10%;">Code</th>
            <th scope="col" class="bordered middled centered" style="width: 70%;">Description</th>
            <th scope="col" class="bordered middled centered" style="width: 10%;">Price (<?php echo $currSymbol; ?>)</th>
            <th scope="col" class="bordered middled centered" style="width: 10%;">
                <?php $form->htmlButtonNew(); ?>
            </th>
        </tr>
        </thead>
        <tbody>
<?php
if ( is_array($items) && count($items) > 0 ) {
    for ( $n=0; $n<count($items); $n++ ) {
        $item = $items[$n];
        $id = $item->id;
        $code = $item->code;
        $description = $item->description;
        $price = number_format_i18n( $item->price, 2);
?>
        <tr>
            <td class="bordered middled centered"><?php echo esc_html( $code ); ?></td>
            <td class="bordered middled centered"><?php echo esc_html( $description ); ?></td>
            <td class="bordered middled righted" style="padding-right: 1em;"><?php echo esc_html( $price ); ?></td>
            <td class="bordered middled centered"><?php $form->htmlButtonEdit( $id ); ?></td>
        </tr>
<?php
    }
}
?>
        </tbody>
    </table>

<?php if ( is_array($items) && count($items) > 0 ) { ?>
    <br>
    <table class="form-table wso pad16p" role="presentation">
        <tbody>
        <tr>
            <td class="middled righted"><?php $form->htmlDelete( 'delete all data', 'Are you sure to delete all data?' ); ?></td>
        </tr>
        </tbody>
    </table>
<?php }
$form->htmlClose();
?>

</div>
