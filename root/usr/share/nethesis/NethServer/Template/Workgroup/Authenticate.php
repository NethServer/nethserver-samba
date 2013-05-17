<?php
/* @var $view Nethgui\Renderer\Xhtml */

$view->requireFlag($view::INSET_DIALOG);

echo $view->header()->setAttribute('template', $T('Authenticate_header'));

echo $view->textInput('login');
echo $view->textInput('password', $view::TEXTINPUT_PASSWORD);

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL);

