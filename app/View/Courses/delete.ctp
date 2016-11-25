<div class="page-header">
    <h1>
        <?php echo $title_for_layout; ?>
    </h1>
</div>

<?php foreach ($messages as $message): ?>
    <?php
        switch ($message['class']) {
            case 'success':
                $class = 'alert-success';
                break;
            case 'error':
                $class = 'alert-danger';
                break;
            default:
                $class = 'alert-info';
        }
    ?>
    <p class="alert <?php echo $class; ?>">
        <?php echo $message['message']; ?>
    <p>
<?php endforeach; ?>