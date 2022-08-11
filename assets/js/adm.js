$(function(){
/*
ADMIN SUBMENU BAR
*/
	$('.content>li','.admmenubar').bind('mouseover', DDM_open);
    $('.content>li','.admmenubar').bind('mouseout', DDM_timer);

	modifyDigidate();
	$(':input','.digidatetime').attr('disabled','disabled');
	$('.enable_digidate').click(function(){
		$(':input',$(this).parents('.fe').children('.digidatetime')).attr('disabled',this.checked?'':'disabled');
	});
	
// вставка тегов
     $("#add_url").click(function() {
          var url = prompt("Введите url:", 'http://');
          if (url) {
               insertTag(sprintf('<a href="{$url}">', url), '</a>');
          }
     });
     $("#b, #i, #blockquote").click(function() {
          insertTag('<' +  this.id + '>', '</' + this.id + '>');
     });

	function insertTag(openTag, closeTag) {
		 var     content = $("#content").get(0),
			  insertedText = '';
		 
		 if ($.browser.msie) { // kill ie, please
			  $(content).focus();
			  var ieSelection = document.selection,
				   range = ieSelection.createRange();
							 
			  if ((ieSelection.type == 'Text' || ieSelection.type == 'None') && range != null) {
				   insertedText = openTag + range.text + closeTag;
				   range.text = insertedText;
			  }
			  
			  range.moveEnd("character", -closeTag.length);
			  range.select();
		 } else {
			 	var oldVal = $(content).val(),
					selStart = content.selectionStart,
					selEnd = content.selectionEnd,
					delta = $.browser.opera ? // opera counts \n as extra symbol in string
							oldVal.substring(0, selStart).split(/\n/).length - 1 :
							0,
					start = selStart - delta
					end = selEnd - delta;
		
				var selection = oldVal.substring(start, end),
					insertedText = openTag + selection + closeTag;
		
				$(content).val(oldVal.substring(0, start) + insertedText + oldVal.substring(end, oldVal.length));
		
				content.selectionStart = selStart + openTag.length;
				content.selectionEnd = content.selectionStart + selection.length;
				$(content).focus();
    
		 }
	}
// */

});