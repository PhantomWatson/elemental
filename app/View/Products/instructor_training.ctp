<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
	<p>
		
	</p>
	<?php if ($logged_in): ?>
		<?php if ($can_access): ?>
			<?php
				$lesson_path = '/instructor_training/Lesson 1/Current Lesson';
				$swf_path = $lesson_path.'/vizi.swf';
				$ac_src = $lesson_path.'/vizi';
			?>
			
			<script language="javascript">AC_FL_RunContent = 0;</script>
			<script src="<?php echo $lesson_path; ?>/AC_RunActiveContent.js" language="javascript"></script>
			<p id="vizi_player">
				<script language="javascript">
					if (AC_FL_RunContent == 0) {
						alert("This page requires AC_RunActiveContent.js.");
					} else {
						AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0','width','898','height','495','align','left','id','ViziRECOVERED10-27-09','src','<?php echo $ac_src; ?>','quality','high','bgcolor','#ffffff','name','ViziRECOVERED10-27-09','allowscriptaccess','sameDomain','allowfullscreen','false','pluginspage','http://www.macromedia.com/go/getflashplayer','movie','<?php echo $ac_src; ?>' ); //end AC code
					}
				</script>
			</p>
			<noscript>
				<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="898" height="495" align="left" id="ViziRECOVERED10-27-09">
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="allowFullScreen" value="false" />
					<param name="movie" value="<?php echo $swf_path; ?>" />
					<param name="quality" value="high" />
					<param name="bgcolor" value="#ffffff" />
					<embed src="<?php echo $swf_path; ?>" width="898" height="495" align="left" quality="high" bgcolor="#ffffff" name="ViziRECOVERED10-27-09" allowscriptaccess="sameDomain" allowfullscreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />                        
				</object>
			</noscript>
			<map name="Map">
				<area shape="rect" coords="575,16,889,56" href="/">
			</map>

		<?php endif; ?>
	<?php else: ?>
		<p class="alert alert-info">
			For information about becoming certified as an Elemental instructor, 
			<?php echo $this->Html->link(
				'contact us',
				array(
					'controller' => 'pages',
					'action' => 'contact'
				)
			); ?>.
		</p>
		<p>
			<?php echo $this->Html->link(
				'Log in',
				array(
					'controller' => 'users',
					'action' => 'login'
				),
				array(
					'class' => 'btn btn-primary btn-large'
				)
			); ?>
		</p>
	<?php endif; ?>
</div>

<?php if ($logged_in && ! $can_access): ?>
	<p class="alert alert-info">
		You are not currently authorized to access the instructor training module. 
		For assistance or for more information about becoming certified as an Elemental instructor, 
		<?php echo $this->Html->link(
			'contact us',
			array(
				'controller' => 'pages',
				'action' => 'contact'
			)
		); ?>.
	</p>
<?php endif; ?>