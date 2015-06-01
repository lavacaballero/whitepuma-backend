<?php
    /**
     * Client model
     *
     * @package    WhitePuma OpenSource Platform
     * @subpackage Backend (wallets API gateway)
     * @copyright  2014 Alejandro Caballero
     * @author     Alejandro Caballero - acaballero@lavasoftworks.com
     * @license    GNU-GPL v3 (http://www.gnu.org/licenses/gpl.html)
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     * THE SOFTWARE.
     */

    class client
    {
        var $client_id;
        var $enabled = false;
        var $name;
        var $website;
        var $public_key;
        var $secret_key;
        var $fees_account;
        var $daemon_operator;
        var $can_register_here;

        var $exists = false;

        function __construct($client_id)
        {
            global $config;
            if( isset($config->clients_database[$client_id]) )
            {
                $client_data             = $config->clients_database[$client_id];

                $this->client_id         = $client_id;
                $this->enabled           = $client_data["enabled"];
                $this->name              = $client_data["name"];
                $this->website           = $client_data["website"];
                $this->public_key        = $client_data["public_key"];
                $this->secret_key        = $client_data["secret_key"];
                $this->fees_account      = $client_data["fees_account"];
                $this->daemon_operator   = $client_data["daemon_operator"];
                $this->can_register_here = $client_data["can_register_here"];
                $this->exists            = true;
            } # end if
        }
    }
