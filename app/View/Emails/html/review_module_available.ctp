<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td>
            <p style="margin: 16px 0;">
                <strong>
                    <?php echo $student_name; ?>,
                </strong>
            </p>

            <p style="margin: 16px 0;">
                Thank you for attending a recent Elemental course. You now have one year of access to the <strong>Elemental Student Review Module</strong>.
            </p>

            <p style="margin: 16px 0;">
                <a href="<?php echo $review_module_link; ?>" style="background-color: #337ab7; border-color: #2e6da4; border-radius: 4px; color: #fff; display: inline-block; padding: 6px 12px; text-decoration: none; vertical-align: middle;">
                    Access the Student Review Module
                </a>
            </p>

            <p style="margin: 16px 0;">
                This is an online multimedia summary of the physical and verbal techniques taught during the course you attended. It includes a video review of all of the techniques covered, summaries of the main points to remember for each defense, and a suggested review schedule.
            </p>

            <p style="margin: 16px 0;">
                If you have any questions about the material covered in your course, your instructor can be reached at
                <a href="mailto:<?php echo $instructor_email; ?>"><?php
                    echo $instructor_email;
                ?></a>.
                If you have any trouble using the website, please let us know through
                <a href="<?php echo $contact_url; ?>">
                    the Elemental contact form
                </a>
                or by emailing
                <a href="mailto:<?php echo Configure::read('admin_email'); ?>"><?php
                    echo Configure::read('admin_email');
                ?></a>.
            </p>
        </td>
    </tr>
</table>