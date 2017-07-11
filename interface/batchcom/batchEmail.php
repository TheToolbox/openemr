<?php
/**
 * Batch Email processor, included from batchcom
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  cfapress
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2008 cfapress
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// create file header.
// menu for fields could be added in the future

?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
</head>
<body class="body_top">
<span class="title"><?php echo xlt('Batch Communication Tool'); ?></span>
<br><br>
<span class="title" ><?php echo xlt('Email Notification Report'); ?></span>
<br><br>


<?php
$email_sender=$_POST['email_sender'];
$sent_by=$_SESSION["authId"];
$msg_type=xlt('Email from Batchcom');

while ($row=sqlFetchArray($res)) {
    // prepare text for ***NAME*** tag
    $pt_name=$row['title'].' '.$row['fname'].' '.$row['lname'];
    $pt_email=$row['email'];

    $email_subject = $_POST['email_subject'];
    $email_body = $_POST['email_body'];
    $email_subject = preg_replace('/\*{3}NAME\*{3}/', $pt_name, $email_subject);
    $email_body = preg_replace('/\*{3}NAME\*{3}/', $pt_name, $email_body);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "To: $pt_name<".$pt_email.">\r\n";
    $headers .= "From: <".$email_sender.">\r\n";
    $headers .= "Reply-to: <".$email_sender.">\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP mailer\r\n";
    if (mail($pt_email, $email_subject, $email_body, $headers)) {
        echo "<br>" . xlt('Email sent to') . ": " . text($pt_name) . " , " . text($pt_email) . "<br>";
    } else {
        $m_error = true;
        $m_error_count++;
    }
}

if ($m_error) {
    echo xlt('Could not send email due to a server problem. ') . '<br>' . $m_error_count . xlt(' emails not sent');
}

?> 
