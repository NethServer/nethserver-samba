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

    /**
     * @var bool
     */
    private $isAuthNeeded = FALSE;

    public function initialize()
    {
        parent::initialize();

        $roleValidator = $this->getPlatform()->createValidator()->memberOf('WS', 'PDC', 'ADS');
        $hostnameOrEmptyValidator = $this->createValidator()->orValidator($this->createValidator(Validate::HOSTNAME), $this->createValidator(Validate::EMPTYSTRING));

        $this->declareParameter('WorkgroupName', $hostnameOrEmptyValidator, array('configuration', 'smb', 'Workgroup'));
        $this->declareParameter('PdcDomain', $hostnameOrEmptyValidator, array('configuration', 'smb', 'Workgroup'));
        $this->declareParameter('AdsDomain', $hostnameOrEmptyValidator, array('configuration', 'smb', 'Workgroup'));
        
        $this->declareParameter('ServerRole', $roleValidator, array('configuration', 'smb', 'ServerRole'));
        $this->declareParameter('RoamingProfiles', Validate::YES_NO, array('configuration', 'smb', 'RoamingProfiles'));

        // DISABLED: this is probed automatically:
        // $this->declareParameter('AdsController', $hostnameOrEmptyValidator, array('configuration', 'smb', 'AdsController'));

        $this->declareParameter('AdsRealm', $hostnameOrEmptyValidator, array('configuration', 'smb', 'AdsRealm'));
        $this->declareParameter('AdsLdapAccountsBranch', Validate::ANYTHING, array('configuration', 'smb', 'AdsLdapAccountsBranch'));
    }

    public function readWorkgroupName($v1)
    {
        return $v1;
    }

    public function writeWorkgroupName($value)
    {
        if($this->parameters['ServerRole'] === 'WS') {
            return array($value);
        }
        return FALSE;
    }

    public function readPdcDomain($v1) 
    {
        return $v1;
    }

    public function writePdcDomain($value) 
    {
        if($this->parameters['ServerRole'] === 'PDC') {
            return array($value);
        }
        return FALSE;
    }

    public function readAdsDomain($v1) 
    {
        return $v1;
    }

    public function writeAdsDomain($value) 
    {
        if($this->parameters['ServerRole'] === 'ADS') {
            return array($value);
        }
        return FALSE;
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-samba-save');

        if($this->parameters['ServerRole'] === 'ADS') {
            $this->isAuthNeeded = $this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/smbads test')->getExitCode() === 0 ? FALSE : TRUE;
        }

        if($this->isAuthNeeded === FALSE
           && $this->parameters['ServerRole'] === 'ADS') {
            // Re-expand templates and reloads daemons, if
            // Authenticate action is not needed:
            $this->getPlatform()->signalEvent('nethserver-samba-adsjoin', array('test'));
        }       
    }

    public function nextPath()
    {
        if ($this->isAuthNeeded) {
            return 'Authenticate';
        }
        return parent::nextPath();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['WinregistryPatches'] = $view->getSiteUrl() . '/winregistry-patches';
        $view['defaultRealm'] = strtoupper($this->getPlatform()->getDatabase('configuration')->getType('DomainName'));
        $view['defaultDomain'] = \Nethgui\array_head(explode('.', strtoupper($this->getPlatform()->getDatabase('configuration')->getType('DomainName'))));
        $view['defaultWorkgroup'] = 'WORKGROUP';
        $view['defaultLdapAccountsBranch'] = 'cn=Users';
    }

}
