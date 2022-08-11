$(function(){
	treeInit();
});

function treeInit(){
	
	$('.tree a.s').each(function(){
		$(this).click(function(){treeOpenSubSection(this); return false});
		if($(this).parent('li').children('ul:hidden').get(0) != null){
			
			$(this).text('+');
		}else{
			$(this).html("–");
		};
	});
	$('.tree a.n').click(function(){treeSetAsParentSection(this); return false});
	$('.tree a[name=' + $('input[name=parent_id]', $('#parentSection')).val()+']').parents('li').each(function(){treeOpenSubSection($('a.s',$(this)),'show')});
}

function treeSetParentSection(ID, num, title){
	if($('input[name=id]',$('#parentSection').parents('form')).val() == ID){
		alert('Элемент не может быть родительским');
		return false;
	}
	var parentText = '<input type="hidden" name="parent_id" value="'+ID+'">';
	if(num != null && title != null) parentText += '<a href="#" class="t">'+num+' <b>'+title+'</b></a> <a href="#" onclick="treeSetParentSection()" class="act">удалить</a>';
	else parentText += 'Нет';
	$('#parentSection').html(parentText);
	$('#priority').val('');
	return false;
};

function treeSetAsParentSection(numLink){
	var title = $(numLink).next();
	treeSetParentSection(title.attr('name'), $(numLink).text(), title.text());
	return false;
};

function treeOpenSubSection(signLink, act){
	$(signLink).blur();
	switch(act){
		case 'show':
			$(signLink).parent('li').children('ul').show();
		break;
		case 'hide':
			$(signLink).parent('li').children('ul').hide();
		break;
		default:
			$(signLink).parent('li').children('ul').toggle();
		break;
	}
	if($(signLink).parent('li').children('ul:hidden').get(0) != null){
		$(signLink).text('+');
	}else{
		$(signLink).html("–");
	};
};
