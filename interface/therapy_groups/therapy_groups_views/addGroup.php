<?php
/**
 * interface/therapy_groups/therapy_groups_views/addGroup.php contains view for adding group.
 *
 * This is the view for the screen inwhich we can add a therapy group.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>

<?php $edit = acl_check("groups", "gadd", false, 'write');?>
<?php $view = acl_check("groups", "gadd", false, 'view');?>

<?php require 'header.php'; ?>
<?php if ($view || $edit) :?>
<main id="add-group">
    <div class="container container-group">
        <form method="post" name="addGroup">
            <input type="hidden" name="group_id" value="<?php echo isset($groupData['group_id']) ? attr($groupData['group_id']) : '';?>">
            <div class="row group-row">
                <div class="col-md-10">

                    <span class="title"><?php echo xlt('Add group') ?> </span>

                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-7 col-sm-6">
                    <div class="row">
                        <div class="col-md-3 col-sm-5">
                            <span class="bold"><?php echo xlt('Group\'s name') ?>:</span>
                        </div>
                        <div class="col-md-9 col-sm-7">
                            <input type="text" name="group_name" class="full-width" value="<?php echo attr($groupData['group_name']);?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-5">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Starting date'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" name="group_start_date" class="full-width datepicker"  value="<?php echo attr(oeFormatShortDate($groupData['group_start_date']));?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Type of group'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="1" name="group_type" <?php echo is_null($groupData['group_type']) || $groupData['group_type'] == '1' ? 'checked' : '';?>><?php echo xlt('Closed'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="2" name="group_type"  <?php echo $groupData['group_type'] == '2' ? 'checked' : '';?>><?php echo xlt('Open'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="3" name="group_type"  <?php echo  $groupData['group_type'] == '3' ? 'checked' : '';?>><?php echo xlt('Train'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <span class="bold"><?php echo xlt('Obligatory participation'); ?>:</span>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="1" name="group_participation" <?php echo is_null($groupData['group_participation']) || $groupData['group_participation'] == '1' ? 'checked' : '';?>><?php echo xlt('Mandatory'); ?>
                    </label>
                    <label class="radio-inline radio-pos">
                        <input type="radio" value="2" name="group_participation" <?php echo $groupData['group_participation'] == '2' ? 'checked' : '';?>><?php echo xlt('Optional'); ?>
                    </label>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 attach-input">
                            <span class="bold"><?php echo xlt('Status'); ?>:</span>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <select name="group_status" class="full-width"  value="<?php echo attr($groupData['group_status']);?>">
                                <?php foreach ($statuses as $key => $status) : ?>
                                    <option value="<?php echo attr($key);?>" <?php echo $key == $groupData['group_status'] ? 'selected' : ''; ?>><?php echo xlt($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 col-sm-5">
                            <span class="bold"><?php echo xlt('Main counselors'); ?>:</span>
                        </div>
                        <div class="col-md-8 col-sm-7">
                            <select name="counselors[]" multiple class="full-width">
                                <?php foreach ($users as $user) : ?>
                                    <option value="<?php echo attr($user['id']);?>" <?php echo !is_null($groupData['counselors']) && in_array($user['id'], $groupData['counselors']) ? 'selected' : '';?>><?php echo text($user['fname'] . ' ' . $user['lname']);?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-3 col-sm-5">
                            <span class="bold"><?php echo xlt('Notes'); ?>:</span>
                        </div>
                        <div class="col-md-9 col-sm-7">
                            <textarea name="group_notes" class="full-width" style="height: 70px"><?php echo text($groupData['group_notes']);?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row group-row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 col-sm-5">
                            <span class="bold"><?php echo xlt('Guest counselors'); ?>:</span>
                        </div>
                        <div class="col-md-8 col-sm-7">
                           <input type="text" name="group_guest_counselors" class="full-width"  value="<?php echo attr($groupData['group_guest_counselors']);?>">
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($edit) :?>
            <div class="row group-row">
                <div class="col-md-3">
                    <?php if ($edit) :?>
                    <button type="submit" name="save" value="save" <?php echo $savingStatus == 'success' ? 'disabled' : '';?>><?php echo xlt('Add group');?></button>
                    <?php endif;?>
                </div>
                <div class="col-md-9 col-sm 12">
                    <?php if ($edit) :?>
                    <?php if ($savingStatus == 'exist') : ?>
                        <div id="exist-group"><h4 class="group-error-msg"><?php echo text($message) ?></h4><button id="cancel-save"><?php echo xlt('cancel') ?></button><button type="submit" value="save_anyway" name="save"><?php echo xlt('Creating anyway') ?></button></div>
                    <?php endif ?>
                    <?php if ($savingStatus == 'success') : ?>
                        <h4 class="group-success-msg"><?php echo text($message) ?></h4>
                    <?php endif ?>
                    <?php if ($savingStatus == 'failed') : ?>
                        <h4 class="group-serror-msg"><?php echo text($message) ?></h4>
                    <?php endif ?>
                    <?php endif;?>
                </div>
            <div>
                <?php endif;?>
        </form>
    </div>
</main>
<script>
    $(document).ready(function(){

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
    $('#cancel-save').on('click', function(e){
        e.preventDefault();
        $('#exist-group').hide();
    });
</script>
<?php    $use_validate_js = 1;?>
<?php validateUsingPageRules($_SERVER['PHP_SELF'] . '?method=addGroup');?>
<?php else :?>

    <div class="container">

    <div class="row alert alert-info">
    <h1 class="col-md-12"><i class="col-md-3 glyphicon glyphicon-alert"></i><span class="col-md-6"><?php echo xlt("access not allowed");?></span></h1>
    </div>
    </div>



<?php endif;?>

<?php require 'footer.php'; ?>
