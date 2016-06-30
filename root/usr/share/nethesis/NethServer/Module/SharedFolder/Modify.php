<?php
namespace NethServer\Module\SharedFolder;

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
use Nethgui\Controller\Table\Modify as Table;

/**
 * This class manage shared folder
 *
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    private $userProvider = null;
    private $groupProvider = null;

    private function getUserProvider()
    {
        if(!$this->userProvider) {
            $this->userProvider = new \NethServer\Tool\UserProvider($this->getPlatform());
        }
        return $this->userProvider;
    }

    private function getGroupProvider()
    {
        if(!$this->groupProvider) {
            $this->groupProvider = new \NethServer\Tool\GroupProvider($this->getPlatform());
        }
        return $this->groupProvider;
    }

    public function initialize()
    {
        // Supported Samba ibay profiles
        $this->profiles = array(
            'default'
        );

        /*
         * Refs #941, #1536. Avoid deletion of Primary ibay
         */
        if ($this->getIdentifier() === 'delete') {
            $ibayNameValidator = $this->createValidator(Validate::USERNAME)->platform('ibay-delete');
        } elseif ($this->getIdentifier() === 'create') {
            $ibayNameValidator = $this->createValidator(Validate::USERNAME)->platform('ibay-create');
        } else {
            $ibayNameValidator = FALSE;
        }

        $parameterSchema = array(
            array('ibay', $ibayNameValidator, Table::KEY),
            array('Description', Validate::ANYTHING, Table::FIELD),
            array('OwningGroup', Validate::ANYTHING, Table::FIELD),
            array('OwnersDatasource', false, null),
            array('GroupAccess', '/^rw?$/', Table::FIELD),
            array('OtherAccess', '/^r?$/', Table::FIELD),
            array('AclRead', Validate::ANYTHING_COLLECTION, Table::FIELD, 'AclRead', ','), // ACL
            array('AclWrite', Validate::ANYTHING_COLLECTION, Table::FIELD, 'AclWrite', ','), // ACL
            array('AclSubjects', FALSE, null),
            array('SmbProfileType', FALSE, Table::FIELD),
            array('SmbRecycleBinStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbRecycleBinVersionsStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('SmbGuestAccessType', '/^(none|rw|r)$/', Table::FIELD),
            array('SmbShareBrowseable', Validate::SERVICESTATUS, Table::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this
            ->setDefaultValue('SmbProfileType', 'default')
            ->setDefaultValue('SmbShareBrowseable', 'enabled')
        ;
        if ($this->getGroupProvider()->isAD()) {
           $this->setDefaultValue('SmbGuestAccessType', 'none');
        } else {
           $this->setDefaultValue('SmbGuestAccessType', 'rw');
        }

        $profileNameValidator = $this->createValidator()->memberOf(array_merge($this->profiles, array('custom')));

        $this->declareParameter('profileName', $profileNameValidator, array());
        $this->declareParameter('customValue', $this->createValidator(), array());

        parent::initialize();
    }

    protected function onParametersSaved($changedParameters)
    {
        $action = $this->getIdentifier();
        if ($action == 'update') {
            $action = 'modify';
        }
        $this->getPlatform()->signalEvent(sprintf('ibay-%s &', $action), array($this->parameters['ibay']));
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $templates = array(
            'create' => 'NethServer\Template\SharedFolder\Modify',
            'update' => 'NethServer\Template\SharedFolder\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);

        $view['isAD'] = $this->getGroupProvider()->isAD();
        $DomainName = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $owners = array(array('domain users'.'@'.$DomainName, $view->translate('domain_users_group_label')));
        $subjects = array(array('domain users'.'@'.$DomainName, $view->translate('domain_users_group_label')));

        // The account providers can be slow. We're trying to execute them only
        // when effectively needed.
        if( $this->getIdentifier() !== 'delete'
                && $this->getRequest()->isValidated()
                && ! $this->getRequest()->isMutation()) {
            foreach ($this->getGroupProvider()->getGroups() as $keyName => $props) {
                $entry = array($keyName, $keyName);
                $owners[] = $entry;
                $subjects[] = $entry;
            }

            $view['OwningGroupDatasource'] = $owners;

            foreach ($this->getUserProvider()->getUsers() as $keyName => $props) {
                $entry = array($keyName, $keyName);
                $subjects[] = $entry;
            }

            $view['AclSubjects'] = $subjects;
        }

        $view['reset-permissions'] = $view->getModuleUrl('../reset-permissions/' . $this->getAdapter()->getKeyValue());

           if ($view['profileName'] === '') {
            $view['profileName'] = 'default';
        }
        unset($view['SmbProfileType']);

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

}
