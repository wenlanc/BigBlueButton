<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2022, Father Vlasie

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basica variables
$name = "BigBlueButton";
$description = 'A module to support BigBlueButton Integration.';            // Short text description
$entryURL = "bbb_settings.php";
$type = "Additional";
$category = "Admin";
$version = "1.0.00";
$author = "Wenlan Cui";
$url = "";

//Settings
$gibbonSetting[0] = "INSERT INTO `gibbonSetting` (`gibbonSettingID` ,`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES (NULL , 'BigBlueButton', 'enableBigBlueButton', 'Enable BigBlueButton', 'Should BigBlueButton be enabled across the system?', 'N');";
$gibbonSetting[1] = "INSERT INTO `gibbonSetting` (`gibbonSettingID` ,`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES (NULL , 'BigBlueButton', 'bigBlueButtonURL', 'BigBlueButton Server URL', 'Server URL are provided by the BigBlueButton', '');";
$gibbonSetting[2] = "INSERT INTO `gibbonSetting` (`gibbonSettingID` ,`scope` ,`name` ,`nameDisplay` ,`description` ,`value`) VALUES (NULL , 'BigBlueButton', 'bigBlueButtonCredentials', 'BigBlueButton API Credentials', 'API credentials are provided by the BigBlueButton', '');";
$gibbonSetting[3] = "INSERT INTO `gibbonCustomField`(`context`,`name`,`active`,`description`,`type`,`options`,`required`,`hidden`,`heading`,`sequenceNumber`)
SELECT 'Lesson Plan','Video Chat','N','BigBlueButton session will be linked to this lesson.','checkboxes','Include','N','N','Basic Information',1
WHERE NOT EXISTS(SELECT 1 FROM `gibbonCustomField` WHERE `name` = 'Video Chat' );";

//Action rows
$actionRows[0]['name'] = "BigBlueButton Settings";
$actionRows[0]['precedence'] = "0";
$actionRows[0]['category'] = "Admin";
$actionRows[0]['description'] = "Allows privileged users to manage all bigbluebutton settings.";
$actionRows[0]['URLList'] = "bbb_settings.php";
$actionRows[0]['entryURL'] = "bbb_settings.php";
$actionRows[0]['entrySidebar'] = 'N';
$actionRows[0]['menuShow'] = 'Y';
$actionRows[0]['defaultPermissionAdmin'] = "Y";
$actionRows[0]['defaultPermissionTeacher'] = "Y";
$actionRows[0]['defaultPermissionStudent'] = "Y";
$actionRows[0]['defaultPermissionParent'] = "Y";
$actionRows[0]['defaultPermissionSupport'] = "Y";
$actionRows[0]['categoryPermissionStaff'] = "Y";
$actionRows[0]['categoryPermissionStudent'] = "Y";
$actionRows[0]['categoryPermissionParent'] = "Y";
$actionRows[0]['categoryPermissionOther'] = "Y";

$actionRows[1]['name'] = 'View live sessions';
$actionRows[1]['precedence'] = '0';
$actionRows[1]['category'] = 'Planner';
$actionRows[1]['description'] = 'Allows a privileged user to view bigbluebutton video chat with their learning area.';
$actionRows[1]['URLList'] = 'planner_view_full.php';
$actionRows[1]['entryURL'] = 'planner_view_full.php';
$actionRows[1]['entrySidebar'] = 'N';
$actionRows[1]['menuShow'] = 'N';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'Y';
$actionRows[1]['defaultPermissionStudent'] = 'Y';
$actionRows[1]['defaultPermissionParent'] = 'Y';
$actionRows[1]['defaultPermissionSupport'] = 'Y';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'Y';
$actionRows[1]['categoryPermissionParent'] = 'Y';
$actionRows[1]['categoryPermissionOther'] = 'Y';

$actionRows[2]['name'] = 'View recorded sessions';
$actionRows[2]['precedence'] = '0';
$actionRows[2]['category'] = 'Planner';
$actionRows[2]['description'] = 'Allows a privileged user to view recorded bigbluebutton video chat with their learning area.';
$actionRows[2]['URLList'] = 'planner_view_full.php';
$actionRows[2]['entryURL'] = 'planner_view_full.php';
$actionRows[2]['entrySidebar'] = 'N';
$actionRows[2]['menuShow'] = 'N';
$actionRows[2]['defaultPermissionAdmin'] = 'Y';
$actionRows[2]['defaultPermissionTeacher'] = 'Y';
$actionRows[2]['defaultPermissionStudent'] = 'Y';
$actionRows[2]['defaultPermissionParent'] = 'Y';
$actionRows[2]['defaultPermissionSupport'] = 'Y';
$actionRows[2]['categoryPermissionStaff'] = 'Y';
$actionRows[2]['categoryPermissionStudent'] = 'Y';
$actionRows[2]['categoryPermissionParent'] = 'Y';
$actionRows[2]['categoryPermissionOther'] = 'Y';
//Hooks
$array = array();
$array['sourceModuleName'] = 'BigBlueButton';
$array['sourceModuleAction'] = 'View recorded sessions';
$array['sourceModuleInclude'] = 'hook_lessonPlannerView.php';
$hooks[0] = "INSERT INTO `gibbonHook` (`gibbonHookID`, `name`, `type`, `options`, gibbonModuleID) VALUES (NULL, 'BigBlueButton', 'Lesson Planner', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";
