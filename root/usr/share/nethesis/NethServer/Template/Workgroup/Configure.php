<?php

//$winregistryLink = $view->button('WinregistryPatches', $view::BUTTON_LINK);
$winregistryLink = $view->literal(sprintf('<a href="%s">%s</a>', $view['WinregistryPatches'], $T('WinregistryPatches_label')));

echo $view->panel()
    ->insert($view->header()->setAttribute('template', 'Workgroup setup'))
    ->insert($view->textInput('workgroup'))
    ->insert($view->fieldset()->setAttribute('template', 'Server role')
        ->insert($view->fieldsetSwitch('role', 'WS'))
        ->insert($view->fieldsetSwitch('role', 'PDC')
            ->insert($view->checkBox('RoamingProfiles', 'yes')->setAttribute('uncheckedValue', 'no'))
            ->insert($winregistryLink)
        )
        ->insert($view->fieldsetSwitch('role', 'ADS')            
            ->insert($view->textInput('AdsRealm')->setAttribute('placeholder', $view['defaultRealm']))
            ->insert($view->textInput('AdsController'))
        )
    )
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
