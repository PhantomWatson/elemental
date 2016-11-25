<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td colspan="2">
            <p style="margin: 16px 0;">
                <strong>
                    <?php echo h($student['User']['name']); ?>,
                </strong>
            </p>
            <p>
                Space has become available in <a href="<?php echo $course_view_url; ?>">the upcoming Elemental sexual assault protection course</a>
                that you registered for, so you have been automatically enrolled.
                If you have any questions about this course, you can email <?php echo $instructor['User']['name']; ?> at <a href="mailto:<?php echo $instructor['User']['email']; ?>"><?php echo $instructor['User']['email']; ?></a>.
            </p>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <strong>
                <?php echo __n('Date', 'Dates', count($course['CourseDate'])); ?>
            </strong>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($course['CourseDate'] as $course_date): ?>
                    <li style="list-style-type: none;">
                        <?php echo date('l, F j, Y', strtotime($course_date['date'])); ?>
                        at
                        <?php echo date('g:ia', strtotime($course_date['start_time'])); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </td>
        <td width="50%" valign="top">
            <strong>
                Location
            </strong>
            <address style="font-style: normal; padding-left: 20px;">
                <?php echo h($course['Course']['location']); ?>
                <br />
                <?php echo nl2br(h($course['Course']['address'])); ?>
                <br />
                <?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>
            </address>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <?php if (! $registration['CourseRegistration']['waiting_list'] && $course['Course']['message']): ?>
                <hr style="margin-top: 20px; margin-bottom: 20px; border: 0; border-top: 1px solid #eee;" />

                <h3>
                    A Message From the Instructor
                </h3>
                <p style="margin: 16px 0;">
                    <?php echo $course['Course']['message']; ?>
                </p>
            <?php endif; ?>

            <hr style="margin-top: 20px; margin-bottom: 20px; border: 0; border-top: 1px solid #eee;" />

            <h3>Cancellation</h3>
            <p style="margin: 16px 0;">
                <?php
                    if ($registration['CourseRegistration']['waiting_list']) {
                        $cancelling = 'removing yourself from the waiting list';
                        $button_label = 'Remove Self from Waiting List';
                    } else {
                        $cancelling = 'canceling your registration';
                        $button_label = 'Cancel Registration';
                    }
                ?>
                If you will not be able to attend this course, please let us know as soon as possible by <?php echo $cancelling; ?>.
                <?php if (! $registration['CourseRegistration']['waiting_list']): ?>
                    If you cancel your registration, you will still be able to re-register up until <?php echo date('F j, Y', strtotime($course['Course']['deadline'])); ?>.
                <?php endif; ?>
                <br />
                <a href="<?php echo $unreg_url; ?>" style="background-color: #d9534f; border-color: #d43f3a; border-radius: 4px; color: #fff; display: inline-block; padding: 6px 12px; text-decoration: none; vertical-align: middle;">
                    <?php echo $button_label; ?>
                </a>
            </p>
        </td>
    </tr>
</table>