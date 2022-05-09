<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function dynroute_destinations() {
	global $module_page;

	//get the list of Dynamic Routes
	$results = dynroute_get_details();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$name = $result['name'] ? $result['name'] : 'Dynamic Route ' . $result['id'];
			$extens[] = array('destination' => 'dynroute-'.$result['id'].',s,1', 'description' => $name);
		}
	}
	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}

}

//dialplan generator
function dynroute_get_config($engine) {
	global $ext;

	switch($engine) {
		case 'asterisk':
			$dynroutelist = dynroute_get_details();
			if(!is_array($dynroutelist)) {
				break;
			}

			foreach($dynroutelist as $dynroute) {
				$c = 'dynroute-' . $dynroute['id'];
				$dynroute = dynroute_get_details($dynroute['id']);
				$ext->addSectionComment($c, $dynroute['name'] ? $dynroute['name'] : 'Dynamic Route ' . $dynroute['id']);


				switch ($dynroute['sourcetype']) {
					case 'mysql':
						$query = $dynroute['mysql_query'];
						break;

					case 'odbc':
						$query = $dynroute['odbc_query'];
						break;

					case 'url':
						$query = $dynroute['url_query'];
						break;

					case 'agi':
						$query = $dynroute['agi_query'];

						break;

					case 'astvar':
						$query = $dynroute['astvar_query'];
						break;
					default:
						break;
				}

				// variable substitutions

				if ($dynroute['enable_substitutions']=='CHECKED')
				{
					$query = str_replace('[NUMBER]', '${CALLERID(num)}', $query);
					$query = str_replace('[INPUT]', '${dtmfinput}', $query);
					$query = str_replace('[DID]', '${FROM_DID}', $query);
					$query = preg_replace('/\[([^\]]*)\]/','${DYNROUTE_$1}',$query);
				}
					
				$announcement_id = (isset($dynroute['announcement_id']) ? $dynroute['announcement_id'] : '0');

				if ($dynroute['enable_dtmf_input']=='CHECKED')
				{
                                	$ext->add($c, 's', '', new ext_setvar('__DYNROUTE_RETRIES', '0'));
					if ($announcement_id) {
                                      		$announcement_msg = recordings_get_file($announcement_id);
                                	} else {
						$announcement_msg = '';
                                	}	
					$ext->add($c, 's', '', new ext_read('dtmfinput',$announcement_msg,$dynroute['max_digits'],'','',$dynroute['timeout']));
					if ($dynroute['chan_var_name'] != '')
						$ext->add($c, 's', '', new ext_setvar('__DYNROUTE_'.$dynroute['chan_var_name'], '${dtmfinput}'));
					if ($dynroute['validation_regex'] != '')
					{
						$ext->add($c, 's', '', new ext_setvar('__DYNROUTE_REGEX', '${REGEX("'.$dynroute['validation_regex'].'" ${dtmfinput})}'));
						$ext->add($c, 's', '', new ext_gotoif('$["${DYNROUTE_REGEX}" = "0"]',$c.',2,1'));
					}
                                }
				$ext->add($c, 's', '', new ext_setvar('dynroute', ''));
				if ($dynroute['sourcetype']=='mysql' && $dynroute['mysql_host']!='' && $dynroute['mysql_dbname']!='' && $dynroute['mysql_query']!='')
				{
					$ext->add($c, 's', '', new ext_setvar('connid', ''));
                                	$ext->add($c, 's', '', new ext_mysql_connect('connid', $dynroute['mysql_host'],  $dynroute['mysql_username'],  $dynroute['mysql_password'],  $dynroute['mysql_dbname']));
					$ext->add($c, 's', '', new ext_gotoif('$["${connid}" = ""]',$id.',4,1'));
                                	$ext->add($c, 's', '', new ext_mysql_query('resultid', 'connid', $query));
					$ext->add($c, 's', '', new ext_gotoif('$["${resultid}" = ""]',$c.',4,1'));
                                	$ext->add($c, 's', '', new ext_mysql_fetch('fetchid', 'resultid', 'dynroute')); 
                                	$ext->add($c, 's', '', new ext_mysql_clear('resultid'));                            
                                	$ext->add($c, 's', '', new ext_mysql_disconnect('connid'));
					$ext->add($c, 's', '', new ext_gotoif('$[${fetchid} = 0]',$c.',4,1'));
					$ext->add($c, 's', '', new ext_noop('dynroute=${dynroute}'));
                                }
				if ($dynroute['sourcetype']=='url' && $dynroute['url_query']!='')
				{
					$ext->add($c, 's', '', new ext_setvar('CURLOPT(dnstimeout)','5'));
					$ext->add($c, 's', '', new ext_setvar('CURLOPT(conntimeout)','5'));
					$ext->add($c, 's', '', new ext_setvar('CURLOPT(ftptimeout)','5'));
					$ext->add($c, 's', '', new ext_setvar('CURLOPT(httptimeout)','5'));
					$ext->add($c, 's', '', new ext_setvar('dynroute', '${CURL'.'("'.$query.'")}'));
                                }
				if ($dynroute['sourcetype']=='agi' && $dynroute['agi_query']!='')
				{
					$ext->add($c, 's', '', new ext_agi($query));
					if ($dynroute['agi_var_name_res'] != '')
						$ext->add($c, 's', '', new ext_setvar('dynroute', '${'.$dynroute['agi_var_name_res'].'}'));
					$ext->add($c, 's', '', new ext_noop('dynroute=${dynroute}'));
                                }
				if ($dynroute['sourcetype']=='odbc' && $dynroute['odbc_func']!='')
				{
					$ext->add($c, 's', '', new ext_setvar('dynroute', '${ODBC_'.$dynroute['odbc_func'].'("'.$query.'")}'));
                                }
				if ($dynroute['sourcetype']=='astvar' && $dynroute['astvar_query']!='')
				{
					$ext->add($c, 's', '', new ext_setvar('dynroute', $query));
                                }
				if ($dynroute['sourcetype']=='none' && $dynroute['enable_dtmf_input']=='CHECKED')
                                {
                                        $ext->add($c, 's', '', new ext_setvar('dynroute','${dtmfinput}'));
                                }
				$ext->add($c, 's', '', new ext_setvar('dynroute', '${STRREPLACE(dynroute,"\"","")}'));
				if ($dynroute['chan_var_name_res'] != '')
					$ext->add($c, 's', '', new ext_setvar('__DYNROUTE_'.$dynroute['chan_var_name_res'], '${dynroute}'));
				$ext->add($c, 's', '', new ext_gotoif('$["${dynroute}" = ""]',$c.',1,1'));

				// add the destinations from the matches section
				$dests = dynroute_get_entries($dynroute['id']);
				if (!empty($dests)) {
					foreach($dests as $dest) {
						$ext->add($c, 's', '', new ext_gotoif('$["${dynroute}" = "'.str_replace("\"","",$dest['selection']).'"]',$dest['dest']));
					}
				}
				$ext->add($c, 's', '', new ext_goto($c.',1,1'));

				// used to send to default route or to hangup if not default route
				if (!empty($dynroute['default_dest'])) $ext->add($c, '1', '', new ext_goto($dynroute['default_dest']));
				$ext->add($c, '1', '', new ext_hangup(''));
				
				// used to manage retries for dtmf not matching validation regex
				if ($dynroute['enable_dtmf_input']=='CHECKED' && $dynroute['validation_regex'] != '')
				{
					$ext->add($c, '2', '', new ext_setvar('__DYNROUTE_RETRIES', '$[${DYNROUTE_RETRIES}+1]'));
					$ext->add($c, '2', '', new ext_gotoif('$["${DYNROUTE_RETRIES}" > "'.$dynroute['max_retries'].'"]',$c.',3,1'));
					if ($dynroute['invalid_retry_rec_id']!='0') $ext->add($c, '2', '', new ext_playback(recordings_get_file($dynroute['invalid_retry_rec_id'])));
					$ext->add($c, '2', '', new ext_goto($c.',s,2'));

					if ($dynroute['invalid_rec_id']!='0') $ext->add($c, '3', '', new ext_playback(recordings_get_file($dynroute['invalid_rec_id'])));
					if ($dynroute['invalid_dest']!='') $ext->add($c, '3', '', new ext_goto($dynroute['invalid_dest']));
					$ext->add($c, '3', '', new ext_goto($c.',1,1'));
				}

				// used for resetting chan_var_name_res in case of error
				if ($dynroute['chan_var_name_res'] != '')
					$ext->add($c, '4', '', new ext_setvar('__DYNROUTE_'.$dynroute['chan_var_name_res'], ''));
				$ext->add($c, '4', '', new ext_goto($c.',1,1'));
			}
		break;
	}
}

