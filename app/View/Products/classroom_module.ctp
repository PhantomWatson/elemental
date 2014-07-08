<h1>
	<?php echo $title_for_layout; ?>
</h1>

<?php if ($can_access): ?>

	<?php if ($warn): ?>
		<div class="alert alert-warning alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span>
				<span class="sr-only">Close</span>
			</button>
			Your access to the Classroom Module will expire on
			<strong><?php echo date('F j, Y', $expiration); ?></strong>.
		</div>
	<?php endif; ?>

	<script language="javascript">AC_FL_RunContent = 0;</script>
	<script src="/teaching_version/AC_RunActiveContent.js" language="javascript"></script>
	<p id="vizi_player">
		<script language="javascript">
			if (AC_FL_RunContent == 0) {
				alert("This page requires AC_RunActiveContent.js.");
			} else {
				AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0','width','898','height','495','align','left','id','ViziRECOVERED10-27-09','src','/teaching_version/vizi','quality','high','bgcolor','#ffffff','name','ViziRECOVERED10-27-09','allowscriptaccess','sameDomain','allowfullscreen','false','pluginspage','http://www.macromedia.com/go/getflashplayer','movie','/teaching_version/vizi' ); //end AC code
			}
		</script>
	</p>
	<noscript>
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="898" height="495" align="left" id="ViziRECOVERED10-27-09">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="allowFullScreen" value="false" />
			<param name="movie" value="/teaching_version/vizi.swf" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<embed src="/teaching_version/vizi.swf" width="898" height="495" align="left" quality="high" bgcolor="#ffffff" name="ViziRECOVERED10-27-09" allowscriptaccess="sameDomain" allowfullscreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>
	</noscript>
	<map name="Map">
		<area shape="rect" coords="575,16,889,56" href="/">
	</map>

<?php else: ?>

	<?php if ($expiration): ?>
		<div class="alert alert-danger" role="alert">
			Your access to the Classroom Module expired on
			<strong><?php echo date('F j, Y', $expiration); ?></strong>.
		</div>
	<?php endif; ?>

<?php endif; ?>