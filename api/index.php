<?php
    /**
     * API executive for clients
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
     *
     * @param           string $public_key  Client identifier
     * @param           string $coin_name
     * @param encrypted string $action      Action to do
     * @param encrypted string $account     User account identifier
     * @param encrypted string $target      When action is send or withdraw: Address that will receive funds
     * @param encrypted number $amount      When action is send or withdraw: Amount to transfer
     * @param encrypted string $is_fee      "true" or "false"
     *
     * @returns (json) { message: "message", data: (encrypted) (json) data }
     *
     * Output errors:
     *  • ERROR:PUBLIC_KEY_NOT_SPECIFIED
     *  • ERROR:COIN_NAME_NOT_SPECIFIED
     *  • ERROR:CLIENT_NOT_FOUND                 ~ Client's public key hasn't been found
     *  • ERROR:NULL_ACTION_SPECIFIED            ~ No action has been specified
     *  • ERROR:NULL_ACCOUNT_SPECIFIED           ~ No account to take coins from has been specified
     *  • ERROR:INVALID_ACTION                   ~ Action is not in the list of allowed actions
     *  • ERROR:NULL_TARGET_OR_AMOUNT_SPECIFIED  ~ On send or withdraw, target is empty or amount is empty
     *  • ERROR:TARGET_IS_NOT_WALLET_ADDRESS     ~ On withdraw, target is not a valid wallet address
     *  • ERROR:INVALID_AMOUNT                   ~ Amount is not "all" and is not numeric
     */

    #############
    # Bootstrap #
    #############

    header("Content-Type: application/json; charset=utf-8");
    if( ! is_file("../config.php") ) die("ERROR: config file not found.");
    include "../config.php";
    include "../functions.php";
    include "../models/client.php";
    include "../models/account.php";
    include "../models/daemon_operator_api_instance.php";

    if( trim($_REQUEST["public_key"]) == "" ) die(json_encode( (object) array("message" => "ERROR:PUBLIC_KEY_NOT_SPECIFIED") ));
    if( trim($_REQUEST["coin_name"])  == "" ) die(json_encode( (object) array("message" => "ERROR:COIN_NAME_NOT_SPECIFIED")  ));

    $_REQUEST["public_key"] = trim($_REQUEST["public_key"]);
    $_REQUEST["coin_name"]  = trim($_REQUEST["coin_name"]);

    $client = new client($_REQUEST["public_key"]);
    if( ! $client->exists ) die(json_encode( (object) array("message" => "ERROR:CLIENT_NOT_FOUND") ));

    ####################################
    # Params decryption and validation #
    ####################################

    $params = array();
    foreach( $_REQUEST as $key => $val )
    {
        if( $key != "public_key" && $key != "coin_name" )
            $params[$key] = decryptRJ256($client->secret_key, trim($val));
    } # end foreach
    $action = $params["action"]; unset($params["action"]);
    if( empty($action) )
        die(json_encode( (object) array("message" => "ERROR:NULL_ACTION_SPECIFIED") ));

    if( empty($params["account"]) )
        die(json_encode( (object) array("message" => "ERROR:NULL_ACCOUNT_SPECIFIED") ));

    if( ! in_array($action, $config->allowed_actions) )
        die(json_encode( (object) array("message" => "ERROR:INVALID_ACTION") ));

    if( $action == "send" && (empty($params["target"]) || empty($params["amount"])) )
        die(json_encode( (object) array("message" => "ERROR:NULL_TARGET_OR_AMOUNT_SPECIFIED") ));

    if( $action == "withdraw" && (empty($params["target"]) || empty($params["amount"])) )
        die(json_encode( (object) array("message" => "ERROR:NULL_TARGET_OR_AMOUNT_SPECIFIED") ));

    if( $action == "withdraw" && ! is_wallet_address($params["target"]) )
            die(json_encode( (object) array("message" => "ERROR:TARGET_IS_NOT_WALLET_ADDRESS") ));

    if( ! empty($params["amount"]) && ! (is_numeric($params["amount"]) || $params["amount"] == "all") )
        die(json_encode( (object) array("message" => "ERROR:INVALID_AMOUNT") ));

    ##########################################
    # Amount multiplier (for outgoing stuff) #
    ##########################################

    $multiplier = 1;
    if( ! empty($config->daemon_operators[$client->daemon_operator]["per_coin_data"][$_REQUEST["coin_name"]]["incoming_amount_multiplier"]) )
        $multiplier = $config->daemon_operators[$client->daemon_operator]["per_coin_data"][$_REQUEST["coin_name"]]["incoming_amount_multiplier"];

    #############
    # Execution #
    #############

    if( substr($params["account"], 0, 1) != "!" )
        $params["account"] = $client->client_id . "." . $params["account"];

    $outgoing_params = array();
    switch($action)
    {
        #===============
        case "register":
        #===============
            $outgoing_params = array(
                "command"       => "getnewaddress",
                "account"       => $params["account"]
            );
            break;
        #==================
        case "get_address":
        #==================
            $outgoing_params = array(
                "command"       => "getaccountaddress",
                "account"       => $params["account"]
            );
            break;
        #=================
        case "get_balance":
        #=================
            $outgoing_params = array(
                "command"       => "getbalance",
                "account"       => $params["account"]
            );
            break;
        #========================
        case "list_transactions":
        #========================
            $outgoing_params = array(
                "command"       => "listtransactions",
                "account"       => $params["account"]
            );
            break;
        #===========
        case "send":
        #===========
            $params["amount"] = $params["amount"] * $multiplier;
            $outgoing_params = array(
                "command"       => "move",
                "account"       => $params["account"],
                "target"        => $client->client_id . "." . $params["target"],
                "amount"        => $params["amount"],
                "is_withdraw"   => "false",
                "is_fee"        => $params["is_fee"]
            );
            break;
        #===============
        case "withdraw":
        #===============
            $params["amount"] = $params["amount"] * $multiplier;
            $outgoing_params = array(
                "command"       => "sendfrom",
                "account"       => $params["account"],
                "target"        => $params["target"],
                "amount"        => $params["amount"],
                "is_withdraw"   => "true"
            );
            break;
        #==========
        # end cases
        #==========
    } # end switch

    $daemon_instance = new daemon_operator_api_instance($client->daemon_operator, $_REQUEST["coin_name"]);
    $res = $daemon_instance->send_api_command($outgoing_params);

    ########################
    # Post-execution steps #
    ########################

    $account = new account($params["account"]);
    switch($action)
    {
        #===============
        case "register":
        #===============
            if( stristr($res->message, "error") === false )
            {
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            } # end if
            break;
        #==================
        case "get_address":
        #==================
            if( stristr($res->message, "error") === false )
            {
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            } # end if
            $account->set_wallet_address($client->daemon_operator, $res->data);
            break;
        #==================
        case "get_balance":
        #==================
            if( stristr($res->message, "error") === false )
            {
                $res->data = $res->data / $multiplier;
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            } # end if
            break;
        #========================
        case "list_transactions":
        #========================
            if( stristr($res->message, "error") === false )
            {
                if( is_array($res->data) )
                    foreach($res->data as $key => $data)
                    {
                        if( ! empty($data->amount) ) $res->data[$key]->amount = $res->data[$key]->amount / $multiplier;
                        if( ! empty($data->fee) )    $res->data[$key]->fee    = $res->data[$key]->fee    / $multiplier;
                    } # end foreach
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            } # end if
            break;
        #===========
        case "send":
        #===========
            if( stristr($res->message, "error") === false )
            {
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            }
            else
            {
                $account->ping();
            } # end if
            break;
        #===============
        case "withdraw":
        #===============
            if( stristr($res->message, "error") === false )
            {
                if( ! $account->_exists )
                {
                    $account->id_account = $params["account"];
                    $account->user_name  = $params["account"];
                    $account->password   = md5(mt_rand(1,65535));
                    $account->name       = $client->name . " user " . $account->id_account;
                    $account->type       = "user";
                    $account->save();
                } # end if
            }
            else
            {
                # $res->message .= ":" . $params["amount"];
                $account->ping();
            } # end if
            break;
        #==========
        # end cases
        #==========
    } # end switch

    if( $res->data && ! is_object($res->data) && ! is_array($res->data) )   $res->data = encryptRJ256($client->secret_key, $res->data);
    elseif( $res->data && (is_object($res->data) || is_array($res->data)) ) $res->data = encryptRJ256($client->secret_key, json_encode($res->data));
    echo json_encode($res);
