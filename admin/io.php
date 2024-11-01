<?php
use \SOSIDEE_WHATS_ORDER\SRC as SRC;

$plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();
$form = $plugin->formIO;
$rows = $form->rows;

?>
<h1>Data import</h1>

<div class="wrap">

    <?php $plugin::msgHtml(); ?>

    <?php $form->htmlOpen(); ?>

    <table class="form-table wso" role="presentation">
        <tbody>
        <tr>
            <th scope="row" class="">File CSV</th>
            <td class="middled">
                <?php $form->htmlBrowse(); ?>
            </td>
            <td class="middled centered">
                <?php $form->htmlUpload(); ?>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">File header</th>
            <td class="middled">
                <?php $form->htmlSkipFirstRow(); ?>
            </td>
            <td class="middled"></td>
        </tr>
        <tr>
            <th scope="row" class="">Column delimiter</th>
            <td class="middled">
                <?php $form->htmlColumnDelimiter(); ?>
            </td>
            <td class="middled"></td>
        </tr>
        <tr>
            <th scope="row" class="">Character encoding</th>
            <td class="middled">
                <?php $form->htmlCharacterEncoding(); ?>
            </td>
            <td class="middled"></td>
        </tr>
    <?php if ( $form->canImport ) { ?>
        <tr>
            <th scope="row" class="">Old records will be</th>
            <td class="middled">
                <?php $form->htmlOldRecordMode(); ?>
            </td>
            <td class="middled"></td>
        </tr>
        <tr>
            <th scope="row" class=""></th>
            <td class="middled"></td>
            <td class="middled centered">
                <?php $form->htmlImport(); ?>
            </td>
        </tr>
    <?php } else {  ?>
        <tr>
            <th scope="row" class=""></th>
            <td class="middled"></td>
            <td class="middled centered">
                <?php $form->htmlDownload(); ?>
            </td>
        </tr>
    <?php } ?>
        </tbody>
    </table>

<?php if ( $form->dataUploaded ) { ?>

    <?php $form->htmlRowCount(count($rows)); ?>
    <table class="form-table swo bordered" role="presentation">
        <thead>
        <tr>
            <th scope="col" class="bordered middled centered" style="width: 5%"></th>
            <th scope="col" class="bordered middled centered" style="width: 15%">Code</th>
            <th scope="col" class="bordered middled centered" style="width: 70%">Description</th>
            <th scope="col" class="bordered middled centered" style="width: 10%">Price</th>
        </tr>
        </thead>
        <tbody>
    <?php
        if ( is_array($rows) && count($rows)>0 ) {
            for ($n=0; $n<count($rows); $n++) {
                $item = $rows[$n];
                $icon = SRC\ImportItem::getStatusIcon( $item->status );
                $code = $item->code;
                $description = $item->description;
                $price = number_format_i18n( $item->price, 2);
    ?>
        <tr>
            <td class="bordered middled centered"><?php echo sosidee_kses( $icon ); ?></td>
            <td class="bordered middled centered"><?php echo esc_html( $code ); ?></td>
            <td class="bordered middled"><?php echo esc_html( $description ); ?></td>
            <td class="bordered middled righted"><?php echo esc_html( $price ); ?></td>
        </tr>
            <?php }
        } ?>
        </tbody>
    </table>

<?php } ?>

<?php
    $form->htmlFile();
    $form->htmlClose();

    if ($form->dataUploaded) {
?>
    <div style="margin-top: 1em; margin-left: 0.5em; font-style: italic;">
        LEGEND<br>
        <?php
            $states = SRC\ImportItem::getStatusList();
            foreach ( $states as $key => $value ) {
                echo SRC\ImportItem::getStatusIcon($key) . ' ' . sosidee_kses($value) . '<br>';
            }
        ?>
    </div>
<?php } ?>

</div>
