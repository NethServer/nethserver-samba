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
// ADS Server role is currently not implemented
//       ->insert($view->fieldsetSwitch('role', 'ADS', $view::STATE_DISABLED)
//          ->insert($view->textInput('PDCName'))
//          ->insert($view->textInput('PDCIP'))
//          ->insert($view->textInput('Realm'))
//       )
    )
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
