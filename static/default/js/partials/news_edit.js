var _authorId = 0;

$(function() {
	$.datepicker.setDefaults({
		dateFormat: 'dd.mm.yyyy',
		changeMonth: true,
		changeYear: true
	});
});


tinyMCE.init({
	// General options
	mode : "exact",  
	elements : "html",
	theme : "advanced",
	plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,preview,media,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,|,bullist,numlist,|,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,preview",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,insertfile,insertimage",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,

	// Skin options
	skin : "o2k7",
	skin_variant : "silver"

});


$(function() {
	$("input[name='date']").datepicker($.datepicker.regional["ru"] = {
		dateFormat: 'dd.mm.yy'
	});
	$("input[name='date']").datepicker($.datepicker.regional["ru"] = {
		dateFormat: 'dd.mm.yy'
	});
});
