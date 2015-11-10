<?php
namespace NethServer\Module\SharedFolder\Plugin;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
use Nethgui\Controller\Table\Modify as Table;

/**
 * Samba SharedFolder plugin
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Samba extends \Nethgui\Controller\Table\RowPluginAction
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Samba', 10);
    }

    public function initialize()
    {
        // Supported Samba ibay profiles
        $this->profiles = array(
            'default'
        );

        $schema = array(
            array('SmbStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbProfileType', FALSE, Table::FIELD),
            // array('SmbShadowCopyStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbRecycleBinStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbRecycleBinVersionsStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbGuestAccessType', '/^(none|rw|r)$/', Table::FIELD),
            array('SmbShareBrowseable', '/^(true|false)$/', Table::FIELD),
        );

        $this->setSchemaAddition($schema);
        $this
            ->setDefaultValue('SmbProfileType', 'default')
            ->setDefaultValue('SmbStatus', 'enabled')
            ->setDefaultValue('SmbGuestAccessType', 'none')
            ->setDefaultValue('SmbShareBrowseable', 'true')
        ;
        parent::initialize();

        $profileNameValidator = $this->createValidator()->memberOf(array_merge($this->profiles, array('custom')));

        $this->declareParameter('profileName', $profileNameValidator, array());
        $this->declareParameter('customValue', $this->createValidator(), array());
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $request = $this->getRequest();
        // restrict custom profile name to a common "variable name" grammar:
        if ($request->isMutation() && $this->parameters['profileName'] === 'custom') {
            $this->getValidator('customValue')->regexp('/^[a-z][a-z0-9]+$/i');
        }
        parent::validate($report);
    }

    public function readProfileName()
    {
        if ( ! isset($this->parameters['SmbProfileType']) || empty($this->parameters['SmbProfileType'])) {
            return '';
        }
        if (in_array($this->parameters['SmbProfileType'], $this->profiles)) {
            return $this->parameters['SmbProfileType'];
        }
        return 'custom';
    }

    public function writeProfileName($value)
    {
        if (in_array($value, $this->profiles)) {
            $this->parameters['SmbProfileType'] = $value;
            return TRUE;
        }
    }

    public function readCustomValue()
    {
        if ( ! isset($this->parameters['SmbProfileType']) || empty($this->parameters['SmbProfileType'])) {
            return '';
        }

        if (in_array($this->parameters['SmbProfileType'], $this->profiles)) {
            return '';
        }

        return $this->parameters['SmbProfileType'];
    }

    public function writeCustomValue($value)
    {
        $request = $this->getRequest();
        if ($request->isMutation() && $request->hasParameter('customValue')) {
            NETHGUI_DEBUG && $this->getLog()->notice(sprintf('COPY %s to SmbProfileType', $this->parameters['customValue']));
            $this->parameters['SmbProfileType'] = $this->parameters['customValue'];
            return TRUE;
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($view['profileName'] === '') {
            $view['profileName'] = 'default';
        }
        unset($view['SmbProfileType']);
    }

}
