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

echo $view->header('ibay')->setAttribute('template', $view->translate($template));
$baseTab = $view->panel()->setAttribute('name', "BaseInfo")
    ->insert($view->textInput('ibay', $keyFlags))
    ->insert($view->textInput('Description'))
    ->insert($view->selector('OwningGroup', $view::SELECTOR_DROPDOWN))
    ->insert($view->checkBox('GroupAccess', 'rw')->setAttribute('uncheckedValue', 'r'))
    ->insert($view->checkBox('OtherAccess', 'r')->setAttribute('uncheckedValue', ''))
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
    ->insertPlugins()
    ->insert($aclTab)
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