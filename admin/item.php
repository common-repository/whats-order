<?php
use \SOSIDEE_WHATS_ORDER\DB as DB;

$plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();
$form = $plugin->formItemEdit;

$title = 'Item Edit';
if ( $form->getId() == 0 ) {
    $title = 'New ' . $title;
}
echo '<h1>'.  esc_html( $title ) . '</h1>';

?>

<div class="wrap">

<?php $plugin::msgHtml(); ?>

<?php $form->htmlOpen(); ?>
    <table class="form-table wso" role="presentation">
        <tbody>
        <tr>
            <th scope="row" class="">Code *</th>
            <td class="middled">
                <?php $form->htmlCode(); ?>
                <span class="note"> only letters, numbers, hyphens and underscores (max. <?php echo DB\Item::CODE_LENGTH; ?> chars.)</span>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Description</th>
            <td class="middled">
                <?php $form->htmlDescription(); ?>
                <span class="note">(max. <?php echo DB\Item::DESCRIPTION_LENGTH; ?> chars.)</span>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Unit</th>
            <td class="middled">
                <?php $form->htmlUnit(); ?>
            </td>
        </tr>
        <tr>
            <th scope="row" class="">Price</th>
            <td class="middled">
                <?php $form->htmlPrice(); ?>
            </td>
        </tr>
        </tbody>
    </table>

    <p style="font-style: italic;">* mandatory field</p>

    <table role="presentation" style="margin-top: 1em;">
        <tbody>
        <tr>
            <td style="width: 120px;">
                <?php $form->htmlDelete( 'delete', 'Are you sure to delete it?' ); ?>
            </td>
            <td style="width: 120px;">
                <?php $form->htmlButtonLink(0); ?>
            </td>
            <td style="width: 120px;">
                <?php $form->htmlSave(); ?>
            </td>
        </tr>
        </tbody>
    </table>

<?php
    $form->htmlId();
    $form->htmlClose();
?>

    <hr>

    <table role="presentation" style="margin-top: 1em;">
        <tbody>
        <tr>
            <td style="width: 120px;">
                <?php $form->htmlButtonBack(); ?>
            </td>
        </tr>
        </tbody>
    </table>



</div>
