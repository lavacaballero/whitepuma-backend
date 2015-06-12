# WhitePuma Open Source Platform - Backend utility

This project is the middle layer of the platform. You need to put it in a
small host apart from the Frontend and the Wallets. No special considerations
have to be taken, since these scripts are a kind of reverse proxy to access
the Wallets API.

The scripts on this project are called only by the Frontend scripts. You may
want to secure the host to prevent DDOS attacks.

This API caches some of the wallet details in order to decrease the impact on the
wallet endpoint, which is a lot slower. So you will need to setup a MySQL database
and mount the included tables.

Please check the next repos for more information:

* [Wallets endpoint](https://github.com/lavacaballero/whitepuma-wallets):
  the keeper of your coins.
  
* [Platform Frontend](https://github.com/lavacaballero/whitepuma-frontend):
  the pages visited by the users and all scripts doing the sending/receiving
  job for the users.

## Requirements

* Apache 2 with rewrite module
* PHP 5.3+ with mcrypt, curl and zlib
* MySQL 5.5+

## Installation

1. Secure the host! You should allow SSH access only from specific IPs
   and use SSH keys instead of passwords.
   
4. Mount a firewall so port 80 is only accessible by the Frontend IP.
   Note that these scripts don't have an IP pseudo-checker for increased protection.

5. Install Apache, PHP and MySQL.

6. Create a user, a pass and a database to hold the data cache.

7. Rename the `.htaccess-sample` file to `.htaccess` and add the coin alias RewriteRule. Sample provided.

8. Rename the `config-sample.php` file to `config.php` and customize it.
   You should pay attention to the variables you're setting, since they must match
   the ones you've set on the wallet endpoint's config file.

9. Upload these scripts and configure a virtual host on Apache to serve the pages.
   **Note:** you must configure the *AllowOverride* directive to **all** on the vhost section.

10. Integrate the provided DB_TABLES.sql script.

11. Visit http://your.ip/api/CoinName/ and you will get a nasty "ERROR:PUBLIC_KEY_NOT_SPECIFIED" message.
    If so, then you're ready to start receiving requests from the Frontend.

## Usage

On the `config-sample.php` you will find all the info you need to set in order to permit communication with
from the frontend and allow this API to reach the wallet endpoint's API. 

You may never need to manually visit this API or add anything to it. It doesn't need any maintenance
since it only listens from the frontend's requests and forwards them to the wallet endpoint,
which returns data to be sent back to the frontend:

The Frontend script requests the user's balance to the Backend API with a POST request:

```
http://your.backend.ip/CoinName/?public_key=whatever_you_set&action=get_balance&account=account_id
```

The API here validates the info and forwards the request to the wallet endpoint with another POST request:

```
http://your.host.ip/CoinName/?public_key=whatever_you_set&action=get_balance&account=account_id
```

Then the Backend communicates with the wallet through a JSON RPC call
and returns either the data in an encrypted format or some error message.

## Contributing

**Maintainers are needed!** If you want to maintain this repository, feel free to let me know.
I don't have enough time to attend pull requests, so you're invited to be part of the core staff
behind this source.

## Credits

Author: Alejandro Caballero - acaballero@lavasoftworks.com

## License

This project is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This project is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
