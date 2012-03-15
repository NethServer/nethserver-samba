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
        $this->declareParameter('workgroup', Validate::HOSTNAME, array('configuration', 'smb', 'Workgroup'));
        $this->declareParameter('server', Validate::HOSTNAME, array('configuration', 'smb', 'ServerName'));
        $this->declareParameter('role', "/^WS$|^PDC$|^ADS$/", array('configuration', 'smb', 'ServerRole'));
        $this->declareParameter('RoamingProfiles', Validate::YES_NO, array('configuration', 'smb', 'RoamingProfiles'));
        $this->declareParameter('PDCName', Validate::HOSTNAME, array('configuration', 'smb', 'PDCName'));
        $this->declareParameter('PDCIP', Validate::IPv4, array('configuration', 'smb', 'PDCIP'));
        $this->declareParameter('Realm', Validate::HOSTNAME, array('configuration', 'smb', 'Realm'));
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-samba-save@post-process');
    }

}
