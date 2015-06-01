<?php
    /**
     * Encrypt/decrypt utility
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

    #############
    # Bootstrap #
    #############

    if( ! is_file("config.php") ) die("ERROR: config file not found.");
    include "config.php";
    include "functions.php";

    header("Content-type: text/html; charset=utf-8");

    if( $_POST["action"] == "encrypt" )
        $result = encryptRJ256(trim($_POST["enc_key"]), $_POST["enc_text"]);
    elseif( $_POST["action"] == "decrypt" )
        $result = decryptRJ256(trim($_POST["enc_key"]), $_POST["enc_text"]);
?>
<html>
    <head>
        <title>Encryption/decryption utility</title>
    </head>
    <body>
        <form method="post" action="<?=$PHP_SELF?>">
            <h1>Encryption/decryption utility</h1>

            <p>Encryption key:</p>
            <p><input type="text" name="enc_key" value="<?=htmlspecialchars(stripslashes($_POST["enc_key"]))?>" style="width: 100%;"></p>
            <p>
                <label><input type="radio" <? if($_POST["action"] == "encrypt") echo "checked"; ?> name="action" value="encrypt">Encrypt</label>
                <label><input type="radio" <? if($_POST["action"] == "decrypt") echo "checked"; ?> name="action" value="decrypt">Decrypt</label>
                Text:
            </p>
            <p><textarea name="enc_text" style="width: 100%;" rows="5"><?=htmlspecialchars(stripslashes($_POST["enc_text"]))?></textarea></p>
            <p>Result:</p>
            <p><textarea name="result" style="width: 100%;" rows="5"><?=htmlspecialchars($result)?></textarea></p>
            <p><button type="submit">Submit</button></p>
        </form>
    </body>
</html>
