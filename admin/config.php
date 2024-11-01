<?php
    $plugin = \SOSIDEE_WHATS_ORDER\SosPlugin::instance();

?>
<h1><?php echo esc_html( $plugin->name ); ?></h1>
    <div class="wrap">
        <?php $plugin::msgHtml(); ?>
        <form method="post" action="options.php">
			<?php $plugin->config->html(); ?>
        </form>
    </div>
