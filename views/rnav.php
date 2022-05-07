<?php
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module
?>
<a href="config.php?display=dynroute" class = "list-group-item <?php echo ($_REQUEST['id'] == ''?'hidden':'')?>"><i class="fa fa-list"></i>&nbsp;<?php echo _("List Dynamc Routes")?></a>
<a href="config.php?display=dynroute&action=add" class = "list-group-item"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add Dynamic Route")?></a>
<?php if($_REQUEST['action'] != ''){
?>
<table id="dynroutenavgrid" data-url="ajax.php?module=dynroute&command=getJSON&jdata=grid" data-cache="false" data-height="299" data-toggle="table" class="table table-striped">
	<thead>
			<tr>
			<th data-field="link" data-formatter="bnavFormatter"><?php echo _("Dynamic Route List")?></th>
		</tr>
	</thead>
</table>

<?php
}
