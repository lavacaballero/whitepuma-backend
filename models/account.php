<?php
    /**
     * User account class
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

    class account
    {
        var $id_account;
        var $parent_id_account;
        var $user_name;
        var $password;

        var $name;
        var $email;
        var $type = "user"; # user, client, admin

        var $receive_notifications = "false";

        var $creation_host;
        var $last_login_host;
        var $last_login_date;
        var $creation_date;
        var $last_update;
        var $last_activity;

        # Temporary
        var $_raw_password;
        var $_exists = false;

        /**
         * User account
         *
         * @param mixed $input Previously fetched row or id_account|user_name to search
         * @return account
         */
        function __construct($input = "")
        {
            global $config;
            if( ! is_resource($config->db_handler) ) db_connect();

            if( is_object($input) )
            {
                $this->assign_from_object($input);
                $this->_exists = true;
                return $this;
            }

            if( empty($input) )
            {
                $this->_exists = false;
                return $this;
            } # end if

            $input = addslashes(trim(stripslashes($input)));
            $query = "select * from {$config->db_tables["account"]} where id_account = '$input' or user_name = '$input'";
            $res   = mysql_query($query);
            if( mysql_num_rows($res) )
            {
                $row = mysql_fetch_object($res);
                $this->assign_from_object($row);
                $this->_exists = true;
                return $this;
            } # end if
            mysql_free_result($res);
        }

        /**
         * Assigns the current class properties from an incoming database query
         *
         * @param object $object
         * @return $this
         */
        function assign_from_object($object)
        {
            global $config;
            $this->id_account            = $object->id_account           ;
            $this->parent_id_account     = $object->parent_id_account     ;
            $this->user_name             = $object->user_name            ;
            $this->password              = $object->password             ;
            $this->name                  = $object->name                 ;
            $this->email                 = $object->email                ;
            $this->receive_notifications = $object->receive_notifications;
            $this->creation_date         = $object-creation_date         ;
            $this->last_update           = $object->last_update          ;
            $this->last_activity         = $object->last_activity        ;

            if($this->creation_date   == "0000-00-00 00:00:00") $this->creation_date  = "";
            if($this->last_update     == "0000-00-00 00:00:00") $this->last_update   = "";
            if($this->last_activity   == "0000-00-00 00:00:00") $this->last_activity = "";
            if($this->last_login_date == "0000-00-00 00:00:00") $this->last_login_date = "";

            return $this;
        }

        /**
         * Adds/Updates account deposit address for a given daemon
         *
         * @param string $coin_name
         * @param string $address   to save
         */
        function set_wallet_address($coin_name, $address)
        {
            global $config;
            $now = date("Y-m-d H:i:s");
            $this->last_update      =
            $this->last_activity    = $now;
            $query = "
                update {$config->db_tables["account_wallets"]} set
                    id_account    = '$this->id_account',
                    coin_name     = '$coin_name',
                    address       = '$address',
                    last_update   = '$now',
                    last_activity = '$now'
                where
                    id_account    = '$this->id_account'
            ";
            mysql_query($query);
            if( mysql_affected_rows() == 0 )
            {
                $query = "
                    insert into {$config->db_tables["account_wallets"]} set
                        id_account    = '$this->id_account',
                        coin_name     = '$coin_name',
                        address       = '$address',
                        last_update   = '$now',
                        last_activity = '$now'
                ";
            } # end if
        }

        /**
         * Sets the last_activity of the account
         *
         * @param boolean $echo_query For debugging only
         */
        function ping($echo_query = false)
        {
            global $config;
            if( ! is_resource($config->db_handler) ) db_connect();

            $this->last_activity = date("Y-m-d H:i:s");
            $query = "
                update {$config->db_tables["account"]} set
                    last_activity    = '$this->last_activity'
                where
                    id_account       = '$this->id_account'
            ";
            if($echo_query) echo $query;
            mysql_query($query);
        }

        function save()
        {
            global $config;
            if( ! is_resource($config->db_handler) ) db_connect();

            $now = date("Y-m-d H:i:s");

            if( ! $this->_exists )
            {
                $this->creation_host    = $_SERVER["REMOTE_ADDR"] . "; " . gethostbyaddr($_SERVER["REMOTE_ADDR"]);
                $this->creation_date    =
                $this->last_update      =
                $this->last_activity    = $now;
                $query = "
                    insert into {$config->db_tables["account"]} set
                        id_account              = '".addslashes($this->id_account)."',
                        parent_id_account       = '".addslashes($this->parent_id_account)."',
                        user_name               = '".addslashes($this->user_name)."',
                        password                = '".addslashes($this->password)."',
                        name                    = '".addslashes($this->name)."',
                        email                   = '".addslashes($this->email)."',
                        type                    = '".addslashes($this->type)."',
                        receive_notifications   = '".addslashes($this->receive_notifications)."',
                        creation_host           = '".addslashes($this->creation_host)."',
                        creation_date           = '$now',
                        last_update             = '$now',
                        last_activity           = '$now'
                ";
            }
            else
            {
                $query = "
                    update {$config->db_tables["account"]} set
                    #   parent_id_account       = '".addslashes($this->parent_id_account)."',
                        user_name               = '".addslashes($this->user_name)."',
                        password                = '".addslashes($this->password)."',
                        name                    = '".addslashes($this->name)."',
                        email                   = '".addslashes($this->email)."',
                        type                    = '".addslashes($this->type)."',
                        receive_notifications   = '".addslashes($this->receive_notifications)."',
                        last_update             = '$now',
                        last_activity           = '$now'
                    where
                        id_account              = '".addslashes($this->id_account)."'
                ";
            } # end if
            return mysql_query($query);
        }

    }
