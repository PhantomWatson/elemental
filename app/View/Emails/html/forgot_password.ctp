<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td colspan="2">
            <p style="margin: 16px 0;">
                <strong>
                    <?php echo h($user['User']['name']); ?>,
                </strong>
            </p>

            <p style="margin: 16px 0;">
                Someone (presumably you) has requested that your password for
                <a href="http://elementalprotection.org">ElementalProtection.org</a>
                be reset so you can log in again. Please visit the following URL, where you will
                be prompted to enter in a new password to overwrite your old one.
            </p>

            <p style="margin: 16px 0;">
                <a href="<?php echo $reset_url; ?>" style="background-color: #337ab7; border-color: #2e6da4; border-radius: 4px; color: #fff; display: inline-block; padding: 6px 12px; text-decoration: none; vertical-align: middle;">
                    Reset Password
                </a>
            </p>
        </td>
    </tr>
</table>