//replaces dynroute_list(), returns all details of any dynamic route
function dynroute_get_details($id = '') {
	return FreePBX::Dynroute()->getDetails($id);
}

//get all dynroute entries
function dynroute_get_entries($id) {
	global $db;

	//+0 to convert string to an integer
	$sql = "SELECT selection, dest FROM dynroute_dests where dynroute_id='$id' ORDER BY selection+0";
	$res = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
	if ($db->IsError($res)) {
		die_freepbx($res->getDebugInfo());
	}
	return $res;
}


//draw dynroute options page
function dynroute_configpageload() {
	global $currentcomponent, $display;
	return true;
}

function dynroute_configpageinit($pagename) {
	global $currentcomponent;
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

	if($pagename == 'dynroute'){
		$currentcomponent->addprocessfunc('dynroute_configprocess');

		//dont show page if there is no action set
		if ($action && $action != 'delete' || $id) {
			$currentcomponent->addguifunc('dynroute_configpageload');
		}

    return true;
	}
}

//prosses received arguments
function dynroute_configprocess(){
	if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'dynroute'){
		global $db;
		//get variables
		$get_var = array('id', 'name', 'description', 'sourcetype','enable_substitutions',
				'mysql_host','mysql_dbname','mysql_query','mysql_username','mysql_password',
				'odbc_func','odbc_query','url_query','agi_query','agi_var_name_res',
				'astvar_query','enable_dtmf_input','max_digits','timeout','announcement_id',
				'chan_var_name','chan_var_name_res','validation_regex',
				'max_retries','invalid_retry_rec_id','invalid_rec_id',
				'invalid_dest', 'default_dest'
				);

		foreach($get_var as $var){
			$vars[$var] = isset($_REQUEST[$var]) 	? $_REQUEST[$var]		: '';
		}


		$vars['enable_dtmf_input'] = empty($vars['enable_dtmf_input']) ? '' : 'CHECKED';
		$vars['max_digits'] = empty($vars['max_digits']) ? '0' : $vars['max_digits'];
		$vars['timeout'] = empty($vars['timeout']) ? '5' : $vars['timeout'];
		$vars['chan_var_name'] = empty($vars['chan_var_name']) ? '' : $vars['chan_var_name'];
		$vars['chan_var_name_res'] = empty($vars['chan_var_name_res']) ? '' : $vars['chan_var_name_res'];
		$vars['validation_regex'] = empty($vars['validation_regex']) ? '' : $vars['validation_regex'];
		$vars['max_retries'] = empty($vars['max_retries']) ? '0' : $vars['max_retries'];
		$vars['invalid_retry_rec_id'] = empty($vars['invalid_retry_rec_id']) || !is_numeric($vars['invalid_retry_rec_id']) ? '0' : $vars['invalid_retry_rec_id'];
		$vars['invalid_rec_id'] = empty($vars['invalid_rec_id']) || !is_numeric($vars['invalid_rec_id']) ? '0' : $vars['invalid_rec_id'];
		$vars['announcement_id'] = empty($vars['announcement_id']) || !is_numeric($vars['announcement_id']) ? '0' : $vars['announcement_id'];

		$action		= isset($_REQUEST['action'])	? $_REQUEST['action']	: '';
		$entries	= isset($_REQUEST['entries'])	? $_REQUEST['entries']	: '';

		switch ($action) {
			case 'save':
//print_r($_REQUEST);exit;
				//get real dest
				$_REQUEST['id'] = $vars['id'] = dynroute_save_details($vars);
				dynroute_save_entries($vars['id'], $entries);
				needreload();
				$this_dest = dynroute_getdest($vars['id']);
				\fwmsg::set_dest($this_dest[0]);
				redirect_standard_continue();
			break;
			case 'delete':
				dynroute_delete($vars['id']);
				needreload();
				redirect_standard_continue();
			break;
		}
	}
}

