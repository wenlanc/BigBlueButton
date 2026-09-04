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
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\System\CustomFieldGateway;

require __DIR__ . '/vendor/autoload.php';
use BigBlueButton\BigBlueButton;
use BigBlueButton\Enum\Role;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;

global $session, $container, $page;

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    //Access denied
    echo "<div class='error'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    $settingGateway = $container->get(SettingGateway::class);
    $enableBigBlueButton = $settingGateway->getSettingByScope('BigBlueButton', 'enableBigBlueButton', true);
    // Get gibbonActionIDs for 2 kind of BBB video chat
    $live_session_sql = 'SELECT gibbonActionID FROM gibbonAction WHERE name = "View live sessions"';
    $recorded_session_sql = 'SELECT gibbonActionID FROM gibbonAction WHERE name = "View recorded sessions"';
    $live_session_qry = $connection2->prepare($live_session_sql);
    $live_session_qry->execute();
    $recorded_session_qry = $connection2->prepare($recorded_session_sql);
    $recorded_session_qry->execute();
    $live_gibbonActionID = $live_session_qry->fetch()["gibbonActionID"] ?? 'NULL';
    $recorded_gibbonActionID = $recorded_session_qry->fetch()["gibbonActionID"] ?? 'NULL';
    // Get permission for person's role and video chat type
    $perm_person_live_sql = 'SELECT permissionID FROM gibbonPermission WHERE gibbonRoleID=' . $session->get('gibbonRoleIDCurrent') . ' AND gibbonActionID=' . $live_gibbonActionID;
    $perm_person_live_qry = $connection2->prepare($perm_person_live_sql);
    $perm_person_live_qry->execute();
    $perm_person_live = $perm_person_live_qry->rowCount();
    $perm_person_recorded_sql = 'SELECT permissionID FROM gibbonPermission WHERE gibbonRoleID=' . $session->get('gibbonRoleIDCurrent') . ' AND gibbonActionID=' . $recorded_gibbonActionID;
    $perm_person_recorded_qry = $connection2->prepare($perm_person_recorded_sql);
    $perm_person_recorded_qry->execute();
    $perm_person_recorded = $perm_person_recorded_qry->rowCount();
    if ($enableBigBlueButton['value'] == 'Y') {
        $customFieldGateway = $container->get(CustomFieldGateway::class);
        $customFields = $customFieldGateway->selectBy(['context' => 'Lesson Plan'])->fetchAll();  
        $key = array_search('Video Chat', array_column($customFields, 'name'));
    
        if ($key !== false && count($customFields) > $key && isset($customFields[$key]['gibbonCustomFieldID'])) {
            $plannerSettingFields = json_decode($values['fields'], true);
            if ($plannerSettingFields && isset($plannerSettingFields[$customFields[$key]['gibbonCustomFieldID']]) && $plannerSettingFields[$customFields[$key]['gibbonCustomFieldID']] == 'Include') {
                $bigBlueButtonURL = (string) $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonURL');
                $bigBlueButtonCredentials = (string) $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonCredentials');
                $meetingId = "planner".(int)$values['gibbonPlannerEntryID'];
                $duration = round((strtotime($values['timeEnd']) - strtotime($values['timeStart'])) / 60) + 25;
                $meeting_html = "";
                $meeting_window_height = 0;
                //checking planer status 
                if ((date('H:i:s', strtotime('10 minutes')) >= $values['timeStart']) and (date('H:i:s', strtotime('-10 minutes')) <= $values['timeEnd']) and $values['date'] == date('Y-m-d')) {
                    if ($perm_person_live > 0 && $hook['name'] == "View live sessions") {
                        // Init BigBlueButton API
                        try {
                            $bbb = new BigBlueButton($bigBlueButtonURL, $bigBlueButtonCredentials);
                            $getMeetingInfoParams = new GetMeetingInfoParameters($meetingId);
                            $response = $bbb->getMeetingInfo($getMeetingInfoParams);
                
                            if ($response->getReturnCode() == 'FAILED') {
                                // Create the meeting
                                $createParams = new CreateMeetingParameters($meetingId, $values['name'].' lesson');
                                $createParams = $createParams->setRecord(true)
                                                            ->setDuration($duration > 0 ? $duration : 120)
                                                            ->setAllowStartStopRecording(true)
                                                            ->setAutoStartRecording(true)
                                                            ->setLogoutUrl($session->get('absoluteURL') . '/modules/BigBlueButton/return.php');
                                $create_response = $bbb->createMeeting($createParams);
                                if ($create_response->getReturnCode() == 'FAILED') {
                                    $meeting_html = $create_response->getMessage();
                                }else{
                                    $meeting_window_height = 500;
                                }
                            }else{
                                $meeting_window_height = 500;
                            }
                            
                            if ($meeting_window_height > 0) {
                                $joinRole = $session->get('gibbonRoleIDCurrentCategory') == 'Staff' ? Role::MODERATOR : Role::VIEWER;
                                $joinParams = new JoinMeetingParameters($meetingId, $session->get('preferredName').' '.$session->get('surname'), $joinRole);
                                $bbbMeetingUrl = $bbb->getJoinMeetingURL($joinParams);
                                $meeting_html = "<IFRAME src='".htmlspecialchars($bbbMeetingUrl)."' allow='geolocation *; microphone *; camera *; display-capture *;' allowFullScreen='true' webkitallowfullscreen='true' mozallowfullscreen='true' sandbox='allow-same-origin allow-scripts allow-modals allow-forms allow-top-navigation' style='width:100%;height:100%;border:0' scrolling='no'></IFRAME>";
                            }
                        } catch (\Exception $e) {
                            $meeting_html = "BBB server is not working. Please contact the administrator.";
                        }
                    } else {
                        return;
                    }
                }else if ((($values['date']) == date('Y-m-d') and (date('H:i:s', strtotime('-10 minutes')) > $values['timeEnd'])) or ($values['date']) < date('Y-m-d')) {
                    if ($perm_person_recorded > 0 && $hook['name'] == "View recorded sessions") {
                        $recordingParams = new GetRecordingsParameters();
                        $recordingParams->setMeetingId($meetingId);
                        try {
                            $bbb = new BigBlueButton($bigBlueButtonURL, $bigBlueButtonCredentials);
                            $response = $bbb->getRecordings($recordingParams);
                            if ($response->getReturnCode() == 'SUCCESS') {
                                $records = $response->getRecords();
                                if($records){
                                    // Group playback formats by type (using actual format names from API)
                                    $formats = [];
                                    $presentationOnlyMode = false;
                                    
                                    foreach ($records as $key => $record){
                                        foreach ($record->getFormats() as $playback) {
                                            $format = $playback->getType();
                                            $playbackUrl = $playback->getUrl();

                                            if ($format && $playbackUrl) {
                                                $format = strtolower(trim($format));
                                                $formats[$format] = $playbackUrl;
                                            }
                                        }
                                    }
                                    
                                    // Check if teacher wants presentation only
                                    $plannerSettingFields = json_decode($values['fields'], true);
                                    if ($plannerSettingFields) {
                                        foreach ($customFields as $field) {
                                            if ($field['name'] === 'Presentation Only' && isset($plannerSettingFields[$field['gibbonCustomFieldID']])) {
                                                if ($plannerSettingFields[$field['gibbonCustomFieldID']] === 'Y' || $plannerSettingFields[$field['gibbonCustomFieldID']] === 'Yes') {
                                                    $presentationOnlyMode = true;
                                                    // Keep only presentation format
                                                    foreach ($formats as $formatName => $formatUrl) {
                                                        if (strpos($formatName, 'presentation') === false) {
                                                            unset($formats[$formatName]);
                                                        }
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Reorder formats so presentation is first
                                    $presentationKey = null;
                                    foreach ($formats as $formatName => $formatUrl) {
                                        if (strpos($formatName, 'presentation') !== false) {
                                            $presentationKey = $formatName;
                                            break;
                                        }
                                    }
                                    if ($presentationKey !== null) {
                                        $presentationFormatUrl = $formats[$presentationKey];
                                        unset($formats[$presentationKey]);
                                        $formats = array($presentationKey => $presentationFormatUrl) + $formats;
                                    }

                                    // Display available formats as tabs
                                    if (!empty($formats)) {
                                        $activeFormat = key($formats); // Use first format as default
                                        
                                        // Create tab buttons (only show tabs if more than one format)
                                        if (count($formats) > 1) {
                                            $meeting_html .= "<div style='margin-bottom: 10px; border-bottom: 2px solid #ddd;'>";
                                            
                                            foreach ($formats as $formatName => $formatUrl) {
                                                $isActive = ($formatName === $activeFormat);
                                                $displayName = ucfirst(str_replace('-', ' ', $formatName));
                                                $buttonId = 'btn-' . preg_replace('/[^a-z0-9]/', '-', $formatName);
                                                
                                                $meeting_html .= "<button onclick=\"";
                                                // Hide all divs
                                                foreach ($formats as $fname => $furl) {
                                                    $divId = 'bbb-format-' . preg_replace('/[^a-z0-9]/', '-', $fname);
                                                    $meeting_html .= "document.getElementById('" . $divId . "').style.display='none'; ";
                                                }
                                                // Show this one
                                                $meeting_html .= "document.getElementById('bbb-format-" . preg_replace('/[^a-z0-9]/', '-', $formatName) . "').style.display='block'; ";
                                                // Reset all button styles
                                                foreach ($formats as $fname => $furl) {
                                                    $bid = 'btn-' . preg_replace('/[^a-z0-9]/', '-', $fname);
                                                    $meeting_html .= "document.getElementById('" . $bid . "').style.borderBottom='1px solid #ddd'; ";
                                                }
                                                // Activate this button
                                                $meeting_html .= "this.style.borderBottom='3px solid #0066cc';";
                                                $meeting_html .= "\" id='" . $buttonId . "' style='padding: 10px 20px; background: white; border: 1px solid #ddd; cursor: pointer; font-weight: bold; border-bottom: " . ($isActive ? "3px solid #0066cc" : "1px solid #ddd") . ";'>" . __($displayName) . "</button>";
                                            }
                                            
                                            $meeting_html .= "</div>";
                                        }
                                        
                                        // Display format containers
                                        foreach ($formats as $formatName => $formatUrl) {
                                            $isActive = ($formatName === $activeFormat);
                                            $divId = 'bbb-format-' . preg_replace('/[^a-z0-9]/', '-', $formatName);
                                            $meeting_html .= "<div id='" . $divId . "' style='width:100%; display:" . ($isActive ? "block" : "none") . ";'>";
                                            $meeting_html .= "<IFRAME src='" . htmlspecialchars($formatUrl) . "' allow='geolocation *; microphone *; camera *; display-capture *;' allowFullScreen='true' webkitallowfullscreen='true' mozallowfullscreen='true' sandbox='allow-same-origin allow-scripts allow-modals allow-forms' style='width:100%;height:500px;border:0;overflow:auto;' scrolling='yes'></IFRAME>";
                                            $meeting_html .= "</div>";
                                        }
                                        
                                        $meeting_window_height = 550;
                                    } else {
                                        $meeting_html = __('No compatible recording formats found.');
                                    }
                                } else {
                                    $meeting_html = $response->getMessage();
                                }
                            } else {
                                $meeting_html = $response->getMessage();
                            }
                        } catch (\Exception $e) {
                            $meeting_html = "BBB server is not working. Please contact the administrator.";
                        }
                    } else {
                        return;
                    }
                } else {
                    if ($hook['name'] == "View live sessions") {
                        $meeting_html = "The meeting hasn't started yet."; 
                    } else { return; }
                }

                echo "<h2>".__('Video Chat').'</h2>';
                echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%;'>";
                echo '<tr>';
                echo "<td style='text-align: justify; padding-top: 5px; width: 100%; vertical-align: top; max-width: 752px!important; height: " . $meeting_window_height . "px;' colspan=3>";
                echo $meeting_html;
                echo '</td>';
                echo '</tr>';
                echo '</table>';
            }
        }        
    }
}
