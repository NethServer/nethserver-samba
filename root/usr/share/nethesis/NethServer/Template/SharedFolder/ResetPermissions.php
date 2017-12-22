<?php

/* @var $view Nethgui\Renderer\Xhtml */
$view->requireFlag($view::INSET_DIALOG | $view::INSET_FORM);

echo $view->header('ibay')->setAttribute('template', $T('reset-permissions_Header'));

echo $view->textLabel('ibay')->setAttribute('template', $T('reset-permissions_Message'));

echo $view->buttonList()
    ->insert($view->button('Reset', $view::BUTTON_SUBMIT))
    ->insert($view->button('Cancel', $view::BUTTON_CANCEL))
;