//save dynroute settings
function dynroute_save_details($vals){
	global $db, $amp_conf;

	// shoud be able to do without the escaping when using
	// db->query since it uses a prepared statement.

	//foreach($vals as $key => $value) {
	//	$vals[$key] = $db->escapeSimple($value);
	//}

	if ($vals['id']) {
		$start = "REPLACE INTO `dynroute` (";
	} else {
		unset($vals['id']);
		$start = "INSERT INTO `dynroute` (";
	}

	$end = ") VALUES (";
	foreach ($vals as $k => $v) {
		$start .= "$k, ";
		$end .= ":$k, ";
	}

	$sql = substr($start, 0, -2).substr($end, 0, -2).")";
	$foo = $db->query($sql, $vals);
	if($db->IsError($foo)) {
		die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
	}
	// Was this a new one?
	if (!isset($vals['id'])) {
		$sql = ( ($amp_conf["AMPDBENGINE"]=="sqlite3") ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()');
		$id = $db->getOne($sql);
		if ($db->IsError($id)){
			die_freepbx($id->getDebugInfo());
		}
		$vals['id'] = $id;
	}

	return $vals['id'];
}

//save dynroute entries
function dynroute_save_entries($id, $entries){
	global $db;
	$id = $db->escapeSimple($id);
	$sql = 'DELETE FROM dynroute_dests WHERE dynroute_id = "' . $id . '"';
        $sth = $db->query($sql);
	if ($entries) {
		for ($i = 0; $i < count($entries['ext']); $i++) {
			//make sure there is an extension & goto set - otherwise SKIP IT
			if (trim($entries['ext'][$i]) != '' && $entries['goto'][$i]) {
				$d[] = array(
						'dynroute_id'	=> $id,
						'selection' 	=> $entries['ext'][$i],
						'dest'		=> $entries['goto'][$i],
				);
			}

		}
		$sql = $db->prepare('INSERT INTO dynroute_dests VALUES (?, ?, ?)');
		$res = $db->executeMultiple($sql, $d);
		if ($db->IsError($res)){
			die_freepbx($res->getDebugInfo());
		}
	}
	return true;
}

//restore dynroute entries
function dynroute_restore_entries($id, $entries){
	global $db;
	$id = $db->escapeSimple($id);
	$sql = 'DELETE FROM dynroute_dests WHERE dynroute_id = "' . $id . '"';
	$sth = $db->query($sql);
	if ($entries) {
		for ($i = 0; $i < count($entries); $i++) {
		//make sure there is selection and dest otherwise SKIP IT
			if (trim($entries[$i]['selection']) != '' && $entries[$i]['dest'] !='') {
				$d[] = array(
					'dynroute_id'   => $id,
					'selection'     => $entries[$i]['selection'],
					'dest'          => $entries[$i]['dest'],
				);
			}
		}
		$sql = $db->prepare('INSERT INTO dynroute_dests VALUES (?, ?, ?)');
		$res = $db->executeMultiple($sql, $d);
		if ($db->IsError($res)){
			die_freepbx($res->getDebugInfo());
		}
	}
	return true;
}



//draw dynamic route entires table header
function dynroute_draw_entries_table_header_dynroute() {
	return  array(_('Match'), _('Destination'), _('Delete'));
}

//draw actualy entires
function dynroute_draw_entries($id){
	$headers		= mod_func_iterator('draw_entries_table_header_dynroute');
	$dynroute_entries	= dynroute_get_entries($id);

	if ($dynroute_entries) {
		foreach ($dynroute_entries as $k => $e) {
			$entries[$k]= $e;
			$array = array('id' => $id, 'ext' => $e['selection']);
			$entries[$k]['hooks'] = mod_func_iterator('draw_entries_dynroute', $array);
		}
	}

	$entries['blank'] = array('selection' => '', 'dest' => '');
	//assign to a vatriable first so that it can be passed by reference
	$array = array('id' => '', 'ext' => '');
	$entries['blank']['hooks'] = mod_func_iterator('draw_entries_dynroute', $array);

	return load_view(dirname(__FILE__) . '/views/entries.php',
				array(
					'headers'	=> $headers,
					'entries'	=>  $entries
				)
			);

}

//delete a dynroute + entires
function dynroute_delete($id) {
	global $db;
	$sql = 'DELETE FROM dynroute WHERE id = "' . $db->escapeSimple($id) . '"';
	$sth = $db->query($sql);

	$sql = 'DELETE FROM dynroute_dests WHERE dynroute_id = "' . $db->escapeSimple($id) . '"';
	$sth = $db->query($sql);
}
//----------------------------------------------------------------------------
// Dynamic Destination Registry and Recordings Registry Functions
function dynroute_check_destinations($dest=true) {
	global $active_modules,$db;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT d.dest, a.name, d.selection, a.id id FROM dynroute a INNER JOIN dynroute_dests d ON a.id = d.dynroute_id  ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$sql .= "ORDER BY name";
	$results = $db->query($sql);

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['id'];
		$name = $result['name'] ? $result['name'] : 'Dynamic Route ' . $thisid;
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Dynamic Route: %s / Option: %s"),$name,$result['selection']),
			'edit_url' => 'config.php?display=dynroute&action=edit&id='.urlencode($thisid),
		);
	}
	return $destlist;
}



