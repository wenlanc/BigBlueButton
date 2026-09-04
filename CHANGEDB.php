<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = [];
$count = 0;

//v1.0.01 - Separate the BBB video chat permission
//v1.0.02 - Fixed capitalisation in SQL calls
//v1.0.03 - Fixed bugs on hook of lesson planner
//v1.0.04 - Fixed bugs on duplicated meetings for multi hooks of lesson planner
$sql[$count][0] = "1.0.04";
$sql[$count][1] = "";

//v1.5 - Presentation Only custom field (install-only until this file was updated)
++$count;
$sql[$count][0] = "1.5";
$sql[$count][1] = "
INSERT INTO `gibbonCustomField`(`context`,`name`,`active`,`description`,`type`,`options`,`required`,`hidden`,`heading`,`sequenceNumber`)
SELECT 'Lesson Plan','Presentation Only','N','Only show the presentation recording without transcripts or summaries.','checkboxes','Yes','N','N','Basic Information',2
WHERE NOT EXISTS(SELECT 1 FROM `gibbonCustomField` WHERE `name` = 'Presentation Only');end
";

//v1.5.1
++$count;
$sql[$count][0] = "1.5.1";
$sql[$count][1] = "";

//v1.5.2 - Apply Presentation Only for sites that already upgraded past 1.5 without CHANGEDB SQL
++$count;
$sql[$count][0] = "1.5.2";
$sql[$count][1] = "
INSERT INTO `gibbonCustomField`(`context`,`name`,`active`,`description`,`type`,`options`,`required`,`hidden`,`heading`,`sequenceNumber`)
SELECT 'Lesson Plan','Presentation Only','N','Only show the presentation recording without transcripts or summaries.','checkboxes','Yes','N','N','Basic Information',2
WHERE NOT EXISTS(SELECT 1 FROM `gibbonCustomField` WHERE `name` = 'Presentation Only');end
";

//v1.5.3 - Show the field when BBB is already enabled (1.5.2 inserted it inactive)
++$count;
$sql[$count][0] = "1.5.3";
$sql[$count][1] = "
INSERT INTO `gibbonCustomField`(`context`,`name`,`active`,`description`,`type`,`options`,`required`,`hidden`,`heading`,`sequenceNumber`)
SELECT 'Lesson Plan','Presentation Only',IFNULL((SELECT `value` FROM `gibbonSetting` WHERE `scope`='BigBlueButton' AND `name`='enableBigBlueButton' LIMIT 1),'N'),'Only show the presentation recording without transcripts or summaries.','checkboxes','Yes','N','N','Basic Information',2
WHERE NOT EXISTS(SELECT 1 FROM `gibbonCustomField` WHERE `name` = 'Presentation Only');end
UPDATE `gibbonCustomField` SET `active`=IFNULL((SELECT `value` FROM `gibbonSetting` WHERE `scope`='BigBlueButton' AND `name`='enableBigBlueButton' LIMIT 1),'N') WHERE `context`='Lesson Plan' AND `name`='Presentation Only';end
";

//v1.5.4 - bigbluebutton-api-php 3.x (no schema change)
++$count;
$sql[$count][0] = "1.5.4";
$sql[$count][1] = "";
