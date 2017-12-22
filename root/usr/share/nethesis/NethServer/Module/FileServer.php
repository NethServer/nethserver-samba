<?php

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

namespace NethServer\Module;

use Nethgui\System\PlatformInterface as Validate;

class FileServer extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_FileServer'),
            'category' => 'Configuration'
        ));
    }

    public function initialize()
    {
        $workgroupValidator = $this->createValidator(Validate::HOSTNAME_SIMPLE)->maxLength(15);
        $this->declareParameter('Workgroup', $workgroupValidator, array('configuration', 'sssd', 'Workgroup'));
        $this->declareParameter('Provider', FALSE, array('configuration', 'sssd', 'Provider'));
        $this->declareParameter('ShareAdm', Validate::SERVICESTATUS, array('configuration', 'smb', 'ShareAdmStatus'));
        $this->declareParameter('HomeAdm', Validate::SERVICESTATUS, array('configuration', 'smb', 'HomeAdmStatus'));
        $this->declareParameter('InheritOwner', Validate::YES_NO, array('configuration', 'smb', 'InheritOwner'));
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-samba-save &');
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if($view['Provider'] !== 'ad') {
            $this->notifications->warning($view->translate('LDAP_account_provider_warning_message1'));
        }
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
        );
    }
}
