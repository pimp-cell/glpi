<?php


/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2007 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Update from 0.68.1 to 0.7
function update0681to07() {
	global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE;

	@mysql_query("SET NAMES 'latin1'",$DB->dbh);

	// Improve user table :
	if (!isIndex("glpi_users", "firstname")) {
		$query = "ALTER TABLE `glpi_users` ADD INDEX ( `firstname` )";
		$DB->query($query) or die("0.7 alter users add indesx on firstname " . $LANG["update"][90] . $DB->error());
	}
	if (!isIndex("glpi_users", "realname")) {
		$query = "ALTER TABLE `glpi_users` ADD INDEX ( `realname` )";
		$DB->query($query) or die("0.7 alter users add indesx on realname " . $LANG["update"][90] . $DB->error());
	}
	// Decimal problem
	if (FieldExists("glpi_infocoms", "value")) {
		$query = "ALTER TABLE `glpi_infocoms` CHANGE `value` `value` DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter value in glpi_infocoms " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_infocoms", "warranty_value")) {
		$query = "ALTER TABLE `glpi_infocoms` CHANGE warranty_value warranty_value DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter warranty_value in glpi_infocoms " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_tracking", "cost_time")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_time cost_time DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_time in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_tracking", "cost_fixed")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_fixed cost_fixed DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_fixed in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_tracking", "cost_material")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE cost_material cost_material DECIMAL( 20, 4 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter cost_material in glpi_tracking " . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "decimal_number")) {
		$query = "ALTER TABLE `glpi_config` ADD `decimal_number` INT DEFAULT '2';";
		$DB->query($query) or die("0.7 add decimal_number in glpi_config " . $LANG["update"][90] . $DB->error());
	}
	$CFG_GLPI["decimal_number"] = 2;

	if (!FieldExists("glpi_config", "cas_logout")) {
		$query = "ALTER TABLE `glpi_config` ADD `cas_logout` VARCHAR( 255 ) NULL AFTER `cas_uri`;";
		$DB->query($query) or die("0.7 add cas_logout in glpi_config " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_computer_device", "specificity")) {
		$query = "ALTER TABLE `glpi_computer_device` ADD INDEX ( `specificity` )";
		$DB->query($query) or die("0.7 add index specificity in glpi_computer_device " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_docs", "comments")) {
		$query = "ALTER TABLE `glpi_docs` CHANGE `comment` `comments` TEXT DEFAULT NULL ";
		$DB->query($query) or die("0.7 alter docs.comment to be comments" . $LANG["update"][90] . $DB->error());

	}
	// Update polish langage file
	$query = "UPDATE glpi_users SET language='pl_PL' WHERE language='po_PO'";
	$DB->query($query) or die("0.7 update polish lang file " . $LANG["update"][90] . $DB->error());

	// Add show_group_hardware
	if (!FieldExists("glpi_profiles", "show_group_hardware")) {
		$query = "ALTER TABLE `glpi_profiles` ADD `show_group_hardware` CHAR( 1 ) NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter glpi_profiles add show_group_hardware" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE glpi_profiles SET `show_group_hardware`=`show_group_ticket`";
		$DB->query($query) or die("0.7 alter glpi_profiles add show_group_hardware" . $LANG["update"][90] . $DB->error());
	}

	// Clean doc association
	if (FieldExists("glpi_doc_device", "is_template")) {
		$query = "ALTER TABLE `glpi_doc_device` DROP `is_template`";
		$DB->query($query) or die("0.7 delete is_template from glpi_doc_device" . $LANG["update"][90] . $DB->error());
	}

	// Clean contract association
	if (FieldExists("glpi_contract_device", "is_template")) {
		$query = "ALTER TABLE `glpi_contract_device` DROP `is_template`";
		$DB->query($query) or die("0.7 delete is_template from glpi_contract_device" . $LANG["update"][90] . $DB->error());
	}
	 


	//// ENTITY MANAGEMENT

	if (!TableExists("glpi_entities")) {
		$query = "CREATE TABLE `glpi_entities` (
					`ID` int(11) NOT NULL auto_increment,
					`name` varchar(255) NOT NULL,
					`parentID` int(11) NOT NULL default '0',
					`completename` text NOT NULL,
					`comments` text,
					`level` int(11) default NULL,
					PRIMARY KEY  (`ID`),
					UNIQUE KEY `name` (`name`,`parentID`),
					KEY `parentID` (`parentID`)
			) ENGINE=MyISAM;";
		$DB->query($query) or die("0.7 create glpi_entities " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_entities_data")) {
		$query = "CREATE TABLE `glpi_entities_data` (
										`ID` int(11) NOT NULL auto_increment,
										`FK_entities` int(11) NOT NULL default '0',
										`address` text,
										`postcode` varchar(255) default NULL,
										`town` varchar(255) default NULL,
										`state` varchar(255) default NULL,
										`country` varchar(255) default NULL,
										`website` varchar(200) default NULL,
										`phonenumber` varchar(200) default NULL,
										`fax` varchar(255) default NULL,
										`email` varchar(255) default NULL,
										`notes` longtext,
										PRIMARY KEY  (`ID`),
										UNIQUE KEY `FK_entities` (`FK_entities`)
										) ENGINE=MyISAM ;";

		$DB->query($query) or die("0.7 create glpi_entities_data " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_users_profiles", "FK_entities")) {
		// Clean Datas
		$query = "DELETE FROM glpi_users_profiles WHERE FK_users='0'";
		$DB->query($query) or die("0.7 clean datas of glpi_users_profiles " . $LANG["update"][90] . $DB->error());

		$query = " ALTER TABLE `glpi_users_profiles` ADD `FK_entities` INT NOT NULL DEFAULT '0',
						ADD `recursive` SMALLINT NOT NULL DEFAULT '1',
						ADD `active` SMALLINT NOT NULL DEFAULT '1',
						ADD `dynamic` SMALLINT NOT NULL DEFAULT '0' ";
		$DB->query($query) or die("0.7 alter glpi_users_profiles " . $LANG["update"][90] . $DB->error());

		// Manage inactive users
		$query = "SELECT ID FROM glpi_users WHERE active='0'";
		$result = $DB->query($query);
		if ($DB->numrows($result)) {
			while ($data = $DB->fetch_array($result)) {
				$query2 = "UPDATE glpi_users_profiles SET active = '0' WHERE FK_users='" . $data['ID'] . "'";
				$DB->query($query2);
			}
		}

		$query = "ALTER TABLE `glpi_users` DROP `active` ";
		$DB->query($query) or die("0.7 drop active from glpi_users " . $LANG["update"][90] . $DB->error());

		$query = "DELETE FROM glpi_display WHERE type='" . USER_TYPE . "' AND num='8';";
		$DB->query($query) or die("0.7 delete active field items for user search " . $LANG["update"][90] . $DB->error());
	}

	// Add entity tags to tables
	$tables = array (
		"glpi_cartridges_type",
		"glpi_computers",
		"glpi_consumables_type",
		"glpi_contacts",
		"glpi_contracts",
		"glpi_docs",
		"glpi_dropdown_locations",
		"glpi_dropdown_netpoint",
		"glpi_enterprises",
		"glpi_groups",
		"glpi_monitors",
		"glpi_networking",
		"glpi_peripherals",
		"glpi_phones",
		"glpi_printers",
		"glpi_reminder",
		"glpi_software",
		"glpi_tracking"
	);
	// "glpi_kbitems","glpi_dropdown_kbcategories", -> easier to manage
	// "glpi_followups" -> always link to tracking ?
	// "glpi_licenses" -> always link to software ? 
	// "glpi_infocoms" -> always link to item ? PB on reports stats ?
	// "glpi_links" -> global items easier to manage
	// "glpi_reservation_item", "glpi_state_item" -> always link to item ? but info maybe needed
	foreach ($tables as $tbl) {
		if (!FieldExists($tbl, "FK_entities")) {
			$query = "ALTER TABLE `" . $tbl . "` ADD `FK_entities` INT NOT NULL DEFAULT '0' AFTER `ID`";
			$DB->query($query) or die("0.7 add FK_entities in $tbl " . $LANG["update"][90] . $DB->error());
		}

		if (!isIndex($tbl, "FK_entities")) {
			$query = "ALTER TABLE `" . $tbl . "` ADD INDEX (`FK_entities`)";
			$DB->query($query) or die("0.7 add index FK_entities in $tbl " . $LANG["update"][90] . $DB->error());
		}
	}

	// Regenerate Indexes :
	$tables = array (
		"glpi_dropdown_locations"
	);
	foreach ($tables as $tbl) {
		if (isIndex($tbl, "name")) {
			$query = "ALTER TABLE `$tbl` DROP INDEX `name`;";
			$DB->query($query) or die("0.7 drop index name in $tbl " . $LANG["update"][90] . $DB->error());
		}
		if (isIndex($tbl, "parentID_2")) {
			$query = "ALTER TABLE `$tbl` DROP INDEX `parentID_2`;";
			$DB->query($query) or die("0.7 drop index name in $tbl " . $LANG["update"][90] . $DB->error());
		}
		$query = "ALTER TABLE `$tbl` ADD UNIQUE(`name`,`parentID`,`FK_entities`);";
		$DB->query($query) or die("0.7 add index name in $tbl " . $LANG["update"][90] . $DB->error());

	}

	if (isIndex("glpi_users_profiles", "FK_users_profiles")) {
		$query = "ALTER TABLE `glpi_users_profiles` DROP INDEX `FK_users_profiles`;";
		$DB->query($query) or die("0.7 drop index FK_users_profiles in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_users_profiles", "active")) {
		$query = "ALTER TABLE `glpi_users_profiles` ADD INDEX (`active`);";
		$DB->query($query) or die("0.7 add index active in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}
	if (!isIndex("glpi_users_profiles", "FK_entities")) {
		$query = "ALTER TABLE `glpi_users_profiles` ADD INDEX (`FK_entities`);";
		$DB->query($query) or die("0.7 add index FK_entities in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_users_profiles", "recursive")) {
		$query = "ALTER TABLE `glpi_users_profiles` ADD INDEX (`recursive`);";
		$DB->query($query) or die("0.7 add index recursive in glpi_users_profiles " . $LANG["update"][90] . $DB->error());
	}

	//// MULTIAUTH MANAGEMENT

	if (!TableExists("glpi_auth_ldap")) {
		$query = "CREATE TABLE `glpi_auth_ldap` (
									 `ID` int(11) NOT NULL auto_increment,
									 `name` varchar(255) NOT NULL,
									 `ldap_host` varchar(255) default NULL,
									`ldap_basedn` varchar(255) default NULL,
									`ldap_rootdn` varchar(255) default NULL,
									`ldap_pass` varchar(255) default NULL,
									`ldap_port` varchar(255) NOT NULL default '389',
									`ldap_condition` varchar(255) default NULL,
									`ldap_login` varchar(255) NOT NULL default 'uid',	
									`ldap_use_tls` varchar(255) NOT NULL default '0',
									`ldap_field_group` varchar(255) default NULL,
									`ldap_group_condition` varchar(255) default NULL,
									`ldap_search_for_groups` int NOT NULL default '0',
									`ldap_field_group_member` varchar(255) default NULL,
									`ldap_field_email` varchar(255) default NULL,
									`ldap_field_realname` varchar(255) default NULL,
									`ldap_field_firstname` varchar(255) default NULL,
									`ldap_field_phone` varchar(255) default NULL,
									`ldap_field_phone2` varchar(255) default NULL,
									`ldap_field_mobile` varchar(255) default NULL,
									`ldap_field_comments` TEXT default NULL,		
									PRIMARY KEY  (`ID`)
								) ENGINE=MyISAM;";
		$DB->query($query) or die("0.7 create glpi_auth_ldap " . $LANG["update"][90] . $DB->error());

		$query = "select * from glpi_config WHERE ID=1";
		$result = $DB->query($query);
		$config = $DB->fetch_array($result);

		if (!empty ($config["ldap_host"])) {

			//Transfer ldap informations into the new table
			
			$query = "INSERT INTO `glpi_auth_ldap` VALUES 
			(NULL, '" . $config["ldap_host"] . "', '" . $config["ldap_host"] . "', '" . $config["ldap_basedn"] . "', '" . $config["ldap_rootdn"] . "', '" . $config["ldap_pass"] . "', " . $config["ldap_port"] . ", '" . $config["ldap_condition"] . "', '" . $config["ldap_login"] . "', '" . $config["ldap_use_tls"] . "', '" . $config["ldap_field_group"] . "',
			'" . $config["ldap_condition"] . "', " . $config["ldap_search_for_groups"] . ", '" . $config["ldap_field_group_member"] . "',
			'" . $config["ldap_field_email"] . "', '" . $config["ldap_field_realname"] . "', '" . $config["ldap_field_firstname"] . "',
			'" . $config["ldap_field_phone"] . "', '" . $config["ldap_field_phone2"] . "', '" . $config["ldap_field_mobile"] . "',NULL);";
			$DB->query($query) or die("0.7 transfert of ldap parameters into glpi_auth_ldap " . $LANG["update"][90] . $DB->error());
		}

		$query = "ALTER TABLE `glpi_config`
									DROP `ldap_field_email`,
									DROP `ldap_port`,
									DROP `ldap_host`,
									DROP `ldap_basedn`,
									DROP `ldap_rootdn`,
									DROP `ldap_pass`,
									DROP `ldap_field_location`,
									DROP `ldap_field_realname`,
									DROP `ldap_field_firstname`,
									DROP `ldap_field_phone`,
									DROP `ldap_field_phone2`,
									DROP `ldap_field_mobile`,
									DROP `ldap_condition`,
									DROP `ldap_login`,
									DROP `ldap_use_tls`,
									DROP `ldap_field_group`,
									DROP `ldap_group_condition`,
									DROP `ldap_search_for_groups`,
									DROP `ldap_field_group_member`;";
		$DB->query($query) or die("0.7 drop ldap fields from glpi_config " . $LANG["update"][90] . $DB->error());

	}
	if (!FieldExists("glpi_users", "id_auth")) {
		$query = "ALTER TABLE glpi_users ADD `id_auth` INT NOT NULL DEFAULT '-1',
										ADD `auth_method` INT NOT NULL DEFAULT '-1',
										ADD `last_login` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
										ADD `date_mod` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
		$DB->query($query) or die("0.7 add auth_method & id_method in glpi_users " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_auth_mail")) {
		$query = "CREATE TABLE `glpi_auth_mail` (
										`ID` int(11) NOT NULL auto_increment,
										`name` varchar(255) NOT NULL,
										`imap_auth_server` varchar(200) default NULL,
										`imap_host` varchar(200) default NULL,
										PRIMARY KEY  (`ID`)
										) ENGINE=MyISAM ;";

		$DB->query($query) or die("0.7 create glpi_auth_mail " . $LANG["update"][90] . $DB->error());

		$query = "select * from glpi_config WHERE ID=1";
		$result = $DB->query($query);
		$config = $DB->fetch_array($result);

		if (!empty ($config["imap_host"])) {

			//Transfer ldap informations into the new table
			$query = "INSERT INTO `glpi_auth_mail` VALUES 
													(NULL, '" . $config["imap_host"] . "', '" . $config["imap_auth_server"] . "', '" . $config["imap_host"] . "');";
			$DB->query($query) or die("0.7 transfert of mail parameters into glpi_auth_mail " . $LANG["update"][90] . $DB->error());

		}

		$query = "ALTER TABLE `glpi_config`
								  		DROP `imap_auth_server`,
								  		DROP `imap_host`";
		$DB->query($query) or die("0.7 drop mail fields from glpi_config " . $LANG["update"][90] . $DB->error());

	}

	// Clean state_item -> add a field from tables
	if (TableExists("glpi_state_item")) {
		$state_type = array (
			SOFTWARE_TYPE,
			COMPUTER_TYPE,
			PRINTER_TYPE,
			MONITOR_TYPE,
			PERIPHERAL_TYPE,
			NETWORKING_TYPE,
			PHONE_TYPE
		);
		foreach ($state_type as $type) {
			$table = $LINK_ID_TABLE[$type];
			if (!FieldExists($table, "state")) {
				$query = "ALTER TABLE `$table` ADD `state` INT NOT NULL DEFAULT '0';";
				$DB->query($query) or die("0.7 add state field to $table " . $LANG["update"][90] . $DB->error());
				$query2 = "SELECT * FROM glpi_state_item WHERE device_type='$type'";
				$result = $DB->query($query2);
				if ($DB->numrows($result)) {
					while ($data = $DB->fetch_array($result)) {
						$query3 = "UPDATE $table SET state='" . $data["state"] . "' WHERE ID ='" . $data["id_device"] . "'";
						$DB->query($query3) or die("0.7 update state field value to $table " . $LANG["update"][90] . $DB->error());
					}
				}
			}
		}
		$query = "DROP TABLE `glpi_state_item` ";
		$DB->query($query) or die("0.7 drop table state_item " . $LANG["update"][90] . $DB->error());
		$query = "INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES (22, 31, 1, 0);";
		$DB->query($query) or die("0.7 add default search for states " . $LANG["update"][90] . $DB->error());
		// Add for reservation
		$query = "INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES ( 29, 4, 1, 0);";
		$DB->query($query) or die("0.7 add defaul search for reservation " . $LANG["update"][90] . $DB->error());
		$query = "INSERT INTO `glpi_display` (`type`, `num`, `rank`, `FK_users`) VALUES ( 29, 3, 2, 0);";
		$DB->query($query) or die("0.7 add defaul search for reservation " . $LANG["update"][90] . $DB->error());
	}

	// Add ticket_tco for hardwares
	$tco_tbl = array (
		COMPUTER_TYPE,
		NETWORKING_TYPE,
		PRINTER_TYPE,
		MONITOR_TYPE,
		PERIPHERAL_TYPE,
		SOFTWARE_TYPE,
		PHONE_TYPE
	);
	include (GLPI_ROOT . "/inc/tracking.function.php");

	foreach ($tco_tbl as $type) {
		$table = $LINK_ID_TABLE[$type];
		if (!FieldExists($table, "ticket_tco")) {
			$query = "ALTER TABLE `$table` ADD `ticket_tco` DECIMAL( 20, 4 ) DEFAULT '0.0000';";
			$DB->query($query) or die("0.7 alter $table add ticket_tco" . $LANG["update"][90] . $DB->error());
			// Update values
			$query = "SELECT DISTINCT device_type, computer 
													FROM glpi_tracking 
													WHERE device_type = '$type' AND (cost_time>0 
														OR cost_fixed>0
														OR cost_material>0)";
			$result = $DB->query($query) or die("0.7 update ticket_tco" . $LANG["update"][90] . $DB->error());
			if ($DB->numrows($result)) {
				while ($data = $DB->fetch_array($result)) {
					$query2 = "UPDATE $table SET ticket_tco='" . computeTicketTco($type, $data["computer"]) . "' 
																					WHERE ID='" . $data["computer"] . "';";
					$DB->query($query2) or die("0.7 update ticket_tco" . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}
	if (!FieldExists("glpi_software", "helpdesk_visible")) {
		$query = "ALTER TABLE glpi_software ADD `helpdesk_visible` INT NOT NULL default '1'";
		$DB->query($query) or die("0.7 add helpdesk_visible in glpi_software " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_dropdown_manufacturer")) {

		$query = "CREATE TABLE `glpi_dropdown_manufacturer` (
								`ID` int(11) NOT NULL auto_increment,
								`name` varchar(255) NOT NULL,
								`comments` text,
								PRIMARY KEY  (`ID`),
								KEY `name` (`name`)
								) ENGINE=MyISAM ;";
		$DB->query($query) or die("0.7 add dropdown_manufacturer table " . $LANG["update"][90] . $DB->error());
	}
	if (countElementsInTable("glpi_dropdown_manufacturer")==0){

		// Fill table
		$query = "SELECT * FROM glpi_enterprises ORDER BY ID";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result)) {
				while ($data = $DB->fetch_assoc($result)) {
					$data = addslashes_deep($data);
					
					$comments = "";
					if (!empty ($data['address'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $LANG["financial"][44] . ":\n";
						$comments .= $data['address'];
					}
					if (!empty ($data['postcode']) || !empty ($data['town'])) {
						if (!empty ($comments))
							$comments .= $LANG["financial"][44] . ":\n";
						$comments .= $data['postcode'] . " " . $data['town'];
					}
					if (!empty ($data['state']) || !empty ($data['country'])) {
						if (!empty ($comments))
							$comments .= $LANG["financial"][44] . ":\n";
						$comments .= $data['country'] . " " . $data['state'];
					}
					if (!empty ($data['website'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $LANG["financial"][45] . ": ";
						$comments .= $data['website'];
					}
					if (!empty ($data['phonenumber'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $LANG["financial"][29] . ": ";
						$comments .= $data['phonenumber'];
					}
					if (!empty ($data['fax'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $LANG["financial"][30] . ": ";
						$comments .= $data['fax'];
					}
					if (!empty ($data['email'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $LANG["setup"][14] . ": ";
						$comments .= $data['email'];
					}
					if (!empty ($data['comments'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $data['comments'];
					}
					if (!empty ($data['notes'])) {
						if (!empty ($comments))
							$comments .= "\n";
						$comments .= $data['notes'];
					}

					$query2 = "INSERT INTO `glpi_dropdown_manufacturer` (ID,name,comments) VALUES ('" . $data['ID'] . "','" . $data['name'] . "','".$comments."')";
					$DB->query($query2) or die("0.7 add manufacturer item " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}

	if (isIndex("glpi_ocs_link", "ocs_id_2")) {
		$query = "ALTER TABLE `glpi_ocs_link` DROP INDEX `ocs_id_2` ";
		$DB->query($query) or die("0.7 alter ocs_link clean index ocs_id  " . $LANG["update"][90] . $DB->error());
	}

	if (isIndex("glpi_ocs_link", "ocs_id")) {
		$query = "ALTER TABLE `glpi_ocs_link` DROP INDEX `ocs_id` ";
		$DB->query($query) or die("0.7 alter ocs_link clean index ocs_id  " . $LANG["update"][90] . $DB->error());
	}

	 
	if (!FieldExists("glpi_ocs_link", "ocs_server_id")) {
		$query = "ALTER TABLE glpi_ocs_link ADD `ocs_server_id` int(11) NOT NULL";
		$DB->query($query) or die("0.7 add ocs_server_id in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
		$query = "update glpi_ocs_link set ocs_server_id=1";
		$DB->query($query) or die("0.7 update ocs_server_id=1 in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_ocs_link", "ocs_server_id")) {
		$query = "ALTER TABLE `glpi_ocs_link` ADD UNIQUE `ocs_server_id` (`ocs_server_id` ,`ocs_id`);";
		$DB->query($query) or die("0.7 alter ocs_link add index ocs_server_id " . $LANG["update"][90] . $DB->error());
	}

	if (!isIndex("glpi_ocs_link", "`ocs_deviceid`")) {
		$query = "ALTER TABLE `glpi_ocs_link` ADD INDEX ( `ocs_deviceid` )";
		$DB->query($query) or die("0.7 alter ocs_link add index ocs_deviceid " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "tplname")) {
		$query = "ALTER TABLE glpi_ocs_config ADD `name` varchar(200) default NULL AFTER `ID`, ADD `is_template` enum('0','1') NOT NULL default '0', ADD `tplname` varchar(200) default NULL, ADD `date_mod` datetime default NULL";
		$DB->query($query) or die("0.7 add name, is_template, tplname, date_mod in glpi_ocs_link " . $LANG["update"][90] . $DB->error());
		$query = "update glpi_ocs_config set name=ocs_db_host";
		$DB->query($query) or die("0.7 add name in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "import_registry")) {
		$query = "ALTER TABLE glpi_ocs_config ADD `import_registry` INT NOT NULL default '0' AFTER `import_device_modems`";
		$DB->query($query) or die("0.7 add import_registry in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_ocs_config", "import_tag_field")) {
		$query = "ALTER TABLE glpi_ocs_config DROP `import_tag_field`;";
		$DB->query($query) or die("0.7 drop import_tag_field in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "import_software_licensetype")) {
		$query = "ALTER TABLE glpi_ocs_config ADD `import_software_licensetype` VARCHAR(255) DEFAULT 'global' AFTER `import_software`";
		$DB->query($query) or die("0.7 add import_software_licensetype in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "import_software_buy")) {
		$query = "ALTER TABLE glpi_ocs_config ADD `import_software_buy` INT NOT NULL DEFAULT '1' AFTER `import_software`";
		$DB->query($query) or die("0.7 add import_software_buy in glpi_ocs_config " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_registry")) {
		$query = "CREATE TABLE  `glpi_registry` (
						 				`ID` int(10) NOT NULL auto_increment,
						 				`computer_id` int(10) NOT NULL DEFAULT '0',
						 				`registry_hive` varchar(45) NOT NULL,
						 				`registry_path` varchar(255) NOT NULL,
						 				`registry_value` varchar(255) NOT NULL,
						 				PRIMARY KEY  (`ID`),
										KEY `computer_id` (`computer_id`)
										) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
		$DB->query($query) or die("0.7 add glpi_registry table " . $LANG["update"][90] . $DB->error());

	}

	if (!FieldExists("glpi_ocs_link", "import_ip")) {
		$query = "ALTER TABLE `glpi_ocs_link` ADD COLUMN `import_ip` LONGTEXT";
		$DB->query($query) or die("0.7 add import_ip in glpi_ocs_link" . $LANG["update"][90] . $DB->error());
	}


	//// Enum clean
	// Enum 0-1
	$enum01 = array ();
	$template_tables = array (
		"glpi_computers",
		"glpi_networking",
		"glpi_printers",
		"glpi_monitors",
		"glpi_peripherals",
		"glpi_software",
		"glpi_phones",
		"state_types",
		"reservation_types",
		"glpi_ocs_config"
	);

	foreach ($template_tables as $table) {
		if (!isset ($enum01[$table])) {
			$enum01[$table] = array ();
		}
		$enum01[$table][] = "is_template";
	}
	$enum01["glpi_config"][] = "auto_assign";
	$enum01["glpi_config"][] = "public_faq";
	$enum01["glpi_config"][] = "url_in_mail";
	$enum01["glpi_profiles"][] = "is_default";

	$enum01["glpi_monitors"][] = "is_global";
	$enum01["glpi_peripherals"][] = "is_global";
	$enum01["glpi_phones"][] = "is_global";
	$enum01["glpi_printers"][] = "is_global";
	$enum01["glpi_reminder"][] = "rv";
	$enum01["glpi_contract_device"][] = "is_template";
	$enum01["glpi_doc_device"][] = "is_template";

	foreach ($enum01 as $table => $fields) {
		foreach ($fields as $key => $field) {
			if (FieldExists($table, $field)) {

				$query = "ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '0' AFTER `$field` ";
				$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

				$query = "UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='1';";
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
				$query = "UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='0';";
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

				$query = "ALTER TABLE `$table` DROP `$field` ";
				$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

				$query = "ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90] . $DB->error());

				if ($table != "glpi_config" && $table != "glpi_profiles") {
					$query = "ALTER TABLE `$table` ADD KEY (`$field`)";
					$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}

	$enumYN["N"]["glpi_contracts"][] = "monday"; // N
	$enumYN["N"]["glpi_contracts"][] = "saturday"; // N
	$enumYN["Y"]["glpi_device_drive"][] = "is_writer"; // Y 
	$enumYN["N"]["glpi_device_control"][] = "raid"; // Y -> N
	$enumYN["Y"]["glpi_device_power"][] = "atx"; // Y
	$enumYN["N"]["glpi_licenses"][] = "oem"; // N
	$enumYN["Y"]["glpi_licenses"][] = "buy"; // Y
	$enumYN["N"]["glpi_software"][] = "is_update"; // N
	$enumYN["Y"]["glpi_type_docs"][] = "upload"; // Y

	$deleted_tables = array (
		"glpi_computers",
		"glpi_networking",
		"glpi_printers",
		"glpi_monitors",
		"glpi_peripherals",
		"glpi_software",
		"glpi_cartridges_type",
		"glpi_contracts",
		"glpi_contacts",
		"glpi_enterprises",
		"glpi_docs",
		"glpi_phones",
		"glpi_consumables_type"
	);

	foreach ($deleted_tables as $table) {
		if (!isset ($enum01[$table])) {
			$enum01[$table] = array ();
		}
		$enumYN["N"][$table][] = "deleted";
	}

	foreach ($enumYN as $default => $tmptbl)
		foreach ($tmptbl as $table => $fields) {
			foreach ($fields as $key => $field) {
				if (FieldExists($table, $field)) {

					$newdef = 0;
					if ($default == "Y") {
						$newdef = 1;
					}

					$query = "ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '$newdef' AFTER `$field` ";
					$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

					$query = "UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='Y';";
					$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
					$query = "UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='N';";
					$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

					$query = "ALTER TABLE `$table` DROP `$field` ";
					$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

					$query = "ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '$newdef'";
					$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90] . $DB->error());

					if ($field == "deleted" || $table == "glpi_licenses" || $table == "glpi_software" || $table == "glpi_type_docs") {
						$query = "ALTER TABLE `$table` ADD KEY (`$field`)";
						$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
					}
				}
			}
		}

	if (FieldExists("glpi_tracking", "is_group")) {
		$query = "ALTER TABLE glpi_tracking DROP `is_group`";
		$DB->query($query) or die("0.7 drop is_group from tracking " . $LANG["update"][90] . $DB->error());
	}

	$enumYesNo["glpi_kbitems"][] = "faq";
	$enumYesNo["glpi_tracking"][] = "emailupdates";
	$enumYesNo["glpi_users"][] = "tracking_order";

	foreach ($enumYesNo as $table => $fields) {
		foreach ($fields as $key => $field) {
			if (FieldExists($table, $field)) {

				$query = "ALTER TABLE `$table` ADD `tmp_convert_enum` SMALLINT NOT NULL DEFAULT '0' AFTER `$field` ";
				$DB->query($query) or die("0.7 alter $table add new field tmp_convert_enum " . $LANG["update"][90] . $DB->error());

				$query = "UPDATE `$table` SET tmp_convert_enum='1' WHERE $field='yes';";
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());
				$query = "UPDATE `$table` SET tmp_convert_enum='0' WHERE $field='no';";
				$DB->query($query) or die("0.7 update $table to set correct values to alod enum01 $field " . $LANG["update"][90] . $DB->error());

				$query = "ALTER TABLE `$table` DROP `$field` ";
				$DB->query($query) or die("0.7 alter $table drop tmp enum field " . $LANG["update"][90] . $DB->error());

				$query = "ALTER TABLE `$table` CHANGE `tmp_convert_enum` `$field` SMALLINT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 alter $table move enum $field to tmp field " . $LANG["update"][90] . $DB->error());

				if ($table == "glpi_kbitems") {
					$query = "ALTER TABLE `$table` ADD KEY (`$field`)";
					$DB->query($query) or die("0.7 alter $table add deleted key " . $LANG["update"][90] . $DB->error());
				}
			}
		}
	}
	// Reste enum : glpi_tracking.status et glpi_device_gfxcard.interface
	if (FieldExists("glpi_tracking", "status")) {
		$query = "ALTER TABLE `glpi_tracking` CHANGE `status` `status` VARCHAR( 255 ) DEFAULT 'new'";
		$DB->query($query) or die("0.7 alter status from tracking " . $LANG["update"][90] . $DB->error());
	}

	if (FieldExists("glpi_device_gfxcard", "interface")) {
		$query = "ALTER TABLE `glpi_device_gfxcard` CHANGE `interface` `interface` VARCHAR( 255 ) NULL DEFAULT 'PCI-X'";
		$DB->query($query) or die("0.7 alter interface from glpi_device_gfxcard " . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_rules_actions")) {
		$query = "CREATE TABLE `glpi_rules_actions` (
						  `ID` int(11) NOT NULL auto_increment,
						  `FK_rules` int(11) NOT NULL DEFAULT '0',
						  `action_type` varchar(255) NOT NULL,
						  `field` varchar(255) NOT NULL,
						  `value` varchar(255) NOT NULL,
						  PRIMARY KEY  (`ID`),
						  KEY `FK_rules` (`FK_rules`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
						";
		$DB->query($query) or die("0.7 add table glpi_rules_descriptions" . $LANG["update"][90] . $DB->error());

	}

	if (!TableExists("glpi_rules_criterias")) {
		$query = "CREATE TABLE `glpi_rules_criterias` (
				  `ID` int(11) NOT NULL auto_increment,
				  `FK_rules` int(11) NOT NULL DEFAULT '0',
				  `criteria` varchar(255) NOT NULL,
				  `condition` smallint(4) NOT NULL DEFAULT '0',
				  `pattern` varchar(255) NOT NULL,
				  PRIMARY KEY  (`ID`),
				KEY `FK_rules` (`FK_rules`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$DB->query($query) or die("0.7 add table glpi_rules_criterias" . $LANG["update"][90] . $DB->error());

	}

	if (!TableExists("glpi_rules_descriptions")) {
		$query = "CREATE TABLE `glpi_rules_descriptions` (
		  `ID` int(11) NOT NULL auto_increment,
		  `FK_entities` int(11) NOT NULL default '-1',
		  `rule_type` smallint(4) NOT NULL DEFAULT '0',
		  `ranking` int(11) NOT NULL DEFAULT '0',
		  `name` varchar(255) NOT NULL,
		  `description` text NOT NULL,
		  `match` varchar(255) NOT NULL,
		  PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$DB->query($query) or die("0.7 add table glpi_rules_actions" . $LANG["update"][90] . $DB->error());

	}

	if (!FieldExists("glpi_config", "use_cache")) {
		$query = "ALTER TABLE `glpi_config` ADD `use_cache` SMALLINT NOT NULL DEFAULT '1' AFTER `debug` ;";
		$DB->query($query) or die("0.7 alter config add use_cache " . $LANG["update"][90] . $DB->error());
	}

	if (TableExists("glpi_rules_descriptions")) {
		//If no rule exists, then create a default one
		$query = "SELECT ID from glpi_rules_descriptions";
		$result = $DB->query($query);
		if ($DB->numrows($result) ==0)
		{
			//Insert rule to affect machines in the Root entity
			$query ="INSERT INTO `glpi_rules_descriptions` (`FK_entities`, `rule_type`, `ranking`, `name`, `description`, `match`) VALUES (-1, 0, 0, 'Root', '', 'AND');";
			$DB->query($query) or die("0.7 add default ocs affectation rule" . $LANG["update"][90] . $DB->error());
		
			$query = "SELECT ID from glpi_rules_descriptions WHERE name='Root' AND rule_type=".RULE_OCS_AFFECT_COMPUTER;
			$result = $DB->query($query);
			//Get the defaut rule's ID
			$datas = $DB->fetch_array($result);
	
			$query="INSERT INTO `glpi_rules_criterias` (`FK_rules`, `criteria`, `condition`, `pattern`) VALUES (".$datas["ID"].", 'TAG', 0, '*');";
			$DB->query($query) or die("0.7 add default ocs criterias" . $LANG["update"][90] . $DB->error());
	
			$query="INSERT INTO `glpi_rules_actions` (`FK_rules`, `action_type`, `field`, `value`) VALUES (".$datas["ID"].", 'assign', 'FK_entities', '0');";
			$DB->query($query) or die("0.7 add default ocs actions" . $LANG["update"][90] . $DB->error());

			//Insert rule to affect users from LDAP to the root entity
			$query ="INSERT INTO `glpi_rules_descriptions` (`FK_entities`, `rule_type`, `ranking`, `name`, `description`, `match`) VALUES (-1, ".RULE_AFFECT_RIGHTS.", 1, 'Root', '', 'OR');";
			$DB->query($query) or die("0.7 add default right affectation rule" . $LANG["update"][90] . $DB->error());

			$query = "SELECT ID from glpi_rules_descriptions WHERE name='Root' AND rule_type=".RULE_AFFECT_RIGHTS;
			$result = $DB->query($query);
			//Get the defaut rule's ID
			$datas = $DB->fetch_array($result);
	
			//Criterias
			$query="INSERT INTO `glpi_rules_criterias` (`FK_rules`, `criteria`, `condition`, `pattern`) VALUES (".$datas["ID"].", 'uid', 0, '*');";
			$DB->query($query) or die("0.7 add default right criterias" . $LANG["update"][90] . $DB->error());

			$query="INSERT INTO `glpi_rules_criterias` (`FK_rules`, `criteria`, `condition`, `pattern`) VALUES (".$datas["ID"].", 'samaccountname', 0, '*');";
			$DB->query($query) or die("0.7 add default right criterias" . $LANG["update"][90] . $DB->error());

			$query="INSERT INTO `glpi_rules_criterias` (`FK_rules`, `criteria`, `condition`, `pattern`) VALUES (".$datas["ID"].", 'MAIL_EMAIL', 0, '*');";
			$DB->query($query) or die("0.7 add default right criterias" . $LANG["update"][90] . $DB->error());
	
			//Action
			$query="INSERT INTO `glpi_rules_actions` (`FK_rules`, `action_type`, `field`, `value`) VALUES (".$datas["ID"].", 'assign', 'FK_entities', '0');";
			$DB->query($query) or die("0.7 add default right actions" . $LANG["update"][90] . $DB->error());

		}
	}
	
	if (!TableExists("glpi_ocs_admin_link")){
			$query = "CREATE TABLE `glpi_ocs_admin_link` (
  			`ID` int(10) unsigned NOT NULL auto_increment,
  			`glpi_column` varchar(255) NULL,
  			`ocs_column` varchar(255) NULL,
  			`ocs_server_id` int(11) NOT NULL,
  			PRIMARY KEY  (`ID`)
			) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
			$DB->query($query) or die("0.7 add table glpi_ocs_admin_link" . $LANG["update"][90] . $DB->error());
	}

	// Add title to tracking
	if (!FieldExists("glpi_tracking", "name")) {
		$query = "ALTER TABLE `glpi_tracking` ADD `name` varchar(255) NULL AFTER `FK_entities`";
		$DB->query($query) or die("0.7 alter tracking add name" . $LANG["update"][90] . $DB->error());
		$query="UPDATE glpi_tracking SET name=SUBSTRING(REPLACE(contents,'\n',' '),1,50);";
		$DB->query($query) or die("0.7 update title of glpi_tracking" . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_reminder", "title")) {
		$query = "ALTER TABLE `glpi_reminder` CHANGE `title` `title` VARCHAR( 255 ) NULL DEFAULT NULL ";
		$DB->query($query) or die("0.7 alter title in glpi_reminder" . $LANG["update"][90] . $DB->error());
	}

	if (!TableExists("glpi_rules_ldap_parameters")){
			$query = "CREATE TABLE `glpi_rules_ldap_parameters` (
			  `ID` int(11) NOT NULL auto_increment,
  			  `name` varchar(255) NOT NULL,
  			  `value` varchar(255) NOT NULL,
  			  `rule_type` smallint(6) NOT NULL default '1',
  			   PRIMARY KEY  (`ID`)
			  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
			$DB->query($query) or die("0.7 add table glpi_rules_ldap_parameters" . $LANG["update"][90] . $DB->error());
	
		$query = "INSERT INTO `glpi_rules_ldap_parameters` (`ID`, `name`, `value`, `rule_type`) VALUES 
					(1, '(LDAP)Organization', 'o', 1),
					(2, '(LDAP)Common Name', 'cn', 1),
					(3, '(LDAP)Department Number', 'departmentnumber', 1),
					(4, '(LDAP)Email', 'mail', 1),
					(5, 'Object Class', 'objectclass', 1),		
					(6, '(LDAP)User ID', 'uid', 1),
					(7, '(LDAP)Telephone Number', 'phone', 1),
					(8, '(LDAP)Employee Number', 'employeenumber', 1),
					(9, '(LDAP)Manager', 'manager', 1),
					(10, '(LDAP)DistinguishedName', 'dn', 1),
					(11, '(AD)DistinguishedName', 'distinguishedname', 1),
					(12, '(AD)User ID', 'samaccountname', 1);";
					
		$DB->query($query) or die("0.7 add standard values to glpi_rules_ldap_parameters " . $LANG["update"][90] . $DB->error());
	
	}

	if (!FieldExists("glpi_config", "helpdeskhelp_url")) {
		$query = "ALTER TABLE `glpi_config` ADD `helpdeskhelp_url` VARCHAR( 255 ) NULL DEFAULT NULL ";
		$DB->query($query) or die("0.7 add helpdeskhelp_url in glpi_config" . $LANG["update"][90] . $DB->error());
	}	
	if (!FieldExists("glpi_config", "centralhelp_url")) {
		$query = "ALTER TABLE `glpi_config` ADD `centralhelp_url` VARCHAR( 255 ) NULL DEFAULT NULL ";
		$DB->query($query) or die("0.7 add centralhelp_url in glpi_config" . $LANG["update"][90] . $DB->error());
	}	

	if (!FieldExists("glpi_config", "default_rubdoc_tracking")) {
		$query = "ALTER TABLE `glpi_config` ADD `default_rubdoc_tracking` int(11) default '0' ";
		$DB->query($query) or die("0.7 add default_rubdoc_tracking in glpi_config" . $LANG["update"][90] . $DB->error());
	}	

	if (!FieldExists("glpi_users", "deleted")) {
		$query = "ALTER TABLE `glpi_users` ADD `deleted` SMALLINT NOT NULL DEFAULT 0 ";
		$DB->query($query) or die("0.7 add deleted in glpi_users" . $LANG["update"][90] . $DB->error());
		$query = "ALTER TABLE `glpi_users` ADD KEY (`deleted`)";
		$DB->query($query) or die("0.7 add key deleted in glpi_users" . $LANG["update"][90] . $DB->error());
	}	

	if (!FieldExists("glpi_reservation_item", "active")) {
		$query = "ALTER TABLE `glpi_reservation_item` ADD `active` smallint(6) NOT NULL default '1' ";
		$DB->query($query) or die("0.7 add active in glpi_reservation_item" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_tracking_planning", "state")) {
		$query = "ALTER TABLE `glpi_tracking_planning` ADD `state` smallint(6) NOT NULL default '1' ";
		$DB->query($query) or die("0.7 add state in glpi_tracking_planning" . $LANG["update"][90] . $DB->error());
		$query="UPDATE `glpi_tracking_planning` SET state='2' WHERE end < NOW()";
		$DB->query($query) or die("0.7 update values of state in glpi_tracking_planning" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_reminder", "state")) {
		$query = "ALTER TABLE `glpi_reminder` ADD `state` smallint(6) NOT NULL default '0' ";
		$DB->query($query) or die("0.7 add state in glpi_reminder" . $LANG["update"][90] . $DB->error());
	}


	if (!FieldExists("glpi_tracking", "recipient")) {
		$query = "ALTER TABLE `glpi_tracking` ADD `recipient` INT NOT NULL DEFAULT '0' AFTER `author` ";
		$DB->query($query) or die("0.7 add recipient in glpi_tracking" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_tracking` SET recipient = author";
		$DB->query($query) or die("0.7 update recipient in glpi_tracking" . $LANG["update"][90] . $DB->error());
		
	}
	if (!isIndex("glpi_tracking","recipient")){
		$query="ALTER TABLE `glpi_tracking` ADD INDEX ( `recipient` ) ";
		$DB->query($query) or die("0.7 add recipient index in glpi_tracking" . $LANG["update"][90] . $DB->error());
	}
	
	if (!FieldExists("glpi_ocs_config", "deconnection_behavior")) {
		$query = "ALTER TABLE `glpi_ocs_config` ADD COLUMN `deconnection_behavior` VARCHAR(45)";
		$DB->query($query) or die("0.7 add state in glpi_reminder" . $LANG["update"][90] . $DB->error());
	}

	// Rights
	if (!FieldExists("glpi_profiles", "search_config_global")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `search_config_global` char(1) default NULL AFTER `search_config`";
		$DB->query($query) or die("0.7 add search_config_global in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `search_config_global` = search_config";
		$DB->query($query) or die("0.7 update search_config_global values in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `search_config` = 'w' WHERE interface='central'";
		$DB->query($query) or die("0.7 update search_confi values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}	
	if (!FieldExists("glpi_profiles", "entity")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `entity` char(1) default NULL AFTER `group`";
		$DB->query($query) or die("0.7 add entity in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `entity` = config";
		$DB->query($query) or die("0.7 update entity values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "entity_dropdown")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `entity_dropdown` char(1) default NULL AFTER `dropdown`";
		$DB->query($query) or die("0.7 add entity_dropdown in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `entity_dropdown` = dropdown";
		$DB->query($query) or die("0.7 update entity_dropdown values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "sync_ocsng")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `sync_ocsng` char(1) default NULL AFTER `ocsng`";
		$DB->query($query) or die("0.7 add sync_ocsng in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `sync_ocsng` = ocsng";
		$DB->query($query) or die("0.7 update sync_ocsng values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "view_ocsng")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `view_ocsng` char(1) default NULL AFTER `ocsng`";
		$DB->query($query) or die("0.7 add view_ocsng in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `view_ocsng` = 'r' WHERE interface='central'";
		$DB->query($query) or die("0.7 update view_ocsng values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "rule_ldap")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `rule_ldap` char(1) default NULL AFTER `config`";
		$DB->query($query) or die("0.7 add rule_ldap in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `rule_ldap` = config";
		$DB->query($query) or die("0.7 update rule_ldap values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "rule_ocs")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `rule_ocs` char(1) default NULL AFTER `config`";
		$DB->query($query) or die("0.7 add rule_ocs in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `rule_ocs` = config";
		$DB->query($query) or die("0.7 update rule_ocs values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_profiles", "rule_tracking")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `rule_tracking` char(1) default NULL AFTER `config`";
		$DB->query($query) or die("0.7 add rule_tracking in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `rule_tracking` = config";
		$DB->query($query) or die("0.7 update rule_tracking values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}


	//Software version's modifications
	//First add the version field to the licenses table	
	if (!FieldExists("glpi_licenses", "version")) {
		$query = "ALTER TABLE `glpi_licenses` ADD COLUMN `version` varchar(255) default NULL AFTER `sID`";
		$DB->query($query) or die("0.7 add version in glpi_licenses" . $LANG["update"][90] . $DB->error());

		$sql = "SELECT ID, version FROM glpi_software";
		$result = $DB->query($sql);
		if ($DB->numrows($result)>0)
		{
			while ($soft = $DB->fetch_array($result))
			{
				$sql = "UPDATE glpi_licenses SET version='".$soft["version"]."' WHERE sID=".$soft["ID"]; 
				$DB->query($sql);
			}
		}

	}
	
	
	if (FieldExists("glpi_software", "version")) {
		$query = "ALTER TABLE `glpi_software` DROP `version`";
		$DB->query($query) or die("0.7 delete version in glpi_software" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_networking_ports", "netmask")) {
		$query = "ALTER TABLE `glpi_networking_ports` ADD COLUMN `netmask` VARCHAR( 255 ) NULL DEFAULT NULL";
		$DB->query($query) or die("0.7 add netmask in glpi_networking_ports" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_networking_ports", "gateway")) {
		$query = "ALTER TABLE `glpi_networking_ports` ADD COLUMN `gateway` VARCHAR( 255 ) NULL DEFAULT NULL";
		$DB->query($query) or die("0.7 add gateway in glpi_networking_ports" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_networking_ports", "subnet")) {
		$query = "ALTER TABLE `glpi_networking_ports` ADD COLUMN subnet VARCHAR( 255 ) NULL DEFAULT NULL";
		$DB->query($query) or die("0.7 add subnet in glpi_networking_ports" . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_networking_ports", "name")) {
		$query = "ALTER TABLE `glpi_networking_ports` CHANGE `name` `name` VARCHAR( 255 ) NULL DEFAULT NULL ,
				CHANGE `ifaddr` `ifaddr` VARCHAR( 255 ) NULL DEFAULT NULL ,
				CHANGE `ifmac` `ifmac` VARCHAR( 255 ) NULL DEFAULT NULL ";
		$DB->query($query) or die("0.7 alter networking_ports fields" . $LANG["update"][90] . $DB->error());
	}
	// mailgate
	if (!TableExists("glpi_mailgate")) {
		$query = "CREATE TABLE `glpi_mailgate` (
		`ID` int(11) NOT NULL auto_increment,
		`name` varchar(255) collate utf8_unicode_ci default NULL,
		`FK_entities` int(11) NOT NULL default '0',
		`host` varchar(255) collate utf8_unicode_ci NOT NULL,
		`login` varchar(255) collate utf8_unicode_ci NOT NULL,
		`password` varchar(255) collate utf8_unicode_ci NOT NULL,
		PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM ;";
		$DB->query($query) or die("0.7 add glpi_mailgate" . $LANG["update"][90] . $DB->error());
		$query = "INSERT INTO `glpi_display` ( `type`, `num`, `rank`, `FK_users`) VALUES (35, 80, 1, 0);";
		$DB->query($query) or die("0.7 add glpi_mailgate display values" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_computers", "os_license_number")) {
		$query = "ALTER TABLE `glpi_computers` ADD COLUMN `os_license_number` VARCHAR( 255 ) NULL DEFAULT NULL AFTER os_sp;";
		$DB->query($query) or die("0.7 alter glpi_computers field" . $LANG["computers"][10] . $DB->error());
	}

	if (!FieldExists("glpi_computers", "os_license_id")) {
		$query = "ALTER TABLE `glpi_computers` ADD COLUMN `os_license_id` VARCHAR( 255 ) NULL DEFAULT NULL AFTER os_license_number;";
		$DB->query($query) or die("0.7 alter glpi_computers field" . $LANG["computers"][10] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "import_os_serial")) {
		$query = "ALTER TABLE `glpi_ocs_config` ADD `import_os_serial` INT( 2 ) NULL AFTER `import_registry` ;";
		$DB->query($query) or die("0.7 alter glpi_computers field import_ocs_serial " . $DB->error());
	}

	if (!FieldExists("glpi_auth_ldap", "use_dn")) {
		$query = "ALTER TABLE `glpi_auth_ldap` ADD `use_dn` INT( 1 ) NOT NULL DEFAULT '1';";
		$DB->query($query) or die("0.7 alter glpi_computers field use_dn " . $DB->error());
	}

	if (!FieldExists("glpi_config", "monitors_management_restrict")) {
		$query = "ALTER TABLE `glpi_config` ADD `monitors_management_restrict` INT( 1 ) NOT NULL DEFAULT '2';";
		$DB->query($query) or die("0.7 alter glpi_computers field monitors_management_restrict " . $DB->error());
	}

	if (!FieldExists("glpi_config", "phones_management_restrict")) {
		$query = "ALTER TABLE `glpi_config` ADD `phones_management_restrict` INT( 1 ) NOT NULL DEFAULT '2';";
		$DB->query($query) or die("0.7 alter glpi_computers field phones_management_restrict " . $DB->error());
	}

	if (!FieldExists("glpi_config", "peripherals_management_restrict")) {
		$query = "ALTER TABLE `glpi_config` ADD `peripherals_management_restrict` INT( 1 ) NOT NULL DEFAULT '2';";
		$DB->query($query) or die("0.7 alter glpi_computers field peripherals_management_restrict " . $DB->error());
	}
	
	if (!FieldExists("glpi_config", "printers_management_restrict")) {
		$query = "ALTER TABLE `glpi_config` ADD `printers_management_restrict` INT( 1 ) NOT NULL DEFAULT '2';";
		$DB->query($query) or die("0.7 alter glpi_computers field printers_management_restrict " . $DB->error());
	}

	if (!FieldExists("glpi_config", "licenses_management_restrict")) {
		$query = "ALTER TABLE `glpi_config` ADD `licenses_management_restrict` INT( 1 ) NOT NULL DEFAULT '2';";
		$DB->query($query) or die("0.7 alter glpi_computers field licenses_management_restrict " . $DB->error());
	}

	if (!FieldExists("glpi_config", "license_deglobalisation")) {
		$query = "ALTER TABLE `glpi_config` ADD `license_deglobalisation` INT( 1 ) NOT NULL DEFAULT '1';";
		$DB->query($query) or die("0.7 alter glpi_computers field license_deglobalisation " . $DB->error());
	}

	if (!FieldExists("glpi_registry", "registry_ocs_name")) {
		$query = "ALTER TABLE `glpi_registry` ADD COLUMN `registry_ocs_name` char(255) NOT NULL default ''";
		$DB->query($query) or die("0.7 add registry_ocs_name in glpi_registry" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_config", "use_errorlog")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `use_errorlog` INT( 1 ) NOT NULL default 0";
		$DB->query($query) or die("0.7 add use_errorlog in glpi_config" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_config", "glpi_timezone")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `glpi_timezone` VARCHAR( 4 ) NOT NULL default 0";
		$DB->query($query) or die("0.7 add glpi_timezone in glpi_config" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_auth_ldap", "timezone")) {
		$query = "ALTER TABLE `glpi_auth_ldap` ADD COLUMN `timezone` VARCHAR( 4 ) NOT NULL default 0";
		$DB->query($query) or die("0.7 add timezone in glpi_auth_ldap" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config","glpi_link_enabled"))
	{
		$query="ALTER TABLE `glpi_ocs_config` ADD COLUMN `glpi_link_enabled` int(1) NOT NULL,
		ADD COLUMN `link_ip` int(1) NOT NULL,
		ADD COLUMN `link_name` int(1) NOT NULL,
		ADD COLUMN `link_mac_address` int(1) NOT NULL,
  		ADD COLUMN `link_serial` int(1) NOT NULL,
  		ADD COLUMN `link_if_status` int(11) NOT NULL default '0'";
  		$DB->query($query) or die("0.7 add glpi_link fields in glpi_ocs_config" . $LANG["update"][90] . $DB->error());
	}
	$intnull=array("glpi_alerts" => array("device_type","FK_device","type"),
		"glpi_cartridges_type"=>array("tech_num"),
		"glpi_computers"=>array("FK_users","FK_groups"),
		"glpi_consumables_type"=>array("tech_num"),
		"glpi_contacts"=>array("type"),
		"glpi_device_case"=>array("type"),
		"glpi_device_control"=>array("interface"),
		"glpi_device_drive"=>array("interface"),
		"glpi_dropdown_kbcategories"=>array("level"),
		"glpi_dropdown_locations"=>array("level"),
		"glpi_dropdown_tracking_category"=>array("level"),
		"glpi_entities"=>array("level"),
		"glpi_infocoms"=>array("FK_enterprise","budget"),
		"glpi_monitors"=>array("type","model","FK_users","FK_groups"),
		"glpi_networking"=>array("type","model","firmware","FK_users","FK_groups"),
		"glpi_networking_ports"=>array("iface","netpoint"),
		"glpi_ocs_link"=>array("ocs_server_id"),
		"glpi_peripherals"=>array("model","FK_users","FK_groups"),
		"glpi_phones"=>array("model","FK_users","FK_groups"),
		"glpi_printers"=>array("type","model","FK_users","FK_groups"),
		"glpi_software"=>array("location","platform","FK_users","FK_groups"),
		"glpi_tracking"=>array("computer"),
		"glpi_users_groups"=>array("FK_users","FK_groups"),
		);

	foreach ($intnull as $table => $fields){
		foreach ($fields as $field){
			if (FieldExists($table, $field)) {
				$query = "UPDATE `$table` SET `$field`=0 WHERE $field IS NULL;";
				$DB->query($query) or die("0.7 update datas in $table for NULL values " . $DB->error());
				$query = "ALTER TABLE `$table` CHANGE `$field` `$field` INT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 alter $field in $table" . $DB->error());
			} else {
				// Error field does not exists : correct it
				$query = "ALTER TABLE `$table` ADD COLUMN `$field` INT NOT NULL DEFAULT '0'";
				$DB->query($query) or die("0.7 add $field in $table" . $DB->error());
			}
		}
	}
	// Clean history
	$query = "DELETE FROM `glpi_history` WHERE `linked_action`=0 AND `device_internal_type`=0 AND `old_value`=`new_value` AND `old_value` IS NOT NULL AND `old_value`!='';";
	$DB->query($query) or die("0.7 clean glpi_history " . $DB->error());

	$query = "DELETE FROM `glpi_display` WHERE `type`=".USER_TYPE." AND (`num`=4 );";
	$DB->query($query) or die("0.7 clean glpi_display for glpi_users " . $DB->error());

	// Add fields to block auto updates on linked items
	if (!FieldExists("glpi_config", "autoupdate_link_contact")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `autoupdate_link_contact` smallint(6) NOT NULL default '1'";
		$DB->query($query) or die("0.7 add autoupdate_link_contact in glpi_config" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "autoupdate_link_user")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `autoupdate_link_user` smallint(6) NOT NULL default '1'";
		$DB->query($query) or die("0.7 add autoupdate_link_user in glpi_config" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "autoupdate_link_group")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `autoupdate_link_group` smallint(6) NOT NULL default '1'";
		$DB->query($query) or die("0.7 add autoupdate_link_group in glpi_config" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "autoupdate_link_location")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `autoupdate_link_location` smallint(6) NOT NULL default '1'";
		$DB->query($query) or die("0.7 add autoupdate_link_location in glpi_config" . $LANG["update"][90] . $DB->error());
	}

	// Flat dropdowntree
	if (!FieldExists("glpi_config", "flat_dropdowntree")) {
		$query = "ALTER TABLE `glpi_config` ADD COLUMN `flat_dropdowntree` smallint(6) NOT NULL default '0'";
		$DB->query($query) or die("0.7 add flat_dropdowntree in glpi_config" . $LANG["update"][90] . $DB->error());
	}
	if (FieldExists("glpi_config", "mailing_signature")) {
		$query = "ALTER TABLE `glpi_config` CHANGE `mailing_signature` `mailing_signature` TEXT NULL ";
		$DB->query($query) or die("0.7 alter mailing signature in glpi_config" . $LANG["update"][90] . $DB->error());
	}

//Software categories
	if (!TableExists("glpi_dropdown_software_category"))
	{
		$query="CREATE TABLE `glpi_dropdown_software_category` (
  			`ID` int(11) NOT NULL auto_increment,
			`name` varchar(255) default NULL,
  			`comments` text,
  			PRIMARY KEY  (`ID`)
			) ENGINE=MyISAM ";
		$DB->query($query) or die("0.7 add table glpi_dropdown_software_category" . $LANG["update"][90] . $DB->error());	
	}
	if (!FieldExists("glpi_profiles", "rule_softwarecategories")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `rule_softwarecategories` char(1) default NULL AFTER `rule_ldap`";
		$DB->query($query) or die("0.7 add rule_softwarecategories in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `rule_softwarecategories` = config";
		$DB->query($query) or die("0.7 update rule_softwarecategories values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_software", "category")) {
		$query = "ALTER TABLE `glpi_software` ADD `category` INT( 11 ) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 alter category in glpi_software" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_ocs_config", "import_monitor_comments")) {
		$query = "ALTER TABLE `glpi_ocs_config` ADD `import_monitor_comments` INT( 2 ) NOT NULL DEFAULT '0' AFTER `import_ip`";
		$DB->query($query) or die("0.7 alter import_monitor_comments in glpi_ocs_config" . $LANG["update"][90] . $DB->error());
	}
	
	if (!FieldExists("glpi_ocs_config", "import_software_comments")) {
		$query = "ALTER TABLE `glpi_ocs_config` ADD `import_software_comments` INT NOT NULL DEFAULT '0' AFTER `import_monitor_comments`";
		$DB->query($query) or die("0.7 alter import_software_comments in glpi_ocs_config" . $LANG["update"][90] . $DB->error());
	}

	if (FieldExists("glpi_device_gfxcard", "ram")) {
		//Update gfxcard memory management
		$query = "UPDATE glpi_device_gfxcard SET specif_default=ram";
		$DB->query($query) or die("0.7 glpi_device_gfxcard" . $LANG["update"][90] . $DB->error());
		
		$query="ALTER TABLE `glpi_device_gfxcard` DROP `ram`";
		$DB->query($query) or die("0.7 delete 'ram' field from glpi_device_gfxcard" . $LANG["update"][90] . $DB->error());
	}

	if (FieldExists("glpi_config", "list_limit")) {
		//Update gfxcard memory management
		$query = "ALTER TABLE `glpi_config` CHANGE `list_limit` `list_limit` INT NULL DEFAULT '20'";
		$DB->query($query) or die("0.7 alter list_limit in config" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "list_limit_max")) {
		//Update gfxcard memory management
		$query = "ALTER TABLE `glpi_config` ADD `list_limit_max` INT NOT NULL DEFAULT '50' AFTER `list_limit` ;";
		$DB->query($query) or die("0.7 add list_limit_max in config" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_users", "list_limit")) {
		//Update gfxcard memory management
		$query = "ALTER TABLE `glpi_users` ADD `list_limit` INT NOT NULL DEFAULT '20' AFTER `language` ;";
		$DB->query($query) or die("0.7 add list_limit_max in users" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_config", "autoname_entity")) {
		$query = "ALTER TABLE `glpi_config` ADD `autoname_entity` smallint(6) NOT NULL default '1' ";
		$DB->query($query) or die("0.7 add autoname_entity in glpi_config" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_profiles", "rule_tracking")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `rule_tracking` char(1) default NULL AFTER `config`";
		$DB->query($query) or die("0.7 add rule_tracking in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `rule_tracking` = config";
		$DB->query($query) or die("0.7 update rule_tracking values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}

	if (FieldExists("glpi_profiles", "show_ticket")) {
		$query = "ALTER TABLE `glpi_profiles` CHANGE `show_ticket` `show_all_ticket` CHAR( 1 ) DEFAULT NULL ";
		$DB->query($query) or die("0.7 rename show_ticket to show_all_ticket in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_profiles", "show_assign_ticket")) {
		$query = "ALTER TABLE `glpi_profiles` ADD COLUMN `show_assign_ticket` char(1) default NULL AFTER `show_all_ticket`";
		$DB->query($query) or die("0.7 add show_assign_ticket in glpi_profiles" . $LANG["update"][90] . $DB->error());
		$query = "UPDATE `glpi_profiles` SET `show_assign_ticket` = show_all_ticket";
		$DB->query($query) or die("0.7 update show_assign_ticket values in glpi_profiles" . $LANG["update"][90] . $DB->error());
	}


	if (!FieldExists("glpi_tracking", "assign_group")) {
		$query = "ALTER TABLE `glpi_tracking` ADD `assign_group` INT NOT NULL DEFAULT '0' AFTER `assign_ent` ;";
		$DB->query($query) or die("0.7 add assign_group in tracking" . $LANG["update"][90] . $DB->error());
		$query = "ALTER TABLE `glpi_tracking` ADD INDEX ( `assign_group` ) ;";
		$DB->query($query) or die("0.7 add index on assign_group in tracking" . $LANG["update"][90] . $DB->error());
	}

	if (!FieldExists("glpi_config", "expand_soft_categorized")) {
		$query = "ALTER TABLE `glpi_config` ADD `expand_soft_categorized` int(1) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 add expand_soft_categorized in glpi_config" . $LANG["update"][90] . $DB->error());
	}
	if (!FieldExists("glpi_config", "expand_soft_not_categorized")) {
		$query = "ALTER TABLE `glpi_config` ADD `expand_soft_not_categorized` int(1) NOT NULL DEFAULT '0';";
		$DB->query($query) or die("0.7 add expand_soft_not_categorized in glpi_config" . $LANG["update"][90] . $DB->error());
	}

	// Clean history
	$query = "SELECT DISTINCT device_type FROM glpi_history";
	if ($result = $DB->query($query)){
		if ($DB->numrows($result)>0){
			while ($data = $DB->fetch_array($result)){
				$query2=" DELETE FROM glpi_history WHERE glpi_history.device_type='".$data['device_type']."' AND
					glpi_history.FK_glpi_device NOT IN (SELECT ID FROM ".$LINK_ID_TABLE[$data['device_type']].")";
				$DB->query($query2);
			}
		}
	}


} // fin 0.7 #####################################################################################
?>
