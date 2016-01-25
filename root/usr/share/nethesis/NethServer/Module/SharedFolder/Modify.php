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

    private $originalAclRead;
    private $originalAclWrite;


    public function initialize()
    {
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
            array('OwningGroup', Validate::USERNAME, Table::FIELD),
            array('OwnersDatasource', false, null),
            array('GroupAccess', '/^rw?$/', Table::FIELD),
            array('OtherAccess', '/^r?$/', Table::FIELD),
            array('AclRead', Validate::USERNAME_COLLECTION, Table::FIELD, 'AclRead', ','), // ACL
            array('AclWrite', Validate::USERNAME_COLLECTION, Table::FIELD, 'AclWrite', ','), // ACL
            array('AclSubjects', FALSE, null),
        );

        $this->setSchema($parameterSchema);
        parent::initialize();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if($request->isMutation()) {
            // save the old values for later usage:
            $this->originalAclRead = $this->getPlatform()->getDatabase('accounts')->getProp($this->parameters['ibay'], 'AclRead');
            $this->originalAclWrite = $this->getPlatform()->getDatabase('accounts')->getProp($this->parameters['ibay'], 'AclWrite');
        }
    }

    protected function onParametersSaved($changedParameters)
    {
        $action = $this->getIdentifier();
        if ($action == 'update') {
            $action = 'modify';
        }

        $eventArgs = array($this->parameters['ibay'], '--orig-acl-read', $this->originalAclRead, '--orig-acl-write', $this->originalAclWrite);

        $this->getPlatform()->signalEvent(sprintf('ibay-%s@post-process', $action), $eventArgs);
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

        $owners = array(array('locals', $view->translate('locals_group_label')));
        $subjects = array(array('locals', $view->translate('locals_group_label')));

        foreach ($this->getPlatform()->getDatabase('accounts')->getAll('group') as $keyName => $props) {
            $entry = array($keyName, sprintf("%s (%s)", isset($props['Description']) ? $props['Description'] : $keyName, $keyName));
            $owners[] = $entry;
            $subjects[] = $entry;
        }

        $view['OwningGroupDatasource'] = $owners;

        foreach ($this->getPlatform()->getDatabase('accounts')->getAll('user') as $keyName => $props) {
            $entry = array($keyName, sprintf("%s (%s)", trim($props['FirstName'] . ' ' . $props['LastName']), $keyName));
            $subjects[] = $entry;
        }

        $view['AclSubjects'] = $subjects;

        $view['reset-permissions'] = $view->getModuleUrl('../reset-permissions/' . $this->getAdapter()->getKeyValue());
    }

}
