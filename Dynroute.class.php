<?php
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module
namespace FreePBX\modules;
use PDO;
class Dynroute extends \FreePBX_Helpers implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		//This is only needed for database stuff. If you are not doing database stuff you don't need this
		$this->db = $freepbx->Database;
	}
	public function install() {}
	public function uninstall() {}
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
	public function saveDetail($vals){
		if (isset($vals['action'])) {
			unset($vals['action']);
                }
		if (isset($vals['display'])) {
			unset($vals['display']);
		}
		if (isset($vals['entries'])) {
			unset($vals['entries']);
		}
		$keys = [];
		$placeholders = [];
                $start = 'REPLACE INTO `dynroute`';
                foreach($vals as $key => $value){
                        $keys[] = $key;
                        $placeholders[] = ':'.$key;
                }
                $keyString = rtrim(implode(',',$keys),',');
                $placeString = rtrim(implode(',',$placeholders),',');
                $sql = sprintf('%s (%s) VALUES (%s)', $start, $keyString, $placeString);
                $this->db->prepare($sql)->execute($vals);
                return $vals['id'];
   
	}
	public function saveEntry($id,$entry){
		$this->deleteEntriesById($id);
		if (is_array($entry) && !empty($entry)) {
			foreach($entry as $entries) {
				$stmt = $this->db->prepare('INSERT INTO dynroute_dests VALUES (:dynroute_id, :selection, :dest)');
				$stmt->execute([
					':dynroute_id' => $entries['dynroute_id'],
					':selection' => $entries['selection'],
					':dest' => $entries['dest'],
				]);
			}
		}
		return true;
	}
	public function deleteEntriesById($id){
		$this->db->prepare('DELETE FROM dynroute_dests WHERE dynroute_id = :dynroute_id')->execute([':dynroute_id' => $id]);
		return $this;
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
					'duplicate' => array(
						'name' => 'duplicate',
						'id' => 'duplicate',
						'value' => _('Duplicate')
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
								'description' => $r['description'],
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
        public function getAllDetails() {
		$final = [];
		$all = $this->db->query('SELECT * FROM dynroute ORDER BY name',PDO::FETCH_ASSOC);
		foreach ($all as $item) {
			$final[$item['id']][] = $item;
		}
		return $final;
	}
	public function getAllEntries(){
		$final = [];
                $all = $this->db->query('SELECT * FROM dynroute_dests',PDO::FETCH_ASSOC);
		foreach ($all as $item){
			$final[$item['dynroute_id']][] = $item;
		}
		return $final;
	}
}
