<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('FileServer_header'));

if($view['Provider'] === 'ad') {
    $adFlags = $view::STATE_DISABLED | $view::STATE_READONLY;
} else {
    $adFlags = 0;
}

echo $view->textInput('Workgroup', $adFlags);

if($adFlags) {
    echo $view->fieldset()->setAttribute('template', $T('InheritOwner_label'))
        ->insert($view->radioButton('InheritOwner', 'yes'))
        ->insert($view->radioButton('InheritOwner', 'no'))
    ;

    echo $view->fieldset()->setAttribute('template', $T('SpecialPrivileges_label'))
        ->insert($view->checkBox('HomeAdm', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        ->insert($view->checkBox('ShareAdm', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
    ;
}

echo $view->buttonList($view::BUTTON_HELP)
    ->insert($view->button('Save', $view::BUTTON_SUBMIT))
;
