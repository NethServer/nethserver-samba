<?php

$baseTabColumnSet1 = $view->columns();

$baseTabColumnSet1->insert($view->fieldset()->setAttribute('template', $T('Recycle'))
        ->insert($view->radioButton('RecycleBin', 'disabled'))
        ->insert($view->fieldsetSwitch('RecycleBin', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
            ->insert($view->checkBox('KeepVersions', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        )
);

$baseTabColumnSet1->insert($view->fieldset()->setAttribute('template', $T('Shadow copy'))
        ->insert($view->radioButton('ShadowCopy', 'disabled'))
        ->insert($view->radioButton('ShadowCopy', 'enabled'))
);

echo $baseTabColumnSet1;
