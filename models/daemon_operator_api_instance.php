<?php
    /**
     * Daemon operator API instance
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

    class daemon_operator_api_instance
    {
        var $enabled = false;
        var $api_url;
        var $public_key;
        var $secret_key;

        var $exists = false;

        function __construct($daemon_instance_keyname, $coin_name)
        {
            global $config;
            if( isset($config->daemon_operators[$daemon_instance_keyname]) )
            {
                $provider          = $config->daemon_operators[$daemon_instance_keyname];
                $this->enabled     = $provider["enabled"];
                $this->api_url     = $provider["per_coin_data"][$coin_name]["api_url"];
                $this->public_key  = $provider["per_coin_data"][$coin_name]["public_key"];
                $this->secret_key  = $provider["per_coin_data"][$coin_name]["secret_key"];
                $this->exists      = true;
            } # end if
        }

        function send_api_command($outgoing_params)
        {
            # Data preparation
            $outgoing_params["public_key"] = $this->public_key;
            foreach($outgoing_params as $key => $val)
                if($key != "public_key")
                    $outgoing_params[$key] = encryptRJ256($this->secret_key, $val);
            $encoded_params = http_build_query($outgoing_params);

            # Sending
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $this->api_url);
            curl_setopt($ch, CURLOPT_POST,           count($outgoing_params));
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $encoded_params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);

            if( curl_errno($ch) )
            {
                $return = (object) array("message" => "ERROR:COMM_ERROR", "extra_info" => curl_error($ch));
                curl_close($ch);
                return $return;
            } # end if

            curl_close($ch);
            $res = json_decode($res);
            if( $res->data ) $res->data = decryptRJ256($this->secret_key, $res->data);
            $tmp = json_decode($res->data); if( is_object($tmp) || is_array($tmp) ) $res->data = $tmp;
            return $res;
        }

    }
