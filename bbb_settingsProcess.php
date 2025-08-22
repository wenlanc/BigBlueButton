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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\System\CustomFieldGateway;

include '../../gibbon.php';

$URL = $session->get('absoluteURL')."/index.php?q=/modules/".getModuleName($_POST['address'])."/bbb_settings.php";

if (isActionAccessible($guid, $connection2, '/modules/BigBlueButton/bbb_settings.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $partialFail = false;
    $settingGateway = $container->get(SettingGateway::class);

    $settingsToUpdate = [
        'BigBlueButton' => [
            'enableBigBlueButton' => 'required',
            'bigBlueButtonURL' => 'skip-hidden',
            'bigBlueButtonCredentials' => 'skip-hidden'
        ]
    ];

    // Validate required fields
    foreach ($settingsToUpdate as $scope => $settings) {
        foreach ($settings as $name => $property) {
            if ($property == 'required' && empty($_POST[$name])) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit;
            }
        }
    }

    // Update fields
    foreach ($settingsToUpdate as $scope => $settings) {
        foreach ($settings as $name => $property) {
            $value = $_POST[$name] ?? '';

            if ($property == 'skip-hidden' && !isset($_POST[$name])) continue;

            $updated = $settingGateway->updateSettingByScope($scope, $name, $value);
            $partialFail &= !$updated;
        }
    }

    // Active or diactive include video chat settings on lesson plan
    $customFieldGateway = $container->get(CustomFieldGateway::class);
    $customFields = $customFieldGateway->selectBy(['context' => 'Lesson Plan'])->fetchAll();
    
    foreach ($customFields as $field) {
        $updated = $customFieldGateway->update($field['gibbonCustomFieldID'], ['active' => $_POST['enableBigBlueButton']]);
    }

    // Update all the system settings that are stored in the session
    getSystemSettings($guid, $connection2);
    $session->set('pageLoads', null);

    $URL .= $partialFail
        ? '&return=warning1'
        : '&return=success0';
    header("Location: {$URL}");    
}
