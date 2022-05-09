<?php
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/* $Id$ */
$get_vars = array(
				'action' 		=> '',
				'id'			=> '',
				'display'		=> '',
				'view'			=> ''
);
foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
	$$k = $var[$k];//todo: legacy support, needs to GO!
}
if(!$astman){
	dbug(_("No Astman, Not loading Dynroute"));
	echo "<h1>"._("Connection Error")."</h1>";
	echo '<div class="well well-danger">';
	echo _("A connection to Asterisk Manager could not be made. This module requires Asterisk to be running and have proper credentials");
	echo '</div>';
}else{
	if (!$action && !$id && $view !=='form') {
	?>
	<div class="container-fluid">
		<h1><?php echo _('Dynamic Route')?></h1>
		<div class = "display full-border">
			<div class="row">
				<div class="col-sm-12">
					<div class="fpbx-container">
						<div class="display full-border">
							<div id="toolbar-all">
								<a href="config.php?display=dynroute&action=add" class = "btn btn-default"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add Dynamic Route")?></a>
							</div>
							<table id="dynroutegrid"
							data-url="ajax.php?module=dynroute&command=getJSON&jdata=grid"
							data-cache="false"
						 	data-toggle="table"
							data-cookie="true"
        			data-cookie-id-table="dynroutegrid"
        			data-toolbar="#toolbar-all"
        			data-maintain-selected="true"
        			data-pagination="true"
        			data-search="true"
							 class="table table-striped">
	    						<thead>
	 								<tr>
	        							<th data-sortable="true" data-field="name"><?php echo _("Dyname Route Name")?></th>
									<th data-field="description"><?php echo _("Description")?></th>
	        							<th class="col-md-2" data-field="link" data-formatter="actionFormatter"><?php echo _("Actions")?></th>
	    							</tr>
	    						</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>




	<?php
	}else{
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$id 	= isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

		echo load_view(__DIR__.'/views/form.php', array('request' => $_REQUEST));
	}
}
