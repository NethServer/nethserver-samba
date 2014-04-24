<?php
/* @var $view Nethgui\Renderer\Xhtml */

echo $view->header()->setAttribute('template', $T('Configure_header'));

//$winregistryLink = $view->button('WinregistryPatches', $view::BUTTON_LINK);
$winregistryLink = $view->literal(sprintf('<div class="labeled-control"><a href="%s">%s</a></div>', $view['WinregistryPatches'], $T('WinregistryPatches_label')));

echo $view->panel()        
    
        ->insert($view->radioButton('ServerRole', 'WS'))
        ->insert($view->fieldsetSwitch('ServerRole', 'PDC', $view::FIELDSET_EXPANDABLE)
            ->insert($view->textInput('PdcDomain')->setAttribute('placeholder', $view['defaultDomain']))
            ->insert($view->checkBox('RoamingProfiles', 'yes')->setAttribute('uncheckedValue', 'no'))
            ->insert($winregistryLink)
        )
        ->insert($view->fieldsetSwitch('ServerRole', 'ADS', $view::FIELDSET_EXPANDABLE)
            ->insert($view->textInput('AdsRealm')->setAttribute('placeholder', $view['defaultRealm']))
            ->insert($view->textInput('AdsDomain')->setAttribute('placeholder', $view['defaultDomain']))
     
            // DISABLED: this is probed automatically:
            // ->insert($view->textInput('AdsController'))
            ->insert($view->textInput('AdsLdapAccountsBranch')->setAttribute('placeholder', $view['defaultLdapAccountsBranch']))
        )
    
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
