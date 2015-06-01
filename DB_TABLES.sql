
-- ----------------------------
-- Table structure for wpos_account
-- ----------------------------
DROP TABLE IF EXISTS `wpos_account`;
CREATE TABLE `wpos_account` (
  `id_account` varchar(32) NOT NULL DEFAULT '',
  `facebook_id` varchar(32) NOT NULL DEFAULT '',
  `facebook_user_access_token` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `alternate_email` varchar(255) NOT NULL DEFAULT '',
  `alternate_password` varchar(255) NOT NULL DEFAULT '',
  `timezone` varchar(32) NOT NULL DEFAULT '',
  `tipping_provider` varchar(16) NOT NULL DEFAULT '',
  `wallet_address` varchar(64) NOT NULL DEFAULT '',
  `receive_notifications` enum('','true','false') NOT NULL DEFAULT 'true',
  `date_created` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `last_activity` datetime NOT NULL,
  PRIMARY KEY (`id_account`),
  KEY `facebook_id` (`facebook_id`),
  KEY `wallet_address` (`wallet_address`),
  KEY `alternate_login` (`alternate_email`,`alternate_password`),
  KEY `email` (`email`),
  KEY `alternate_email` (`alternate_email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for wpos_account_wallets
-- ----------------------------
DROP TABLE IF EXISTS `wpos_account_wallets`;
CREATE TABLE `wpos_account_wallets` (
  `id_account` varchar(32) NOT NULL DEFAULT '',
  `coin_name` varchar(64) NOT NULL DEFAULT 'Dogecoin',
  `address` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_account`,`coin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

