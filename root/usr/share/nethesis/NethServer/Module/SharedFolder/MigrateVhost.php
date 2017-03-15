<?php
namespace NethServer\Module\SharedFolder;

/*
 * Copyright (C) 2017 Nethesis S.r.l.
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

/**
 * Trigger vhost-migrate-ibay event
 *
 */
class MigrateVhost extends \Nethgui\Controller\Table\AbstractAction
{
    /**
     * The selected ibay identifier
     *
     * @var string
     */
    private $key;

    public function __construct()
    {
        parent::__construct('migrate-vhost');
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);

        $this->key = \Nethgui\array_end($request->getPath());

        /* @var $tableAdapter \ArrayInterface */
        $tableAdapter = $this->getAdapter();

        // Check if the ibay identifier is set:
        if ( ! isset($tableAdapter[$this->key])) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1489431143);
        }
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('vhost-migrate-ibay', array($this->key));
            $this->getAdapter()->flush();
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['vhost'] = $view->translate($this->getPlatform()->getDatabase('accounts')->getProp($this->key,'HttpVirtualHost'));
        $view['ibay'] = $this->key;
    }

}
