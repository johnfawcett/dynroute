<?php
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module

extract($request, EXTR_SKIP);
if($action == 'add'){
	$heading = _("Add Dynamic Route");
	$deet = array('id', 'name', 'description', 'sourcetype','enable_substitutions',
				'mysql_host','mysql_dbname','mysql_query','mysql_username','mysql_password',
				'odbc_func','odbc_query','url_query','agi_query','agi_var_name_res',
				'astvar_query','enable_dtmf_input','max_digits','timeout','announcement_id',
				'chan_var_name','chan_var_name_res','validation_regex',
				'max_retries','invalid_retry_rec_id','invalid_rec_id',
				'invalid_dest','default_dest'
				);

	//set defaults on new dymaic routes
	foreach ($deet as $d) {
		switch ($d){
			case 'announcement_id':
				$dynroute[$d] = '';
				break;
			case 'timeout':
				$dynroute[$d] = 5;
				break;
			case 'max_retries':
				$dynroute[$d] = 0;
				break;
			case 'max_digits':
				$dynroute[$d] = 0;
				break;
			case 'enable_substitutions':
				$dynroute[$d] = 'CHECKED';
				break;
			default:
			$dynroute[$d] = '';
				break;
		}
	}
}else{
	$dynroute = dynroute_get_details($id);
	$heading = _('Edit Dynamic Route: ');
	$heading .= ($dynroute['name'] ? htmlspecialchars($dynroute['name'],ENT_QUOTES) : 'ID '.$dynroute['id']);
	$usage_list	= framework_display_destination_usage(dynroute_getdest($dynroute['id']));
	if(!empty($usage_list)){
		$infohtml = '
		<div class="panel panel-default">
			<div class="panel-heading">
				'.$usage_list['text'].'
			</div>
			<div class="panel-body">
    			'.$usage_list['tooltip'].'
			</div>
		</div>
		';
	}
	$delURL = '?display=dynroute&action=delete&id='.$id;
}
$recordingList = recordings_list();
$annopts = '<option>'._('None').'</option>';
foreach($recordingList as $r){
	$checked = ($r['id'] == $dynroute['announcement_id']?' SELECTED':'');
	$annopts .= '<option value="'.$r['id'].'" '.$checked.'>'.$r['displayname'].'</option>';
}
$invalidretryopts = '<option>'._('None').'</option>';
foreach($recordingList as $r){
	$checked = ($r['id'] == $dynroute['invalid_retry_rec_id']?' SELECTED':'');
	$invalidretryopts .= '<option value="'.$r['id'].'" '.$checked.'>'.$r['displayname'].'</option>';
}
$invalidopts = '<option>'._('None').'</option>';
foreach($recordingList as $r){
	$checked = ($r['id'] == $dynroute['invalid_rec_id']?' SELECTED':'');
	$invalidopts .= '<option value="'.$r['id'].'" '.$checked.'>'.$r['displayname'].'</option>';
}
$source_types=array(
	array('id'=>'none','displayname'=>'none'),
	array('id'=>'mysql','displayname'=>'MySQL'),
	array('id'=>'odbc','displayname'=>'ODBC'),
	array('id'=>'url','displayname'=>'URL'),
	array('id'=>'agi','displayname'=>'AGI'),
	array('id'=>'astvar','displayname'=>'Asterisk variable'),
);
$sourcetypeopts = '';
foreach($source_types as $r){
	$checked = ($r['id'] == $dynroute['sourcetype']?' SELECTED':'');
	$sourcetypeopts .= '<option value="'.$r['id'].'" '.$checked.'>'.$r['displayname'].'</option>';
}

