<?php if(!empty($rssoutput)):
echo '<?xml version="1.0" encoding="utf-8"?>' . "
";
?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
    <title><?php echo $title; ?></title>
    <link><?php echo $link; ?></link>
    <description><?php echo $description; ?></description>
    <dc:language><?php echo $language; ?></dc:language>
<?php /*    <dc:creator><?php echo $creator_email; ?></dc:creator> */?>
    <dc:rights>Copyright <?php echo gmdate("Y", time()); ?></dc:rights>
    <admin:generatorAgent rdf:resource="<?=Lib::config('app.url')?>" />
	<lastBuildDate><?=date('r');?></lastBuildDate>
    <?php foreach($items as $entry): ?>
        <item>
		  <guid isPermaLink='false'><?php echo $entry['guid'] ?></guid>
          <title><?php echo text::xml_convert($entry['title']); ?></title>
          <link><?php echo $entry['link'] ?></link>
          <description><![CDATA[<?= $entry['description']?>]]></description>
	      <pubDate><?=$entry['pubdate']?></pubDate>
        </item>       
    <?php endforeach; ?>    
    </channel></rss>
<?php else: ?>
<div class="main rss">
	<div class="lcol">
		<img src="/i/rss-logo.png" width="50" height="50" align="left">
		
		<h2>Что это такое?</h2>		
		<p>RSS — это технология, обеспечивающая возможность оперативного получения свежей информации, не посещая сайты.</p>
		
		<h2>Как это работает?</h2>
		<p>Для того, чтобы оперативно узнавать об обновлениях на сайте, необходимо установить специальную программу — агрегатор новостей — и подключиться к одной из наших RSS-лент:</p>
		<div class="common">
			<h2>Общая лента новинок:</h2>
			<p><a href="/rss/common.xml"><?=Lib::config('app.url')?>/rss/common.xml</a> — получайте все новые объявления из всех разделов.</p>
		</div>
		
		<h2>RSS-ленты категорий:</h2>
		<div class="catalogue">
			<div class="col">
<?php 
		if(!empty($catalog)):
			$i = 0;
			$divider = ceil($catalogRootCount/3);
			foreach($catalog as $item):
				if($item->level > 1) continue;
				if($i > 0 and !($i%$divider)) echo '</div>
				<div class="col">';
				?>					
				<h3><a href="<?=$item->url_rss?>"><?=$item->title?></a></h3>
				<p>
<?php 				$i++;
				
			endforeach;
		endif;	?> 
				</p>
			</div>
	    </div>
        <div class="clb"></div>



	</div>
    <div class="rcol">
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?> 
    </div>

</div>
<?php endif;?>