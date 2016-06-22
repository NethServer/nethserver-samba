<?php
namespace NethServer\Module\SharedFolder;

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

/**
 * Trigger ibay-reset-permissions event
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ResetPermissions extends \Nethgui\Controller\Table\AbstractAction
{
    /**
     * The selected ibay identifier
     *
     * @var string
     */
    private $key;

    public function __construct()
    {
        parent::__construct('reset-permissions');
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);

        $this->key = \Nethgui\array_end($request->getPath());

        /* @var $tableAdapter \ArrayInterface */
        $tableAdapter = $this->getAdapter();

        // Check if the ibay identifier is set:
        if ( ! isset($tableAdapter[$this->key])) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1353949626);
        }
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('ibay-reset-permissions &', array($this->key));
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['ibay'] = $this->key;
    }
}