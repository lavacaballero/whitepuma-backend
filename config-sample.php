<?php
    /**
     * Sample Backend Configuration file - it must be edited and saved as config.php
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

    class config
    {
        #===========================#
        var $frontend_enabled = true;
        #===========================#

        #=========================#
        # Local database settings #
        #=========================#

        # Set the next vars accordingly
        var $db_handler        = null; # Leave this one as null
        var $db_host           = "localhost";
        var $db_user           = "db_user";
        var $db_password       = "db_password";
        var $db_db             = "db_db";

        var $db_tables = array(
            "account"         => "wpos_account",
            "account_wallets" => "wpos_account_wallets",
        );

        #===============================================#
        # Communication with the private back-end hosts #
        #===============================================#

        var $daemon_operators = array(
            "provider_keyname" => array( # This must also be specified on the wallet endpoints
                "enabled"                   =>  true,

                "per_coin_data" => array(

                    # This is a coin sample with a multiplier factor. Notice that the coin name is different
                    # in the call to the wallets endpoint. That's because it is the same coin, but
                    # being represented as two on the frontend.
                    "BitcoinBITS"   => array(
                        "api_url"                    => "http://ip-or-host-for-wallet-endpoint/Bitcoin/",
                        "public_key"                 => "provider_keyname",                                 # As defined in the config file of the wallet
                        "secret_key"                 => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",                 # As defined in the config file of the wallet
                        "fees_account"               => "_txfees",                                          # As defined in the config file of the wallet
                        "incoming_amount_multiplier" => 0.00000100,                                         # Specific: incoming amounts are in bits (100 satoshis), we divide those to get BTC
                        "transaction_fee"            => 0.00000000,                                         # Not to be defined here
                        "min_transaction_amount"     => 0.00000100,                                         # As defined in the config file of the wallet
                        "system_transaction_fee"     => 0.00050000,                                         # As defined in the config file of the wallet
                        "withdraw_fee"               => 0.00000000,                                         # As defined in the config file of the wallet
                        "min_withdraw_amount"        => 0.00300000,                                         # As defined in the config file of the wallet
                    ),

                    # This is a coin sample with direct values (not multiplied)
                    "BitcoinBTC"   => array(
                        "api_url"                    => "http://ip-or-host-for-wallet-endpoint/Bitcoin/",
                        "public_key"                 => "provider_keyname",                                 # As defined in the config file of the wallet
                        "secret_key"                 => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",                 # As defined in the config file of the wallet
                        "fees_account"               => "_txfees",                                          # As defined in the config file of the wallet
                        "incoming_amount_multiplier" => 0         ,                                         # Not used on this case
                        "transaction_fee"            => 0.00000000,                                         # Not to be defined here
                        "min_transaction_amount"     => 0.00010000,                                         # As defined in the config file of the wallet
                        "system_transaction_fee"     => 0.00050000,                                         # As defined in the config file of the wallet
                        "withdraw_fee"               => 0.00000000,                                         # As defined in the config file of the wallet
                        "min_withdraw_amount"        => 0.00300000,                                         # As defined in the config file of the wallet
                    ),
                )
            )
        );

        #======================#
        # Who can access here? #
        #======================#

        var $clients_database = array(
            "client_keyname" => array( # This most correspond with the one specified in the front end
                "enabled"           => true,
                "name"              => "Client Name",
                "website"           => "http://www.client-domain.com",
                "public_key"        => "client_keyname",                      # The same as above
                "secret_key"        => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",    # The same to be set on the frontend
                "fees_account"      => "wpnet_fbtipbot",                      # Set by us (Tipping provider)
                "daemon_operator"   => "provider_keyname",                    # Taken from $daemon_operators above
                "can_register_here" => false,                                 # Not actually used
            ),
        );

        #=====================================#
        # Allowed actions coming from clients #
        #=====================================#

        var $allowed_actions = array(
            "register",             # <account>
            "get_address",          # <account>
            "get_balance",          # <account>
            "list_transactions",    # <account>
            "send",                 # <account> <target> <amount>
            "withdraw"              # <account> <target> <amount>
        );

    } # end class

    $config = new config();
