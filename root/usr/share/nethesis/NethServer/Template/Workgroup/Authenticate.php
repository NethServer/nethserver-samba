<?php
echo $view->panel()->insert($view->fieldset()->setAttribute('template', 'Administrative credentials')
        ->insert($view->textInput('login'))
        ->insert($view->textInput('password'))
);

