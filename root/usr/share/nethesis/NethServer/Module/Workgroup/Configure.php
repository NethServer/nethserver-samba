<?php
namespace NethServer\Module\Workgroup;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;

/**
 * Configure SAMBA
 *
 * @link https://dev.nethesis.it/projects/nethserver-samba
 */
class Configure extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        parent::initialize();

        $roleValidator = $this->getPlatform()->createValidator()->memberOf('WS', 'PDC', 'ADS');
        $hostnameOrEmptyValidator = $this->createValidator()->orValidator($this->createValidator(Validate::HOSTNAME), $this->createValidator(Validate::EMPTYSTRING));

        $this->declareParameter('Workgroup', Validate::HOSTNAME, array('configuration', 'smb', 'Workgroup'));
        $this->declareParameter('ServerRole', $roleValidator, array('configuration', 'smb', 'ServerRole'));
        $this->declareParameter('RoamingProfiles', Validate::YES_NO, array('configuration', 'smb', 'RoamingProfiles'));
        $this->declareParameter('AdsController', $hostnameOrEmptyValidator, array('configuration', 'smb', 'AdsController'));
        $this->declareParameter('AdsRealm', $hostnameOrEmptyValidator, array('configuration', 'smb', 'AdsRealm'));
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-samba-save@post-process');
    }

    public function nextPath()
    {
        $isAuthNeeded = FALSE;
        $request = $this->getRequest();
        if ($request->hasParameter('ServerRole') && $request->getParameter('ServerRole') === 'ADS') {
            // $this->isAuthNeeded = exitcode of `net -k ads testjoin`
            $isAuthNeeded = $this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/smbads test')->getExitCode() === 0 ? FALSE : TRUE;
        }

        if ($isAuthNeeded) {
            return 'Authenticate';
        }
        return parent::nextPath();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['WinregistryPatches'] = $view->getSiteUrl() . '/winregistry-patches';
        $view['defaultRealm'] = strtoupper($this->getPlatform()->getDatabase('configuration')->getType('DomainName'));
    }

}
