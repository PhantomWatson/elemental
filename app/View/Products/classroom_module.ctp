<h1>
	<?php echo $title_for_layout; ?>
</h1>

<?php if ($expired): ?>

    <?php if ($expiration): ?>
        <div class="alert alert-danger" role="alert">
            Your access to the Classroom Module expired on
            <strong><?php echo date('F j, Y', $expiration); ?></strong>.
        </div>
    <?php endif; ?>

    <?php echo $this->element('purchase_classroom_module'); ?>

<?php elseif (! $has_purchased): ?>

    <div class="alert alert-info" role="alert">
        Please purchase the Elemental Classroom Module to continue.
    </div>

    <?php echo $this->element('purchase_classroom_module'); ?>

<?php else: ?>

	<?php if ($expiration < strtotime('+30 days')): ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            Your access to the Classroom Module will expire on
            <strong><?php echo date('F j, Y', $expiration); ?></strong>.
        </div>
    <?php endif; ?>

    <object type="application/x-shockwave-flash" data="/classroom_module/vizi.swf" width="898" height="495" id="vizi" style="float: none; vertical-align:middle">
        <param name="movie" value="/classroom_module/vizi.swf" />
        <param name="quality" value="high" />
        <param name="bgcolor" value="#ffffff" />
        <param name="play" value="true" />
        <param name="loop" value="true" />
        <param name="wmode" value="window" />
        <param name="scale" value="showall" />
        <param name="menu" value="true" />
        <param name="devicefont" value="false" />
        <param name="salign" value="" />
        <param name="allowScriptAccess" value="sameDomain" />
        <a href="http://www.adobe.com/go/getflash">
            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
        </a>
    </object>

<?php endif; ?>