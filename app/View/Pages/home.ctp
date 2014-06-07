<div id="welcome">
	<div class="col-lg-12 hidden-xs">
		<img src="/img/elemental.gif" class="img-responsive" alt="Elemental: A sexual assault protection program" />
	</div>
	<div id="welcome_message" class="row">
		<div class="col-lg-8 col-lg-offset-4 col-sm-12 col-xs-12">
			<div id="welcome_message_mover" class="visible-lg">
			</div>
			<p>
				Elemental is a sexual assault protection program that offers
				<strong>realistic training for realistic situations</strong>. This program:
			</p>
			<ul>
				<li>
					Combines the best of classroom education and self-defense training in a program <a href="http://jax.sagepub.com/site/misc/Index/Podcasts.xhtml">with proven long-term effectiveness</a>.
				</li>
				<li>
 					Offers an organized, cost-effective, and holistic curriculum that is grounded in social science research and is inclusive of a variety of participants, including gender and sexual minorities (GSM).
				</li>
				<li>
					Assists your institution in filling the most recent requirements of the <a href="http://media.wix.com/ugd/c62206_3b605aa14dc748d39efef9add87bedb1.pdf">Violence Against Women Act (including the SaVE Act)</a>. Elemental is a primary prevention and awareness program that offers face-to-face training and ongoing prevention through self-directed student review.
				</li>
				<li>
					Provides program participants with choices in the way they respond to an assault, each based on the four elements of <a href="http://skhquest.com">To-Shin Do</a>. Our name comes from this element-based problem-solving paradigm:
				</li>
 			</ul>
			<div class="row">
				<div class="col-sm-2 col-xs-4">
					<img src="/img/logo_earth.png" class="img-responsive" />
				</div>
				<div class="col-sm-4 col-xs-12">
					<span class="element earth">Earth</span> (Confident)
					<br />
					<em>Hold your ground</em>
				</div>
				<div class="col-sm-2 col-xs-2">
					<img src="/img/logo_water.png" class="img-responsive" />
				</div>
				<div class="col-sm-4 col-xs-10">
					<span class="element water">Water</span> (Surprised)
					<br />
					<em>Step back momentarily, assess, then react</em>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-2 col-xs-2">
					<img src="/img/logo_fire.png" class="img-responsive" />
				</div>
				<div class="col-sm-4 col-xs-10">
					<span class="element fire">Fire</span> (Connected)
					<br />
					<em>Anticipate the threat and stop it before it grows</em>
				</div>
				<div class="col-sm-2 col-xs-2">
					<img src="/img/logo_wind.png" class="img-responsive" />
				</div>
				<div class="col-sm-4 col-xs-10">
					<span class="element wind">Wind</span> (Withdrawn)
					<br />
					<em>Exploit weaknesses within the threat that enable you to leave entirely</em>
				</div>
			</div>
			<div class="row">
					<?php echo $this->Html->link(
						'<span class="glyphicon glyphicon-info-sign"></span> Learn More',
						array(
							'controller' => 'pages',
							'action' => 'about_intro'
						),
						array(
							'class' => 'btn btn-primary btn-large col-xs-12 col-sm-5',
							'escape' => false
						)
					); ?>

					<?php echo $this->Html->link(
						'<span class="glyphicon glyphicon-play"></span> Student Review Materials',
						array(
							'controller' => 'products',
							'action' => 'review_materials'
						),
						array(
							'class' => 'btn btn-primary btn-large col-xs-12 col-sm-6 col-sm-offset-1',
							'escape' => false
						)
					); ?>
			</div>
			<div class="row">
					<?php echo $this->Html->link(
						'<span class="glyphicon glyphicon-user"></span> Become an Instructor',
						array(
							'controller' => 'products',
							'action' => 'instructor_certification'
						),
						array(
							'class' => 'btn btn-primary btn-large col-xs-12 col-sm-5',
							'escape' => false
						)
					); ?>

					<?php echo $this->Html->link(
						'<span class="glyphicon glyphicon-road"></span> Bring Elemental to Your Campus',
						array(
							'controller' => 'pages',
							'action' => 'booking'
						),
						array(
							'class' => 'btn btn-primary btn-large col-xs-12 col-sm-6 col-sm-offset-1',
							'escape' => false
						)
					); ?>

			</div>
		</div>
	</div>
</div>

<hr />

<div class="row">
	<div class="col-lg-6">
		<?php echo $this->element('courses/front_page_list'); ?>
	</div>
	<div class="col-lg-6">
		<?php echo $this->element('testimonials/front_page'); ?>
	</div>
</div>

<hr />

<?php if (! empty($article)): ?>
	<div class="row">
		<div class="<?php echo empty($more_articles) ? 'col-lg-12' : 'col-lg-9'; ?>">
			<h2>Latest News</h2>
			<h3>
				<?php echo $article['Article']['title']; ?>
			</h3>

			<p class="article_posted_time">
				Posted on
				<?php
					$timestamp = strtotime($article['Article']['created']);
					echo date('M j', $timestamp);
					echo '<sup>'.date('S', $timestamp).'</sup>';
					echo date(', Y', $timestamp);
				?>
			</p>

			<div class="articles view">
				<?php echo $article['Article']['body']; ?>
			</div>
		</div>
		<?php if (! empty($more_articles)): ?>
			<div class="col-lg-3">
				<h2>More Articles</h2>

				<ul class="more_articles list-unstyled">
					<?php foreach ($more_articles as $a): ?>
						<li>
							<?php echo $this->Html->link(
								h($a['Article']['title']),
								array(
									'controller' => 'articles',
									'action' => 'view',
									'id' => $a['Article']['id']
								)
							); ?>
							<br />
							<span class="date">
								<?php
									$timestamp = strtotime($a['Article']['created']);
									echo date('M j', $timestamp);
									echo '<sup>'.date('S', $timestamp).'</sup>';
									echo date(', Y', $timestamp);
								?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php echo $this->Html->link(
					'Browse all articles &raquo;',
					array(
						'controller' => 'articles',
						'action' => 'index'
					),
					array(
						'class' => 'btn btn-default',
						'escape' => false
					)
				); ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>