function dynroute_change_destination($old_dest, $new_dest) {
	global $db;
// this query is unsafe, it can change destinations for other dynroutes
// 	$sql = "UPDATE dynroute_dests SET dest = '$new_dest' WHERE dest = '$old_dest'";
// 	$db->query($sql);

}


function dynroute_getdest($exten) {
	return array('dynroute-'.$exten.',s,1');
}

function dynroute_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,9) == 'dynroute-') {
		$exten = explode(',',$dest);
		$exten = substr($exten[0],9);

		$thisexten = dynroute_get_details($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['dynroute']['type'])?$active_modules['dynroute']['type']:'setup';
			return array('description' => sprintf(_("Dynamic Route: %s"), ($thisexten['name'] ? $thisexten['name'] : $thisexten['id'])),
			             'edit_url' => 'config.php?display=dynroute&action=edit&id='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function dynroute_recordings_usage($recording_id) {
	global $active_modules,$db;

	$sql = "SELECT `id`, `name` FROM `dynroute` WHERE `announcement_id` = '$recording_id' OR `invalid_retry_rec_id` = '$recording_id' OR `invalid_rec_id` = '$recording_id'";
	$results = $db->query($sql);
	if (empty($results)) {
		return array();
	} else {
		foreach ($results as $result) {
			$usage_arr[] = array(
				'url_query' => 'config.php?display=dynroute&action=edit&id='.urlencode($result['id']),
				'description' => sprintf(_("Dynamic Route: %s"), ($result['name'] ? $result['name'] : $result['id'])),
			);
		}
		return $usage_arr;
	}
}

