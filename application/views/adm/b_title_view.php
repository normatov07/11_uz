<div class="corns admmenubar"><i class="ct"><i></i><b></b></i>
	<ul class="content">
		<li><a href="/adm/offers/">Объявления</a></li>
		<li><a href="/adm/user/">Пользователи</a>
 		
		</li>
<?php if($this->isAdministrator()):?>

		<li><a href="/adm/category/">Каталог</a>
			<ul>
				<li><a href="/adm/category/">Разделы-подразделы</a></li>
				<li><a href="/adm/list/">Фильтры-списки</a></li>
			</ul></li>
    <li><a href="/adm/region/">Регионы</a></li>
  
		<li><a href="/adm/statistics/">Статистика</a></li>					

<?php endif?>		
		<li><a href="/adm/bonus">Баннер</a></li>
		<li><a href="/" class="front">На фронт</a></li>
	</ul>
	<div class="clb"></div>
<i class="cb"><i></i><b></b></i></div>

<table class="title">
	<tr>
		<th><h1><?php if($this->template->parent_title != '') echo '<b>'. ($this->template->parent_title).':</b> '?><?php if($this->template->title != '') echo $this->template->title;?></h1></th>

	</tr>
</table>