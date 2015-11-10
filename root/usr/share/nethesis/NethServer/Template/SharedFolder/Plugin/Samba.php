<?php

/* @var $view Nethgui\Renderer\Xhtml */

$vfsRecycle = $view->fieldsetSwitch('SmbRecycleBinStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->checkBox('SmbRecycleBinVersionsStatus', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
;

// $vfsShadow = $view->checkBox('SmbShadowCopyStatus', 'enabled')->setAttribute('uncheckedValue', 'disabled');
//$customValues = $view->fieldset()->setAttribute('template', $T('Profiles_label'))
//    ->insert($view->radioButton('profileName', 'default'))
//    ->insert($view->fieldsetSwitch('profileName', 'custom', $view::FIELDSETSWITCH_EXPANDABLE )
//        ->insert($view->textInput('customValue', $view::LABEL_NONE)))
//    ;

$guestAccess = $view->fieldset()->setAttribute('template', $T('SmbGuestAccess_label'))
    ->insert($view->radioButton('SmbGuestAccessType', 'none'))
    ->insert($view->radioButton('SmbGuestAccessType', 'r'))
    ->insert($view->radioButton('SmbGuestAccessType', 'rw'))
;

$browseableState = $view->fieldsetswitch('SmbShareBrowseable', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'enabled')
;

echo $view->fieldsetSwitch('SmbStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($vfsRecycle)
//    ->insert($vfsShadow)
//    ->insert($customValues)
    ->insert($guestAccess)
//    ->insert($browseableState)
;
