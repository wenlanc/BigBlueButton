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
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;

global $session, $container, $page;

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {   

    $settingGateway = $container->get(SettingGateway::class);
    $enableBigBlueButton = $settingGateway->getSettingByScope('BigBlueButton', 'enableBigBlueButton', true);

    if ($enableBigBlueButton['value'] == 'Y') {

        $customFieldGateway = $container->get(CustomFieldGateway::class);
        $customFields = $customFieldGateway->selectBy(['context' => 'Lesson Plan'])->fetchAll();  
        $key = array_search('Video Chat', array_column($customFields, 'name'));
    
        if (count($customFields) > $key) {
            $plannerSettingFields = json_decode($values['fields'], true);
            
            if ($plannerSettingFields[$customFields[$key]['gibbonCustomFieldID']] == 'Include') {
                $bigBlueButtonURL = $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonURL', '');
                $bigBlueButtonCredentials = $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonCredentials', '');
                putenv('BBB_SERVER_BASE_URL='. $bigBlueButtonURL);
                putenv('BBB_SECRET='. $bigBlueButtonCredentials);
                $meetingId = "planner".(int)$values['gibbonPlannerEntryID'];
                $duration = round((strtotime($values['timeEnd']) - strtotime($values['timeStart'])) / 60) + 25;
                $meeting_html = "";
                $meeting_window_height = 0;
                //checking planer status 
                if ((date('H:i:s', strtotime('10 minutes')) >= $values['timeStart']) and (date('H:i:s', strtotime('-10 minutes')) <= $values['timeEnd']) and $values['date'] == date('Y-m-d')) {
                    // Init BigBlueButton API
                    try {
                        $bbb = new BigBlueButton();
                        $getMeetingInfoParams = new GetMeetingInfoParameters($meetingId, 'moderator_password');
                        $response = $bbb->getMeetingInfo($getMeetingInfoParams);
            
                        if ($response->getReturnCode() == 'FAILED') {
                            // Create the meeting
                            $createParams = new CreateMeetingParameters($meetingId, $values['name'].' lesson');
                            $createParams = $createParams->setModeratorPassword('moderator_password')
                                                        ->setAttendeePassword('attendee_password')
                                                        ->setRecord(true)
                                                        ->setDuration($duration > 0 ? $duration : 120)
                                                        ->setAllowStartStopRecording(true)
                                                        ->setAutoStartRecording(true);
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
                            $joinParams = new JoinMeetingParameters($meetingId, $session->get('preferredName').' '.$session->get('surname'), $session->get('gibbonRoleIDCurrentCategory') == 'Staff' ? 'moderator_password':'attendee_password');
                            $joinParams->setRedirect(false);
                            $joinResponse = $bbb->joinMeeting($joinParams);
                            $bbbMeetingUrl = $joinResponse->getUrl();
                            $meeting_html = "<IFRAME src='".$bbbMeetingUrl."' allow='geolocation *; microphone *; camera *; display-capture *;' allowFullScreen='true' webkitallowfullscreen='true' mozallowfullscreen='true' sandbox='allow-same-origin allow-scripts allow-modals allow-forms allow-top-navigation' style='width:100%;height:100%;border:0' scrolling='no'></IFRAME>";
                        }
                    } catch (\Exception $e) {
                        $meeting_html = "BBB server is not working. Please contact with administrator.";
                    }
                }else if ((($values['date']) == date('Y-m-d') and (date('H:i:s', strtotime('-10 minutes')) > $values['timeEnd'])) or ($values['date']) < date('Y-m-d')) {
                    $recordingParams = new GetRecordingsParameters();
                    $recordingParams->setMeetingId($meetingId);
                    try {
                        $bbb = new BigBlueButton();
                        $response = $bbb->getRecordings($recordingParams);
                        if ($response->getReturnCode() == 'SUCCESS') {
                            $records = $response->getRecords();
                            if($records){
                                foreach ($records as $key => $record){
                                    // process all recording
                                    $meeting_html .= "<IFRAME src='".$record->getPlaybackUrl()."' allow='geolocation *; microphone *; camera *; display-capture *;' allowFullScreen='true' webkitallowfullscreen='true' mozallowfullscreen='true' sandbox='allow-same-origin allow-scripts allow-modals allow-forms' style='width:100%;height:100%;border:0' scrolling='no'></IFRAME>";
                                }
                                $meeting_window_height = 500;
                            } else {
                                $meeting_html = $response->getMessage();
                            }
                        } else {
                            $meeting_html = $response->getMessage();
                        }
                    } catch (\Exception $e) {
                        $meeting_html = "BBB server is not working. Please contact with administrator.";
                    }
                }else {
                    $meeting_html = "The meeting hasn't started yet.";
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