$hooks = \FreePBX::Dynroute()->pageHook($_REQUEST);
$hookhtml = '';
foreach ($hooks as $key => $value) {
	$hookhtml .= $value;
}
?>
<div class="container-fluid">
	<h1><?php echo $heading?></h1>
	<?php echo $infohtml?>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-9">
				<div class="fpbx-container">
					<div class="display full-border">
						<form class='fpbx-submit' name="frm_dynroute" id="frm_dynroute" method="POST" action="config.php?display=dynroute" data-fpbx-delete="<?php echo $delURL?>">
						<input type="hidden" name="id" value="<?php echo $dynroute['id']?>">
						<input type="hidden" name="invalid_dest" id="invalid_dest" value="">
						<input type="hidden" name="default_dest" id="default_dest" value="">
						<input type="hidden" name="action" value="save">

						<div class="section-title" data-for="dynroutegeneral">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route General Options')?></h3>
						</div>
						<div class="section" data-id="dynroutegeneral">
							<!--DR Name-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="name"><?php echo _("Dynamic Route Name") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="name"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($dynroute['name'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="name-help" class="help-block fpbx-help-block"><?php echo _("Name of this Dynamic Route")?></span>
									</div>
								</div>
							</div>
							<!--END DR Name-->
							<!--DR Description-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="description"><?php echo _("Dynamic Route Description") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
												</div>
												<div class="col-md-9">
													<input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($dynroute['description'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="description-help" class="help-block fpbx-help-block"><?php echo _("Description of this Dynamic Route")?></span>
									</div>
								</div>
							</div>
							<!--END DR Description-->
						</div>
						<div class="section-title" data-for="dynamicroutedtmf">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route DTMF Options')?></h3>
						</div>
						<div class="section" data-id="dynamicroutedtmf">
							<!--Enable DTMF-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="enable_dtmf_input"><?php echo _("Enable DTMF Input") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="enable_dtmf_input"></i>
												</div>
												<div class="col-md-9 radioset">
													<input type="radio" name="enable_dtmf_input" id="dtmfyes" value="CHECKED" <?php echo ($dynroute['enable_dtmf_input']=='CHECKED'?'CHECKED':'') ?>>
													<label for="dtmfyes"><?php echo _("Yes");?></label>
													<input type="radio" name="enable_dtmf_input" id="dtmfno" value="" <?php echo ($dynroute['enable_dtmf_input']=='CHECKED'?'':'CHECKED') ?>>
													<label for="dtmfno"><?php echo _("No");?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="enable_dtmf_input-help" class="help-block fpbx-help-block"><?php echo _("Wait for DTMF input")?></span>
									</div>
								</div>
							</div>
							<!--END Enable DTMF-->
							<!--Announcement-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="announcement_id"><?php echo _("Announcement") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="announcement_id"></i>
												</div>
												<div class="col-md-9">
													<select class="form-control" id="announcement_id" name="announcement_id">
														<?php echo $annopts?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="announcement_id-help" class="help-block fpbx-help-block"><?php echo _("Greeting to be played prior to DTMF input.")?></span>
									</div>
								</div>
							</div>
							<!--END Announcement-->
							<!--Max digits-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="max_digits"><?php echo _("Max digits") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="max_digits"></i>
												</div>
												<div class="col-md-9">
													<input type="number" min=0 class="form-control" id="max_digits" name="max_digits" value="<?php echo htmlspecialchars($dynroute['max_digits'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="max_digits-help" class="help-block fpbx-help-block"><?php echo _("Maximum number of DTMF digits. If zero then no limit. Avoids having to press # key at end of fixed input length. Additional DTMF input is ignored.")?></span>
									</div>
								</div>
							</div>
							<!--END Max digits-->
							<!--Timeout-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="timeout"><?php echo _("Timeout") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="timeout"></i>
												</div>
												<div class="col-md-9">
													<input type="number" min=0 class="form-control" id="timeout" name="timeout" value="<?php echo htmlspecialchars($dynroute['timeout'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="timeout-help" class="help-block fpbx-help-block"><?php echo _("Time in seconds to wait for DTMF input")?></span>
									</div>
								</div>
							</div>
							<!--END Timeout-->
							<!--Validation-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="validation_regex"><?php echo _("Validation") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="validation_regex"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="validation_regex" name="validation_regex" value="<?php echo htmlspecialchars($dynroute['validation_regex'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="validation_regex-help" class="help-block fpbx-help-block"><?php echo _("Validation rules using a Asterisk regular expression. The DTMF input will be validated with Asterisk REGEX_MATCH function using this REGEX. For example to ensure the input is between 3 and 4 digits long you could use ^[0-9]\{3,4\}$ in this field. Non matching DTMF will produce a retry depending on the value of Invalid Retries.")?></span>
									</div>
								</div>
							</div>
							<!--END Validation-->
							<!--Retries-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="max_retries"><?php echo _("Invalid Retries") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="max_retries"></i>
												</div>
												<div class="col-md-9">
													<input type="number" min=0 max="10" class="form-control" id="max_retries" name="max_retries" value="<?php echo htmlspecialchars($dynroute['max_retries'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="max_retries-help" class="help-block fpbx-help-block"><?php echo _("Number of times to retry when DTMF does not match validation rule.")?></span>
									</div>
								</div>
							</div>
							<!--END Retries-->
							<!--Invalid Retry Recording-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="invalid_retry_rec_id"><?php echo _("Invalid Retry Recording") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="invalid_retry_rec_id"></i>
												</div>
												<div class="col-md-9">
													<select class="form-control" id="invalid_retry_rec_id" name="invalid_retry_rec_id">
														<?php echo $invalidretryopts?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="invalid_retry_rec_id-help" class="help-block fpbx-help-block"><?php echo _("Prompt to be played if dtmf does not match validation rules and maximum retries has not been reached")?></span>
									</div>
								</div>
							</div>
							<!--END Invalid Retry Recording-->
							<!--Invalid Recording-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="invalid_rec_id"><?php echo _("Invalid Recording") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="invalid_rec_id"></i>
												</div>
												<div class="col-md-9">
													<select class="form-control" id="invalid_rec_id" name="invalid_rec_id">
														<?php echo $invalidopts?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="invalid_rec_id-help" class="help-block fpbx-help-block"><?php echo _("Prompt to be played when a timeout occurs, before prompting the caller to try again")?></span>
									</div>
								</div>
							</div>
							<!--END Invalid Recording-->
							<!--Invalid Destination-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="gotoinvalid"><?php echo _("Invalid Destination") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="gotoinvalid"></i>
												</div>
												<div class="col-md-9">
													<?php echo drawselects($dynroute['invalid_dest'],'invalid')?>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="gotoinvalid-help" class="help-block fpbx-help-block"><?php echo _("Destination to send the call to if the dtmf did not match the validation rule and maximum retries has been reached")?></span>
									</div>
								</div>
							</div>
							<!--END Invalid Destination-->
						</div>
						<div class="section-title" data-for="dynamicroutevars">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route Saved Variables')?></h3>
						</div>
						<div class="section" data-id="dynamicroutevars">
							<!--Input variable-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="chan_var_name"><?php echo _("Saved input variable name") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="chan_var_name"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="chan_var_name" name="chan_var_name" value="<?php echo htmlspecialchars($dynroute['chan_var_name'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="chan_var_name-help" class="help-block fpbx-help-block"><?php echo _("Name of variable in which to save dtmf input for future use in the dialplan or further dynamic routes. This is available as [xxx] in the query/lookup where xxx is the name of the variable you specify here. To use the variable in the dialplan (e.g. custom applicaitons) it is necessary to prefix it with DYNROUTE_ e.g. DYNROUTE_xxx")?></span>
									</div>
								</div>
							</div>
							<!--END Input variable-->
							<!--Result variable-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="chan_var_name_res"><?php echo _("Saved result variable name") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="chan_var_name_res"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="chan_var_name_res" name="chan_var_name_res" value="<?php echo htmlspecialchars($dynroute['chan_var_name_res'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="chan_var_name_res-help" class="help-block fpbx-help-block"><?php echo _("Name of variable in which to save lookup result for future use in the dialplan or further dynamic routes. This is available as [xxx] in the query/lookup where xxx is the name of the variable you specify here. To use the variable in the dialplan (e.g. custom applicaitons) it is necessary to prefix it with DYNROUTE_ e.g. DYNROUTE_xxx. In the case of lookup type None then this will be valorized with DTMF input (if enabled).")?></span>
									</div>
								</div>
							</div>
							<!--END Result variable-->
						</div>
						<div class="section-title" data-for="dynamicroutesource">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route Lookup Source')?></h3>
						</div>
						<div class="section" data-id="dynamicroutesource">
							<!--Source Type-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="sourcetype"><?php echo _("Source Type") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="sourcetype"></i>
												</div>
												<div class="col-md-9">
													<select class="form-control" id="sourcetype" name="sourcetype">
														<?php echo $sourcetypeopts?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="sourcetype-help" class="help-block fpbx-help-block"><?php echo _("The source of the information to be looked up.")?></span>
									</div>
								</div>
							</div>
							<!--END Source Type-->
							<!--Enable Substitutions-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="enable_substitutions"><?php echo _("Enable substitutions") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="enable_substitutions"></i>
												</div>
												<div class="col-md-9 radioset">
													<input type="radio" name="enable_substitutions" id="substyes" value="CHECKED" <?php echo ($dynroute['enable_substitutions']=='CHECKED'?'CHECKED':'') ?>>
													<label for="substyes"><?php echo _("Yes");?></label>
													<input type="radio" name="enable_substitutions" id="substno" value="" <?php echo ($dynroute['enable_substitutions']=='CHECKED'?'':'CHECKED') ?>>
													<label for="substno"><?php echo _("No");?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="enable_substitutions-help" class="help-block fpbx-help-block"><?php echo _("Enable variable substitutions in the lookup query (for [INPUT], [NUMBER], [DID] or [name] where name is a Saved Input Variable Name or Saved Result Variable Name from a previous Dynamic Route). You probably want to disable this if using a lookup type of Asterisk Variable and a REGEX expression in the lookup since substitions can be interpreted wrongly.")?></span>
									</div>
								</div>
							</div>
							<!--End Enable substitutions-->
							<!--Mysql host-->
							<div class="element-container src_mysql">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="mysql_host"><?php echo _("MySQL hostname") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="mysql_host"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="mysql_host" name="mysql_host" value="<?php echo htmlspecialchars($dynroute['mysql_host'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="mysql_host-help" class="help-block fpbx-help-block"><?php echo _("Hostname of MySQL server")?></span>
									</div>
								</div>
							</div>
							<!--END Mysql host-->
							<!--Mysql db-->
							<div class="element-container src_mysql">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="mysql_dbname"><?php echo _("MySQL database") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="mysql_dbname"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="mysql_dbname" name="mysql_dbname" value="<?php echo htmlspecialchars($dynroute['mysql_dbname'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="mysql_dbname-help" class="help-block fpbx-help-block"><?php echo _("Database to use.")?></span>
									</div>
								</div>
							</div>
							<!--END Mysql db-->
							<!--Mysql Username-->
							<div class="element-container src_mysql">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="mysql_username"><?php echo _("MySQL username") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="mysql_username"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="mysql_username" name="mysql_username" value="<?php echo htmlspecialchars($dynroute['mysql_username'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="mysql_username-help" class="help-block fpbx-help-block"><?php echo _("Username to use for connection to MySQL server")?></span>
									</div>
								</div>
							</div>
							<!--END Mysql Username-->
							<!--Mysql Password-->
							<div class="element-container src_mysql">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="mysql_password"><?php echo _("MySQL password") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="mysql_password"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="mysql_password" name="mysql_password" value="<?php echo htmlspecialchars($dynroute['mysql_password'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="mysql_password-help" class="help-block fpbx-help-block"><?php echo _("Password to use for connection to MySQL server")?></span>
									</div>
								</div>
							</div>
							<!--END Mysql Password-->
							<!--Mysql Query-->
							<div class="element-container src_mysql">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="mysql_query"><?php echo _("MySQL query") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="mysql_query"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="mysql_query" name="mysql_query" value="<?php echo htmlspecialchars($dynroute['mysql_query'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="mysql_query-help" class="help-block fpbx-help-block"><?php echo _("Query to use to obtain the result from the MySQL database. The following substitutions are available:<br>[NUMBER] the callerid number<br>[INPUT] the dtmf sequence input<br>[DID] the dialed number<br>[xxx] where xxx is the name of an input or result variable saved from a previous dynamic route on the same call")?></span>
									</div>
								</div>
							</div>
							<!--END Mysql Query-->
							<!--Mysql spacer-->
							<div class="element-container src_mysql">
							</div>
							<!--END Mysql spacer-->
							<!--ODBC Function-->
							<div class="element-container src_odbc">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="odbc_func"><?php echo _("ODBC Function") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="odbc_func"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="odbc_func" name="odbc_func" value="<?php echo htmlspecialchars($dynroute['odbc_func'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="odbc_func-help" class="help-block fpbx-help-block"><?php echo _("ODBC Function to use. The value used here should be the name of a section in /etc/asterisk/func_odbc.conf without the ODBC_ prefix that asterisk adds. The dynroute module will add the ODBC_ prefix. For debugging you can also check if the ODBC function is registered at the asterisk console with \"core show functions \".")?></span>
									</div>
								</div>
							</div>
							<!--END ODBC Function-->
							<!--ODBC Query-->
							<div class="element-container src_odbc">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="odbc_query"><?php echo _("ODBC query") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="odbc_query"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="odbc_query" name="odbc_query" value="<?php echo htmlspecialchars($dynroute['odbc_query'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="odbc_query-help" class="help-block fpbx-help-block"><?php echo _("Query to use to obtain the result from the database. The following substitutions are available:<br>[NUMBER] the callerid number<br>[INPUT] the dtmf sequence input<br>[DID] the dialed number<br>[xxx] where xxx is the name of an input or result variable saved from a previous dynamic route on the same call")?></span>
									</div>
								</div>
							</div>
							<!--END ODBC Query-->
							<!--URL Lookup-->
							<div class="element-container src_url">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="url_query"><?php echo _("URL Lookup") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="url_query"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="url_query" name="url_query" value="<?php echo htmlspecialchars($dynroute['url_query'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="url_query-help" class="help-block fpbx-help-block"><?php echo _("URL to use to obtain the result (it must return text only, no html, xml or json. Exmaple http://localhost/test.php?param1=4&param2=9 The following substitutions are available:<br>[NUMBER] the callerid number<br>[INPUT] the dtmf sequence input<br>[DID] the dialed number<br>[xxx] where xxx is the name of an input or result variable saved from a previous dynamic route on the same call")?></span>
									</div>
								</div>
							</div>
							<!--END URL Lookup-->
							<!--URL spacer-->
							<div class="element-container src_url">
							</div>
							<!--END URL spacer-->
							<!--AGI Lookup-->
							<div class="element-container src_agi">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="agi_query"><?php echo _("AGI Lookup") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="agi_query"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="agi_query" name="agi_query" value="<?php echo htmlspecialchars($dynroute['agi_query'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="agi_query-help" class="help-block fpbx-help-block"><?php echo _("AGI to use to obtain the result (it must return text only, no html, xml or json. For example test.agi,param1,param2 The following substitutions are available for use in the input parameters:<br>[NUMBER] the callerid number<br>[INPUT] the dtmf sequence input<br>[DID] the dialed number<br>[xxx] where xxx is the name of an input or result variable saved from a previous dynamic route on the same call")?></span>
									</div>
								</div>
							</div>
							<!--END AGI Lookup-->
							<!--AGI Result Variable-->
							<div class="element-container src_agi">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="agi_var_name_res"><?php echo _("AGI Result Variable") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="agi_var_name_res"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="agi_var_name_res" name="agi_var_name_res" value="<?php echo htmlspecialchars($dynroute['agi_var_name_res'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="agi_var_name_res-help" class="help-block fpbx-help-block"><?php echo _("Name of result variable used in AGI script.")?></span>
									</div>
								</div>
							</div>
							<!--END AGI Result Variabl-->
							<!--Asterisk Variable Lookup-->
							<div class="element-container src_astvar">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="astvar_query"><?php echo _("Asterisk Variable") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="astvar_query"></i>
												</div>
												<div class="col-md-9">
													<input type="text" max="10" class="form-control" id="astvar_query" name="astvar_query" value="<?php echo htmlspecialchars($dynroute['astvar_query'],ENT_QUOTES)?>">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="url_query-help" class="help-block fpbx-help-block"><?php echo _("Asterisk variable whose value is to be used. The following substitutions are available:<br>[NUMBER] the callerid number<br>[INPUT] the dtmf sequence input<br>[DID] the dialed number<br>[xxx] where xxx is the name of an input or result variable saved from a previous dynamic route on the same call")?></span>
									</div>
								</div>
							</div>
							<!--END Asterisk Variable Lookup-->
							<!--Asterisk Variable spacer-->
							<div class="element-container src_astvar">
							</div>
							<!--END Asterisk Variable spacer-->
						</div>
						<div class="section-title" data-for="dynamicroutedefaultdest">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route Default Entry')?></h3>
						</div>
						<div class="section" data-id="dynamicroutedefaultdest">
							<!--Default Destination-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="gotodefault"><?php echo _("Default Destination") ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="gotodefault"></i>
												</div>
												<div class="col-md-9">
													<?php echo drawselects($dynroute['default_dest'],'default')?>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="gotodefault-help" class="help-block fpbx-help-block"><?php echo _("Destination to send the call to if there is no match in the Dynamic Route Entries section below or if the lookup fails or returns an empty value.")?></span>
									</div>
								</div>
							</div>
							<!--END Default Destination-->
						</div>
						<div class="section-title" data-for="dynrouteentries">
							<h3><i class="fa fa-minus"></i> <?php echo _('Dynamic Route Entries')?></h3>
						</div>
						<div class="section" data-id="dynrouteentries">
							<?php echo dynroute_draw_entries($dynroute['id'])?>
						</div>
						</form>
						<?php echo $hookhtml?>
					</div>
				</div>
			</div>
			<div class="col-sm-3 bootnav <?php echo $fw_popover?'hidden':''?>">
				<div class="list-group">
					<?php echo load_view(__DIR__.'/rnav.php')?>
				</div>
			</div>
		</div>
	</div>
</div>
