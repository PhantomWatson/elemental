<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php
		if ($title_for_layout) {
			echo 'Elemental - '.strip_tags($title_for_layout);
		} else {
			echo 'Elemental Sexual Assault Protection';
		}
		?>
	</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="icon" type="image/png" href="/favicon.png" />
	<link rel="stylesheet" href="/css/vendor/bootstrap.min.css">

	<style>
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
	</style>
	<link rel="stylesheet" href="/css/main.css">
	<script src="/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
	<?php
		echo $this->fetch('meta');
		echo $this->fetch('css');
	?>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-43654529-1', 'elementalprotection.org');
	  ga('send', 'pageview');
	</script>
</head>
<body>
	<!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

	<!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->

	<?php echo $this->element('header_nav'); ?>

	<div class="container">


		<?php echo $this->element('flash_messages'); ?>

		<?php echo $this->fetch('content'); ?>

		<hr>

		<footer>
			<p>
				&copy; Elemental Sexual Assault Protection LLC <?php echo date('Y'); ?>
				<?php echo $this->Html->link(
					'Terms',
					array(
						'controller' => 'pages',
						'action' => 'terms'
					)
				); ?>
				<?php echo $this->Html->link(
					'Privacy',
					array(
						'controller' => 'pages',
						'action' => 'privacy'
					)
				); ?>
				<br />
				Elemental is not affiliated with, endorsed by, or sponsored by SKH Quest Centers or To-Shin Do
			</p>
		</footer>

	</div>
	<!-- /container -->

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
	<script src="/js/vendor/bootstrap.min.js"></script>
	<script src="/js/main.js"></script>
	<?php echo $this->fetch('script'); ?>
	<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>