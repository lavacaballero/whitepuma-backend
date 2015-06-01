<?php
    /**
     * Tipping provider class for final clients.
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

    class tipping_provider
    {
        var $api_url;
        var $public_key;
        var $secret_key;

        protected $outgoing_params;

        function __construct($api_url, $public_key, $secret_key)
        {
            $this->api_url    = $api_url;
            $this->public_key = $public_key;
            $this->secret_key = $secret_key;
        }

        function register($account_id)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "register",
                "account"       => $account_id
            );
            return $this->send_request();
        }

        function get_address($account_id)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "get_address",
                "account"       => $account_id
            );
            return $this->send_request();
        }

        function get_balance($account_id)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "get_balance",
                "account"       => $account_id
            );
            return $this->send_request();
        }

        function list_transactions($account_id)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "list_transactions",
                "account"       => $account_id
            );
            return $this->send_request();
        }

        function send($sender_account_id, $target_account_id, $amount)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "send",
                "account"       => $sender_account_id,
                "target"        => $target_account_id,
                "amount"        => $amount
            );
            return $this->send_request();
        }

        function withdraw($sender_account_id, $target_address, $amount)
        {
            $this->outgoing_params = array(
                "public_key"    => $this->public_key,
                "action"        => "withdraw",
                "account"       => $sender_account_id,
                "target"        => $target_address,
                "amount"        => $amount
            );
            return $this->send_request();
        }

        protected function send_request()
        {
            # Data preparation
            foreach($this->outgoing_params as $key => $val)
                if( $key != "public_key" )
                    $this->outgoing_params[$key] = encryptRJ256($this->secret_key, trim($val));
            $encoded_params = http_build_query($this->outgoing_params);

            # Sending
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $this->api_url);
            curl_setopt($ch, CURLOPT_POST,           count($this->outgoing_params));
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
            if( $res->data && ! is_object($res->data) && ! is_array($res->data) )
                $res->data = decryptRJ256($this->secret_key, $res->data);
            if( ! empty($res->data) && ! is_object($res->data) && ! is_array($res->data) )
                $res->data = json_decode($res->data);
            return $res;
        }

    }
