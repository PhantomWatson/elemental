<?php echo h($course['Course']['location']); ?>

(<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>)

on

<?php echo $this->element('courses/date_list'); ?>.

<?php echo $this->Html->link('View course details', array(
	'controller' => 'courses',
	'action' => 'view',
	'id' => $course['Course']['id']
)); ?>