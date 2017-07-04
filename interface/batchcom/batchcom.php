<?php
/**
 * Batchcom script.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @link    http://www.open-emr.org
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @todo    KNOWN SQL INJECTION VECTOR
 */

//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("../../library/acl.inc");
include_once("batchcom.inc.php");

use OpenEMR\Core\Header;

if (!acl_check('admin', 'batchcom')) {
    echo "<html>\n<body>\n";
    echo "<h1>".xl('You are not authorized for this.','','','</h1>')."\n";
    echo "</body>\n</html>";
    exit();
}

// menu arrays (done this way so it's easier to validate input on validate selections)
$process_choices = array (xl('Download CSV File'),xl('Send Emails'),xl('Phone call list'));
$gender_choices = array (xl('Any'),xl('Male'),xl('Female'));
$hipaa_choices = array (xl('No'),xl('Yes'));
$sort_by_choices = array (xl('Zip Code')=>'patient_data.postal_code',xl('Last Name')=>'patient_data.lname',xl('Appointment Date')=>'last_ap' );

// process form
if ($_POST['form_action']=='process') {
    //validation uses the functions in batchcom.inc.php
    //validate dates
    if (!check_date_format($_POST['app_s'])) $form_err.=xl('Date format for "appointment start" is not valid','','<br>');
    if (!check_date_format($_POST['app_e'])) $form_err.=xl('Date format for "appointment end" is not valid','','<br>');
    if (!check_date_format($_POST['seen_since'])) $form_err.=xl('Date format for "seen since" is not valid','','<br>');
    if (!check_date_format($_POST['seen_before'])) $form_err.=xl('Date format for "seen before" is not valid','','<br>');
    // validate numbers
    if (!check_age($_POST['age_from'])) $form_err.=xl('Age format for "age from" is not valid','','<br>');
    if (!check_age($_POST['age_upto'])) $form_err.=xl('Age format for "age up to" is not valid','','<br>');
    // validate selections
    if (!check_select($_POST['gender'],$gender_choices)) $form_err.=xl('Error in "Gender" selection','','<br>');
    if (!check_select($_POST['process_type'],$process_choices)) $form_err.=xl('Error in "Process" selection','','<br>');
    if (!check_select($_POST['hipaa_choice'],$hipaa_choices)) $form_err.=xl('Error in "HIPAA" selection','','<br>');
    if (!check_select($_POST['sort_by'],$sort_by_choices)) $form_err.=xl('Error in "Sort By" selection','','<br>');
    // validates and or
    if (!check_and_or ($_POST['and_or_gender'])) $form_err.=xl('Error in and/or option','','<br>');
    if (!check_and_or ($_POST['and_or_app_within'])) $form_err.=xl('Error in and/or option','','<br>');
    if (!check_and_or ($_POST['and_or_seen_within'])) $form_err.=xl('Error in and/or option','','<br>');

    //process sql
    if (!$form_err) {
         $sql="select patient_data.*, cal_events.pc_eventDate as next_appt,cal_events.pc_startTime as appt_start_time,cal_date.last_appt,forms.last_visit from patient_data left outer join openemr_postcalendar_events as cal_events on patient_data.pid=cal_events.pc_pid and curdate() < cal_events.pc_eventDate left outer join (select pc_pid,max(pc_eventDate) as last_appt from openemr_postcalendar_events where curdate() >= pc_eventDate group by pc_pid ) as cal_date on cal_date.pc_pid=patient_data.pid left outer join (select pid,max(date) as last_visit from forms where curdate() >= date group by pid) as forms on forms.pid=patient_data.pid where 1=1";
        //appointment dates
        if ($_POST['app_s']!=0 and $_POST['app_s']!='') { $sql .= " and cal_events.pc_eventDate >= '".$_POST['app_s']."'"; }
        if ($_POST['app_e']!=0 and $_POST['app_e']!='') { $sql .= " and cal_events.pc_endDate <= '".$_POST['app_e']."'"; }
        // encounter dates
        if ($_POST['seen_since']!=0 and $_POST['seen_since']!='') { $sql .= " and forms.date >= '".$_POST['seen_since']."' " ; }
        if ($_POST['seen_before']!=0 and $_POST['seen_before']!='') { $sql .= " and forms.date <= '".$_POST['seen_before']."' " ; }
        // age
        if ($_POST['age_from']!=0 and $_POST['age_from']!='') { $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 >= '".$_POST['age_from']."' "; }
        if ($_POST['age_upto']!=0 and $_POST['age_upto']!='') { $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 <= '".$_POST['age_upto']."' "; }
        // gender
        if ($_POST['gender']!='Any') { $sql .= " and patient_data.sex='".$_POST['gender']."' "; }//INJECTION VECTOR HERE
        // hipaa overwrite
        if ($_POST['hipaa_choice'] != $hipaa_choices[0]) { $sql .= " and patient_data.hipaa_mail='YES' "; }

        switch ($_POST['process_type']):
            case $choices[1]: // Email
                $sql.=" and patient_data.email IS NOT NULL ";
            break;
        endswitch;

        // sort by
        $sql.=' ORDER BY '.$_POST['sort_by'];//INJECTION VECTOR
        // send query for results.
        //echo $sql;exit();
        $res = sqlStatement($sql);

        if (sqlNumRows($res)==0){
            $form_err = xl('No results found, please try again.');
        } else {
            switch ($_POST['process_type']):
                case $process_choices[0]: // CSV File
                    generate_csv($res);
                    exit();
                case $process_choices[1]: // Email
                    require_once('batchEmail.php');
                    exit();
                case $process_choices[2]: // Phone list
                    require_once('batchPhoneList.php');
                    exit();
            endswitch;
        }

    }
}

?>
<html>
<head>
<title><?php echo xlt('BatchCom'); ?></title>
<?php Header::setupHeader(['datetime-picker']); ?>
<style>
.datepicker {
    width: 100%;
}
</style>
</head>

<body class="body_top">
<!-- larry's sms/email notification -->
<?php include_once("batch_navigation.php");?>
<!--- end of larry's insert -->
<main class="container">
    <header class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
            <h1><?php xl('Batch Communication Tool','e')?></h1>
        </div>    
    </header>
    <?php if ($form_err) { echo "<div class=\"alert alert-danger\">".xl("The following errors occurred").": $form_err</div>"; } ?>
    <form name="select_form" method="post" action="">
        <div class="row">
            <div class="col-md-3 well">
                <label for="process_type"><?php echo xl("Process","e").":"; ?></label>
                <select name="process_type">
                    <?php foreach ($process_choices as $choice) { echo "<option>$choice</option>"; } ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="hipaa_choice"><?php echo xl("Override HIPAA choice").":"; ?></label>
                <select name="hipaa_choice">
                    <?php foreach ($hipaa_choices as $choice) { echo "<option>$choice</option>"; } ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="sort_by"><?php echo xl("Sort by","e"); ?></label>
                <select name="sort_by">
                    <?php foreach ($sort_by_choices as $choice => $sorting_code) { echo "<option value=\"$sorting_code\">$choice</option>"; } ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="age_from"><?php echo xl("Age Range","e").":"; ?></label>
                <input name="age_from" size="2" type="num" placeholder="<?php echo xl("any"); ?>"> - <input name="age_upto" size="2" type="num" placeholder="<?php echo xl("any"); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 well">
                <select name="and_or_gender">
                    <option value="AND"><?php xl('And','e')?></option>
                    <option value="OR"><?php xl('Or','e')?></option>
                </select>
                <label for="gender"><?php xl('Gender','e')?>:</label>
                <select name="gender">
                    <?php foreach ($gender_choices as $choice) { echo "<option>$choice</option>"; } ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <select name="and_or_app_within">
                    <option value="AND"><?php xl('And','e')?></option>
                    <option value="OR"><?php xl('Or','e')?></option>
                    <option value="AND NOT"><?php xl('And not','e')?></option>
                    <option value="OR NOT"><?php xl('Or not','e')?></option>
                </select>
                <label for="app_s"><?php xl('Appointment within','e')?>:</label>
                    <input type="text" class="datepicker" name="app_s" placeholder="any date">
                    <div class="text-center"><?php xl('to','e'); ?></div>
                    <input type="text" class="datepicker" name="app_e" placeholder="any date">
            </div>
            <!-- later gator    <br>Insurance: <SELECT multiple NAME="insurance" Rows="10" cols="20"></SELECT> -->
            <div class="col-md-3 well">
                <select name="and_or_seen_within">
                    <option value="AND"><?php xl('And','e')?></option>
                    <option value="OR"><?php xl('Or','e')?></option>
                    <option value="AND NOT"><?php xl('And not','e')?></option>
                    <option value="OR NOT"><?php xl('Or not','e')?></option>
                </select>
                <label for="app_s"><?php xl('Seen within','e')?>:</label>
                    <input type="text" class="datepicker" name="seen_since" placeholder="any date">
                    <div class="text-center"><?php xl('to','e'); ?></div>
                    <input type="text" class="datepicker" name="seen_before" placeholder="any date">
            </div>
        </div>
        <div class="email row">
            <div class="col-md-6 col-md-offset-3 well">
                <div class="col-md-6">
                    <label for="email_sender"><?php xl('Email Sender','e'); ?>:</label>
                    <input type="text" name="email_sender" placeholder="your@email.email">
                </div>
                
                <div class="col-md-6">
                    <label for="email_subject"><?php xl('Email Subject','e'); ?>:</label>
                    <input type="text" name="email_subject" placeholder="From your clinic">
                </div>
                <div class="col-md-12">
                    <label for="email_subject"><?php echo xlt('Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***{{Do Not translate the ***NAME*** elements of this constant.}}'); ?>:</label>
                </div>
                <div class="col-md-12">
                    <textarea name="email_body" id="" cols="40" rows="8"></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
                <input type="hidden" name="form_action" value="process">
                <input type="submit" name="submit" class="btn btn-primary" value="<?php xl("Process (can take some time)",'e'); ?>">
            </div>
        </div>
    </form>
</main>
</body>

<script>
    (function() {
        var email = document.querySelector('.email');
        var process = document.querySelector('select[name="process_type"]');
        function hideEmail() {
            if (process.value !== '<?php echo $process_choices[1]; ?>') { email.style.display = 'none'; } else { email.style.display = ''; }
        }
        process.addEventListener('change', hideEmail);
        hideEmail();
        $('.datepicker').datetimepicker({
            timepicker: false,
            format: 'Y-m-d'
        });
    })();
</script>
</html>