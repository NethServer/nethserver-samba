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
 * Manage credentials of the AD administrator
 *
 * @link https://dev.nethesis.it/projects/nethserver-samba
 */
class Authenticate extends \Nethgui\Controller\AbstractController
{
    /**
     *
     * @var \NethServer\Tool\PasswordStash
     */
    private $passwordStash;

    /**
     *
     * @var bool
     */
    private $isAuthNeeded = FALSE;

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('login', Validate::NOTEMPTY);
        $this->declareParameter('password', Validate::NOTEMPTY);

        $this->passwordStash = new \NethServer\Tool\PasswordStash();
        $this->passwordStash->setAutoUnlink(TRUE);
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
            $this->passwordStash->store($this->parameters['password']);
            $this->getPlatform()->signalEvent('nethserver-samba-adsjoin', array('-u', $this->parameters['login'], '-F', $this->passwordStash->getFilePath(), 'join'));

            // Check if join is OK:
            $this->isAuthNeeded = $this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/smbads test')->getExitCode() === 0 ? FALSE : TRUE;
        }
    }

    public function nextPath()
    {
        if ($this->isAuthNeeded) {
            return FALSE;
        }
        return 'Configure';
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($this->isAuthNeeded === TRUE && $this->getRequest()->isMutation()) {
            $view->getCommandList('/Notification')->showMessage($view->translate('Join_failed_message'), \Nethgui\Module\Notification\AbstractNotification::NOTIFY_ERROR);
        }
    }

}
