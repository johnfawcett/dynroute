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
			$sql = "ALTER TABLE $table
  				ADD PRIMARY KEY (`dynroute_id`,`selection`);";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}


// upgrade from previous release

		try{
			$sql = "SELECT default_dest FROM dynroute";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			$sql = "ALTER TABLE dynroute ADD COLUMN `default_dest` VARCHAR(255);";
			$sth = $this->db->prepare($sql);
			$sth->execute();
			return;
		}

		try{
			$sql = "SELECT description FROM dynroute";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			$sql = "ALTER TABLE dynroute ADD COLUMN `description` TEXT;";
			$sth = $this->db->prepare($sql);
			$sth->execute();
			return;
		}

		try{
			$sql = "SELECT id FROM dynroute";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			$sql = "ALTER TABLE dynroute CHANGE COLUMN `dynroute_id` `id` INT(11) AUTO_INCREMENT NOT NULL;";
			$sth = $this->db->prepare($sql);
			$sth->execute();
			return;
		}

		try{
			$sql = "SELECT name FROM dynroute";
			$sth = $this->db->prepare($sql);
			$sth->execute();
		} catch(PDOException $e) {
			$sql = "ALTER TABLE dynroute CHANGE COLUMN `displayname` `name` VARCHAR(255);";
			$sth = $this->db->prepare($sql);
			$sth->execute();
			return;
		}

// rewrite default_dest data

// upgrade from older releases

		return;
	}

	public function uninstall() {

			$table = 'dynroute';

		try{
			$sql = "DROP TABLE IF EXISTS $table;";
			$sth = $this->db->prepare($sql);
			return $sth->execute();

		} catch(PDOException $e) {
			return $e->getMessage();
		}

		$table = 'dynroute_dests';

		try{
			$sql = "DROP TABLE IF EXISTS $table;";
			$sth = $this->db->prepare($sql);
			return $sth->execute();

		} catch(PDOException $e) {
			return $e->getMessage();

		}
	}


	public function backup() {}
	public function restore($backup) {}
	public function doConfigPageInit($page) {

	}
	public function search($query, &$results) {
		$dynroutes = $this->getDetails();
		foreach ($dynroutes as $dynroute) {
			$results[] = array(
				"text" => _("Dynamic Route").": ".stripslashes($dynroute['name']),
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
								'name' => stripslashes($r['name']),
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
