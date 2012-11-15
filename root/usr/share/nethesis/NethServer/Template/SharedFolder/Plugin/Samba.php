<?php

/* @var $view Nethgui\Renderer\Xhtml */



$vfsRecycle = $view->fieldsetSwitch('SmbRecycleBinStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->checkBox('SmbRecycleBinVersionsStatus', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
;

$vfsShadow = $view->checkBox('SmbShadowCopyStatus', 'enabled')->setAttribute('uncheckedValue', 'disabled');

$customValues = $view->fieldset()->setAttribute('template', $T('Profiles_label'))
    ->insert($view->radioButton('profileName', 'default'))
    ->insert($view->fieldsetSwitch('profileName', 'custom', $view::FIELDSETSWITCH_EXPANDABLE )
        ->insert($view->textInput('customValue', $view::LABEL_NONE)))
    ;

echo $view->fieldsetSwitch('SmbStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($vfsRecycle)
    ->insert($vfsShadow)
    ->insert($customValues)
;