<li class="dropdown user_menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Instructor
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu">
		<li class="dropdown-header">
			Certification expired
		</li>
		<li>
		    <?php echo $this->Html->link(
                'Contact us to get recertified',
                array(
                    'controller' => 'pages',
                    'action' => 'contact',
                    $this->params['prefix'] => false
                )
            ); ?>
		</li>
	</ul>
</li>