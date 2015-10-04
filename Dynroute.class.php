<?php
// Copyright (c) 2015 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module
namespace FreePBX\modules;
class Dynroute extends \FreePBX_Helpers implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		//This is only needed for database stuff. If you are not doing database stuff you don't need this
		$this->db = $freepbx->Database;
	}
	public function install() {

		$table = 'dynroute';

		try{
			$sql = "CREATE TABLE IF NOT EXISTS $table(
				`id` INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY, 
				`name` VARCHAR(255) not NULL, 
				`description` text not NULL,
				`sourcetype` VARCHAR(100) default NULL, 
				`mysql_host` varchar(60) default NULL,
				`mysql_dbname` varchar(60) default NULL,
				`mysql_query` text,
				`mysql_username` varchar(30) default NULL,
				`mysql_password` varchar(30) default NULL,
				`odbc_func` varchar(100) default NULL,
				`odbc_query` text,
				`url_query` text,
				`agi_query` text,
        		        `agi_var_name_res` varchar(255), 
				`astvar_query` text,
        		        `enable_dtmf_input` varchar(8), 
       		         	`timeout` INT(11), 
              			`announcement_id` INT(11),
				`chan_var_name` varchar(255), 
				`chan_var_name_res` varchar(255),
				`validation_regex` text, 
				`max_retries` INT(11), 
				`invalid_retry_rec_id` INT(11),
				`invalid_rec_id` INT(11),
				`invalid_dest` VARCHAR(255),
				`default_dest` VARCHAR(255)
			);";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}



		$table = 'dynroute_dests';

		try{
			$sql = "CREATE TABLE IF NOT EXISTS $table(
				`dynroute_id` INT NOT NULL, 
				`selection` VARCHAR(255), 
				`dest` VARCHAR(50) 
			);";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}

		try{
			$sql = "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE "
				."`TABLE_CATALOG` = 'def' AND `TABLE_SCHEMA` = DATABASE() AND "
				."`TABLE_NAME` = '$table' AND `INDEX_NAME` = 'PRIMARY' ";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE $table
	  				ADD PRIMARY KEY (`dynroute_id`,`selection`);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
			return $e->getMessage();
		}

// upgrade from older releases


		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'enable_dtmf_input'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `enable_dtmf_input` VARCHAR(8);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'timeout'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `timeout` INT(11);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'announcement_id'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `announcement_id` INT(11);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'chan_var_name'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `chan_var_name` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'chan_var_name_res'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `chan_var_name_res` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'odbc_func'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `odbc_func` VARCHAR(100);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'odbc_query'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `odbc_query` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

// the following upgrade change is further updated later for V.13. Do not move this later than V.13 upgrade statements

		try{
			$sql = "SHOW COLUMNS FROM `dynroute_dests` LIKE 'default_dest'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute_dests ADD COLUMN `default_dest` CHAR(1) default 'n';";
				$sth = $this->db->prepare($sql);
				$sth->execute();
				$sql = "UPDATE dynroute_dests set default_dest='y',selection='' WHERE selection='default';";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'url_query'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `url_query` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'agi_query'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `agi_query` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'agi_var_name_res'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `agi_var_name_res` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'astvar_query'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `astvar_query` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'validation_regex'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `validation_regex` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'max_retries'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `max_retries` INT(11);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'invalid_retry_rec_id'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `invalid_retry_rec_id` INT(11);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'invalid_rec_id'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `invalid_rec_id` INT(11);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'invalid_dest'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `invalid_dest` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

// new in this release

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'default_dest'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `default_dest` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'description'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute ADD COLUMN `description` TEXT;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'id'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute CHANGE COLUMN `dynroute_id` `id` INT(11) AUTO_INCREMENT NOT NULL;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute` LIKE 'name'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute CHANGE COLUMN `displayname` `name` VARCHAR(255);";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "DELETE FROM dynroute WHERE name ='__install_done'";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
				return $e->getMessage();
		}


// rewrite default_dest data from dynroute_dests to dynroute.default_dest

		try{
			$sql = "SELECT * FROM `dynroute_dests` WHERE default_dest='y'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (!empty($results)) {
				$c = count($results);
				echo _("There are $c default destinations in dynroute_dests to migrate<br>");
				$sql = "UPDATE dynroute d SET default_dest=(SELECT dest FROM dynroute_dests dd WHERE dd.dynroute_id = d.id AND dd.default_dest='y')";
				$sth = $this->db->query($sql);
				$num_rows = $sth->rowCount();
				echo _("Updated dynroute.default_dest with $num_rows destinations from dynroute_dests<br>");
				if ($num_rows < $c) { 
					echo _("Less destinations than expected were migrated. Aborting without deleting source table");
				} else {
					$sql = "DELETE FROM dynroute_dests WHERE default_dest='y'";
					$sth = $this->db->query($sql);
					$num_rows2 = $sth->rowCount();
					echo _("$num_rows2 unneeded rows deleted from dynroute_dests<br>");
					$sql = "ALTER TABLE dynroute_dests DROP COLUMN default_dest";
					$sth = $this->db->prepare($sql);
					$sth->execute();
				}
			} else {
				echo _("No default destinations in dynroute_dests to migrate. Skipping...<br>");
			}	
		} catch(PDOException $e) {
				return $e->getMessage();
		}

		try{
			$sql = "SHOW COLUMNS FROM `dynroute_dests` LIKE 'default_dest'";
			$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
			if (empty($results)) {
				$sql = "ALTER TABLE dynroute_dests DROP COLUMN `default_dest`;";
				$sth = $this->db->prepare($sql);
				$sth->execute();
			}		
		} catch(PDOException $e) {
				return $e->getMessage();
		}


		return;
	}

	public function uninstall() {

			$table = 'dynroute';

		try{
			$sql = "DROP TABLE IF EXISTS $table;";
			$sth = $this->db->prepare($sql);
			$sth->execute();

		} catch(PDOException $e) {
			return $e->getMessage();
		}

		$table = 'dynroute_dests';

		try{
			$sql = "DROP TABLE IF EXISTS $table;";
			$sth = $this->db->prepare($sql);
			$sth->execute();

		} catch(PDOException $e) {
			return $e->getMessage();

		}
		return;
	}


	public function backup() {}
	public function restore($backup) {}
	public function doConfigPageInit($page) {

	}
	public function search($query, &$results) {
		$dynroutes = $this->getDetails();
		foreach ($dynroutes as $dynroute) {
			$results[] = array(
				"text" => _("Dynamic Route").": ".$dynroute['name'],
				"type" => "get",
				"dest" => "?display=dynroute&action=edit&id=".$dynroute['id']
			);
		}
	}

	public function getDetails($id = false) {
		$sql = 'SELECT * FROM dynroute';
		if ($id) {
			$sql .= ' where  id = :id ';
		}
		$sql .= ' ORDER BY name';

		$sth = $this->Database->prepare($sql);
		$sth->execute(array(":id" => $id));
		$res = $sth->fetchAll();
		if ($id && isset($res[0])) {
			return $res[0];
		} else {
			$res = is_array($res)?$res:array();
			return $res;
		}
	}
	public function getActionBar($request) {
		$buttons = array();
		switch($request['display']) {
			case 'dynroute':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['id'])) {
					unset($buttons['delete']);
				}
				isset($request['action'])?'':$buttons = NULL;
			break;
		}
		return $buttons;
	}
	public function pageHook($request){
		return \FreePBX::Hooks()->processHooks($request);
	}
	public function ajaxRequest($req, &$setting) {
	switch ($req) {
		case 'getJSON':
			return true;
		break;
		default:
			return false;
		break;
	}
}
public function ajaxHandler(){
	switch ($_REQUEST['command']) {
		case 'getJSON':
			switch ($_REQUEST['jdata']) {
				case 'grid':
					$dynroutes = $this->getDetails();
					$ret = array();
					foreach ($dynroutes as $r) {
						$r['name'] = $r['name'] ? $r['name'] : 'Dynamic Route ID: ' . $r['id'];
						$ret[] = array(
								'name' => $r['name'],
								'id' => $r['id'],
								'link' => array($r['id'],$r['name'])
							);
					}
					return $ret;
					break;
					default:
						return false;
					break;
				}
			break;
			default:
				return false;
			break;
		}
	}
}
