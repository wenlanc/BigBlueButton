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

use Gibbon\Forms\Form;
use Gibbon\Domain\System\SettingGateway;

if (isActionAccessible($guid, $connection2, '/modules/BigBlueButton/bbb_settings.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__m('Settings'));
    
    $settingGateway = $container->get(SettingGateway::class);
 
    $form = Form::create('action', $session->get('absoluteURL').'/modules/'.$session->get('module').'/bbb_settingsProcess.php');

    $form->addHiddenValue('address', $session->get('address'));

    // BBB server settings
    $form->addRow()->addHeading('BigBlueButton', __('BigBlueButton'))->append(__('Gibbon can handle BigBlueButton using a API. These are external services, not affiliated with Gibbon.'));

    $setting = $settingGateway->getSettingByScope('BigBlueButton', 'enableBigBlueButton', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $form->toggleVisibilityByClass('bigBlueButton')->onSelect($setting['name'])->when('Y');

    $setting = $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonURL', true);
    $row = $form->addRow()->addClass('bigBlueButton');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value'])->required();

    $setting = $settingGateway->getSettingByScope('BigBlueButton', 'bigBlueButtonCredentials', true);
    $row = $form->addRow()->addClass('bigBlueButton');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value'])->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
    
}
