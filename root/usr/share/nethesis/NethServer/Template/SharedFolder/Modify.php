<?php

/* @var $view \Nethgui\Renderer\Xhtml */
$view->requireFlag($view::INSET_FORM);

if ($view->getModule()->getIdentifier() == 'update') {
    $keyFlags = $view::STATE_DISABLED;
    $template = 'Update_Header';
} else {
    $keyFlags = 0;
    $template = 'Create_Header';
}

$guestAccess = $view->fieldset()->setAttribute('template', $T('SmbGuestAccess_label'))
    ->insert($view->radioButton('SmbGuestAccessType', 'none'))
    ->insert($view->radioButton('SmbGuestAccessType', 'r'))
    ->insert($view->radioButton('SmbGuestAccessType', 'rw'))
;

$browseableState = $view->checkBox('SmbShareBrowseable', 'enabled')->setAttribute('uncheckedValue', 'disabled');

$vfsRecycle = $view->fieldsetSwitch('SmbRecycleBinStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->checkBox('SmbRecycleBinVersionsStatus', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
;

$advanced = $view->fieldset('', $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('template', $T('Advanced_label'))
    ->insert($vfsRecycle)
    ->insert($browseableState)
;

echo $view->header('ibay')->setAttribute('template', $view->translate($template));
$baseTab = $view->panel()->setAttribute('name', "BaseInfo")
    ->insert($view->textInput('ibay', $keyFlags))
    ->insert($view->textInput('Description'))
    ->insert($view->selector('OwningGroup', $view::SELECTOR_DROPDOWN))
    ->insert($view->checkBox('GroupAccess', 'rw')->setAttribute('uncheckedValue', 'r'))
    ->insert($view->checkBox('OtherAccess', 'r')->setAttribute('uncheckedValue', ''))
    ->insert($guestAccess)
    ->insert($advanced)
;

$aclTab = $view->panel()->setAttribute('name', "Acl")
    ->insert($view->objectPicker()
    ->setAttribute('objects', 'AclSubjects')
    ->setAttribute('objectLabel', 1)
    ->insert($view->checkBox('AclRead', FALSE, $view::STATE_CHECKED))
    ->insert($view->checkBox('AclWrite', FALSE))
);

echo $view->tabs()
    ->insert($baseTab)
    ->insert($aclTab)
    ->insertPlugins()
;

$buttons = $view->buttonList()
    ->insert($view->button('Submit', $view::BUTTON_SUBMIT))
;

if ($keyFlags !== 0) {
    $buttons->insert($view->button('reset-permissions', $view::BUTTON_LINK));
}

$buttons
    ->insert($view->button('Cancel', $view::BUTTON_CANCEL))
    ->insert($view->button('Help', $view::BUTTON_HELP))
;

echo $buttons;

$actionId = $view->getUniqueId();
$view->includeJavascript("
jQuery(function($){
    $('#${actionId}').on('nethguishow', function () {
        $(this).find('.Tabs').tabs('select', 0);
    });
});
");
