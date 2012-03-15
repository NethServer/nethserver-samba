<?php
echo $view->panel()
    ->insert($view->header()->setAttribute('template', 'Workgroup setup'))
    ->insert($view->textInput('workgroup'))
    ->insert($view->textInput('server'))
    ->insert($view->fieldset()->setAttribute('template', 'Server role')
        ->insert($view->fieldsetSwitch('role', 'WS')) 
        ->insert($view->fieldsetSwitch('role', 'PDC')
            ->insert($view->checkBox('RoamingProfiles', 'yes'))
        )
// ADS Server role is currently not implemented
       ->insert($view->fieldsetSwitch('role', 'ADS')
          ->insert($view->textInput('PDCName'))
          ->insert($view->textInput('PDCIP'))
          ->insert($view->textInput('Realm'))
       )
    )
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
