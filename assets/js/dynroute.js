// Copyright (c) 2015-2022 John Fawcett
// This is a dervied work licenced under GPL V3 or later
// The original file was published by Sagoma Technologies in
// Freepbx IVR module

$("#duplicate").click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $('input[name="action"]').val("save");
        name = $('input[name="name"]').val();
        $('input[name="name"]').val(name + "_COPY_");
        $('input[name="id"]').val("");
        $("#extdisplay").val("");
        $("#frm_dynroute").submit();
});


$(document).ready(function(){

	$('#add_entrie').click(function(e){
		e.preventDefault();
		// we get this each time in case a popOver has updated the array
		new_entrie = '<tr>' + $('#gotoDESTID').parents('tr').html() + '</tr>';
		id = new Date().getTime();//must be cached, as we have many replaces to do and the time can shift
		thisrow = $('#dynroute_entries > tbody:last').find('tr:last').after(new_entrie.replace(/DESTID/g, id));
		$('.destdropdown2', $(thisrow).next()).addClass('hidden');
		bind_dests_double_selects();
	});

	$('input[type=submit]').click(function(){
		//remove the last blank field so that it isnt subject to validation, assuming it wasnt set
		//called from .click() as that is fired before validation
		last = $('#dynroute_entries > tbody:last').find('tr:last');
		if(last.find('input[name="entries[ext][]"]').val() == ''
			&& last.find('.destdropdown').val() == ''){
			last.remove()
		}
	});
	if($('form[name=frm_dynroute]').length > 0){
		//fix for popovers because jquery wont bubble up a real "submit()" correctly.
		//See FREEPBX-8122 for more information
		$('form[name=frm_dynroute]')[0].onsubmit = function() {

			invalid = $('[name=' + $('[name=gotoinvalid]').val() + 'invalid]').val();
			$('#invalid_dest').val(invalid);

			defaultdest = $('[name=' + $('[name=gotodefault]').val() + 'default]').val();
			$('#default_dest').val(defaultdest);

			//set goto fileds for destinations
			$('[name^=goto]').each(function(){
				num = $(this).attr('name').replace('goto', '');
				dest = $('[name=' + $(this).val() + num + ']').val();
				$(this).parent().find('input[name="entries[goto][]"]').val(dest);
				//console.log(num, dest, $(this).parent().find('input[name="entries[goto][]"]').val())
			});

			//disable dests so that they dont get posted
			$('.destdropdown, .destdropdown2').attr("disabled", "disabled");

			setTimeout(restore_form_elemens, 100);
		}
	}

	//delete rows on click
	$(document).on('click','.delete_entrie', function(e){	
		e.preventDefault();
		$(this).closest('tr').fadeOut('normal', function(){$(this).closest('tr').remove();});
	});

	$('#sourcetype').change(function(e){
		hide_show_source_type()
	});
	hide_show_source_type()

});

function hide_show_source_type() {
		src=$('#sourcetype').val();
		if(src=='mysql') {
			$('.src_mysql').show();
			$('.src_odbc').hide();
			$('.src_url').hide();
			$('.src_agi').hide();
			$('.src_astvar').hide();
		}
		else if(src=='odbc') {
			$('.src_mysql').hide();
			$('.src_odbc').show();
			$('.src_url').hide();
			$('.src_agi').hide();
			$('.src_astvar').hide();
		}
		else if(src=='url') {
			$('.src_mysql').hide();
			$('.src_odbc').hide();
			$('.src_url').show();
			$('.src_agi').hide();
			$('.src_astvar').hide();
		}
		else if(src=='agi') {
			$('.src_mysql').hide();
			$('.src_odbc').hide();
			$('.src_url').hide();
			$('.src_agi').show();
			$('.src_astvar').hide();
		}
		else if(src=='astvar') {
			$('.src_mysql').hide();
			$('.src_odbc').hide();
			$('.src_url').hide();		
			$('.src_agi').hide();		
			$('.src_astvar').show();
		}
		else {
			$('.src_mysql').hide();
			$('.src_odbc').hide();
			$('.src_url').hide();		
			$('.src_agi').hide();		
			$('.src_astvar').hide();
		}
}

function restore_form_elemens() {
	$('.destdropdown, .destdropdown2').removeAttr('disabled');
}



function actionFormatter(value){
	var html = '<a href="?display=dynroute&action=edit&id='+value[0]+'"><i class="fa fa-pencil"></i></a>&nbsp;';
	html += '<a href="?display=dynroute&action=delete&id='+value[0]+'" class="delAction"><i class="fa fa-trash"></i></a>&nbsp;';
	return html;
}
function bnavFormatter(value){
	var html = '<a href="?display=dynroute&action=edit&id='+value[0]+'"><i class="fa fa-pencil"></i>&nbsp;'+_("Edit:")+'&nbsp;'+value[1]+'</a>';
	return html;
}

