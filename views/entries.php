<?php
// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module

$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="table table-striped alt_table DREntries" id="dynroute_entries">'));
//build header
$h = array();
foreach($headers as $mod => $header) {
	$h += $header;
}
$table->set_heading($h);

$count = 0;
foreach ($entries as $e) {
	$count++;

	//add ext to dial
	$row[] = form_input(
				array(
					'name'			=> 'entries[ext][]',
					'value'			=> $e['selection'],
					'placeholder'	=> _('value to be matched'),
					'required'		=> ''
				)
			);

	//add destination. The last one gets a different count so that we can manipualte it on the page
	if ($count == count($entries)) {
		$row[] = drawselects($e['dest'], 'DESTID', false, false) . form_hidden('entries[goto][]', '');
	} else {
		$row[] = drawselects($e['dest'], $count, false, false) . form_hidden('entries[goto][]', '');
	}


	//delete buttom
	$row[] = '<a href="#" alt="'
	. _('Delete this entry. Dont forget to click Submit to save changes!')
	. '" class="delete_entrie"><i class="fa fa-trash"></i></a>';

	//add module hooks
	if (isset($e['hooks']) && $e['hooks']) {
		foreach ($e['hooks'] as $module => $hooks) {
			foreach ($hooks as $h) {
				$row[] = $h;
			}
		}

	}


	$table->add_row(array_values($row));

	unset($row);
}

$ret = '';
$ret .= $table->generate();
$ret .= '<a class="DREntries" href="#" id="add_entrie"><i class="fa fa-plus"></i></a>';


echo $ret;

