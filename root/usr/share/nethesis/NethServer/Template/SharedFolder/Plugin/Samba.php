<?php
$baseTabColumnSet1 = $view->columns();

$baseTabColumnSet1->insert($view->fieldset()->setAttribute('template', $T('Recycle'))
        ->insert($view->fieldsetSwitch('RecycleBin', 'enabled')
            ->insert($view->checkBox('KeepVersions', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        )
        ->insert($view->fieldsetSwitch('RecycleBin', 'disabled')));

$baseTabColumnSet1->insert($view->fieldset()->setAttribute('template', $T('Shadow copy'))
        ->insert($view->fieldsetSwitch('ShadowCopy', 'enabled'))
        ->insert($view->fieldsetSwitch('ShadowCopy', 'disabled')));

echo $baseTabColumnSet1;
