<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>
		    <?php echo $title_for_layout; ?>
	    </title>
	</head>
	<body>
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center">
                    <table cellspacing="0" cellpadding="0" border="0" width="600px">
                        <tr>
                            <td>
                                <h1 style="font-size: 30px; line-height: 40px;">
                                    <a href="http://elementalprotection.org/" style="color: black; text-decoration: none;">
                                        <img src="http://elementalprotection.org/img/star.svg" style="height: 44px; width: 41px;" />
                                        Elemental Sexual Assault Protection
                                    </a>
                                </h1>
                                <hr style="margin-top: 20px; margin-bottom: 20px; border: 0; border-top: 1px solid #eee;" />
                                <?php echo $this->fetch('content'); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
	</body>
</html>