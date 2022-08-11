<?php

class text extends text_Core{

	static function print_r($subj, $goexit = false){
		echo '<br><pre>';
		print_r($subj);
		echo '</pre><br>';
		if($goexit) exit;
	}
	
	static function out($subj, $goexit = false){
		if(is_array($subj) or is_object($subj)) return self::print_r($subj, $goexit);
		echo '--: ';
		echo($subj);
		echo ' :--<br>';
		if($goexit) exit;
	}

	static function break_long_words($str, $break_width = 30) {
		$ret = preg_replace("/(([\d\pL\pP]){{$break_width}})(?=[\pL\d\pP])/ui", '$1 ', $str);
		return $ret ? $ret : $str;
	}
	
	/**
	 * Wraps urls and breaks long words
	 *
	 * @param string $plain_text
	 * @param int $break_width - length to break words by
	 * @param boolean $check_fl_domain - whether to check first level domain in urls by the list
	 * @return string
	 */
	
	static function wordwrap_decorate_urls($plain_text, $break_width = 30, $decorate_urls = true) {
		
		$plain_text = trim($plain_text);

		if ($decorate_urls) {
			$add_http_to_www_regex =
				"~(?<!http://)www\.(?<domain>([-a-z\d]{1,63}\.)+(?<fldomain>[-a-z]{1,63}))(?<path>/[-a-z\d+&@#/%=\~_|\!:,.;]*[a-z\d#/_])?(?<parameters>\/?\?[-a-z\d+&@#/%=\~_|\!:,.;]*[a-z\d/#&=])?~iu";

			$plain_text = preg_replace($add_http_to_www_regex, 'http://$0', $plain_text);
		}

		$regex = "!(((?<protocol>https?|ftp)://)|((?<user>[-a-z\d_.]+)@)|www\.)(?<domain>([-a-z\d]{1,63}\.)+(?<fldomain>[-a-z]{1,63}))(?<path>/[-a-z\d+&@#/%=~_|\!:,.;]*[a-z\d#/_])?(?<parameters>\/?\?[-a-z\d+&@#/%=~_|\!:,.;]*[a-z\d/#&=])?|(?<long_word>[\d\pP\pL]{{$break_width},})!iu";
		$domains = array(
			'com', 'org', 'info', 'biz', 'name', 'aero', 'arpa', 'edu', 'int', 'gov', 'mil', 'coop', 'museum', 'mobi', 'travel',
			'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'az',
			'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw',
			'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv',
			'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu',
			'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp',
			'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im',
			'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr',
			'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'mg',
			'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na',
			'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk',
			'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se',
			'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'st', 'su', 'sv', 'sy', 'sz', 'tc', 'td', 'tf',
			'tg', 'th', 'tj', 'tk', 'tm', 'tn', 'to', 'tp', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us',
			'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'za', 'zm', 'zw'
		);

		$matches = array();
		if (preg_match_all($regex, $plain_text, $matches)) {
			$urls = $matches[0];
			$search = $replace = array();

			foreach($urls as $key => $item) {

				if (in_array($item, $search)) {
					continue;
				}

				$found_url = in_array($matches['fldomain'][$key], $domains);

				if ($decorate_urls && $found_url) {
					$search[] = $item;
					if (!empty($matches['user'][$key])) {
						$replace[] = html::mailto($item);
					} else {
						$first_part = floor($break_width * 0.8);
						$second_part = floor($break_width * 0.2);
						$title = preg_replace("/(.{{$first_part}}).+(.{{$second_part}})/ui", '$1...$2', $item);
						$protocol = $matches['protocol'][$key] ? '' : 'http://';
						$replace[] = "<noindex><a href='{$protocol}{$item}' ref='nofollow'>$title</a></noindex>";
					}
				} elseif(!$found_url) {
					$search[] = $item;
					$replace[] = self::break_long_words($item, $break_width);
				}
			}

			return str_replace($search, $replace, $plain_text);
		}

		return $plain_text;
	}


	static function undecorate_urls($text){
		return preg_replace('/(<noindex>)?<a.*?href=["\']?(mailto:|&#109;&#097;&#105;&#108;&#116;&#111;&#058;)?([^"\']*)["\']?.*?>(.*?)<\/a>(<\/noindex>)?/ius', "$3", $text);
	}

	static function getSentence(&$string, $length = 150){
		preg_match('/^(.{1,'.$length.'}[^\.]*\.[\s$])(.*)/us', $string, $matches);
		return $matches;
	}

	static function removeBeginEndPunctuation($text){
		$text = '!'. $text;
		return trim(preg_replace('~(^[^а-я0-9a-z"«\(]*)|([^0-9a-zа-я»"\)]*$)~ui', '', $text));
	}

	static function capsFix($text, $is_text = false){
		
		$len = utf8::strlen($text);
		
		if($len <= 12) return $text;
		
		$capscount = preg_match_all('/[A-ZА-Я]/u', $text, $m);
		
		if(
			($len <= 20 and $capscount > $len*.7) 
			or ((!$is_text or $len <= 100) and $capscount > $len*.4)
			or ($len > 100 and $capscount > $len*.15)):
				$text = utf8::strtolower($text);
		endif;
		
		return $text;	
		
	}

	static function ucSentence($text){
		$sentences = preg_split('/[\.!\?;]+\s/u', $text);

		foreach($sentences as $key => $item):
			$sentences[$key] = utf8::ucfirst($item);
		endforeach;
		
		return join('. ', $sentences);
	}

	static function xml_convert($str)
	{
		$temp = '__TEMP_AMPERSANDS__';

		// Replace entities to temporary markers so that 
		// ampersands won't get messed up
		$str = str_replace(array("&nbsp;"),array("&#160;"),$str);
		
		$str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
		$str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);
		
		$str = str_replace(array("&","<",">","\"", "'", "-"),
						   array("&amp;", "&lt;", "&gt;", "&quot;", "&#39;", "&#45;"),
						   $str);

		// Decode the temp markers back to entities		
		$str = preg_replace("/$temp(\d+);/","&#\\1;",$str);
		$str = preg_replace("/$temp(\w+);/","&\\1;", $str);
		
		return $str;
	}


/**
 * Shrink text to a given length and adds expand link
 */

	static function shrinkText(&$text, $length = 350){
		$part = self::getSentence($text, $length);
		if(!empty($part[2]))
			return '<div class="short">'.strip_tags($part[1]).'<a href="#" class="more" onclick="$(this).parent().next().show(); $(this).parent().hide(); return false">Читать далее →</a></div><div class="full">'.$text.'</div>';
		else
			return $text;
	}
	

	/**
	 * TYPOGRAPHY
	 */
	
	// Block level elements that should not be wrapped inside <p> tags
	static $block_elements = 'div|blockquote|pre|code|h\d|script|ol|ul';
	
	// Elements that should not have <p> and <br /> tags within them.
	static $skip_elements	= 'pre|ol|ul';
	
	// Tags we want the parser to completely ignore when splitting the string.
	static $ignore_elements = 'a|b|i|em|strong|span|img|li';	
	
	
	static function HTML2String($text, $removeNewLines = FALSE, $charset = 'UTF-8'){
		if($removeNewLines):
			return preg_replace("/(\r\n|\r|\n)+/ius", " ", html_entity_decode(strip_tags($text), ENT_QUOTES, $charset));
		else:
			return html_entity_decode(strip_tags($text), ENT_QUOTES, $charset);
		endif;
	}
	
	static function untypography($text, $charset = 'UTF-8'){
		return html_entity_decode(preg_replace('/\n*<br\s?\/?>\n*/ius', "\n", preg_replace("/<\/?p>/i", '', str_replace("</p>\n\n<p>", "\n\n", $text))), ENT_QUOTES, $charset);
	}

	static function typographyString($text){
		return trim(self::typography($text, TRUE));
	}

	/**
	 * Main Processing Function
	 */

	static function typography($str, $singleString = FALSE)
	{
	
		$str = preg_replace('/[\xa0]/iu', ' ', $str);
		
		$str = preg_replace('/\\\/','/', $str); // заменяем бэкслэши
		
		$str = preg_replace('/(\s|&nbsp;)([-])([^\d\s])/iu','$1$2 $3', $str); // добавляем пробелы после тире
		$str = preg_replace('/(\s|&nbsp;)([–—])([^\s])/iu','$1$2 $3', $str); // добавляем пробелы после тире
		$str = preg_replace('/(\d)([-–—])(\s)/iu','$1 $2$3', $str); // добавляем пробелы перед тире
	
			
	
		if ($str == '')
		{
			return '';
		}
		
		/* mark quotes */
		$quote_regexp = '/([^=][\x01-(\s\"]|\A|>|&nbsp;)(\")([^\"]{1,})([^\s\"(])(\")/iu';
		$str = preg_replace($quote_regexp, '$1«$3$4»', $str);
	
		if (preg_match('/"/', $str)){
			$str = preg_replace('/([\x01(\s\"]|\A|>|&nbsp;)(\")([^\"]{1,})([^\s\"(])(\")/iu',
			"$1«$3$4»", $str);
			while (preg_match('/(«)([^»]*)(«)/iu', $str)){
				$str = preg_replace('/(«)([^»]*)(«)([^»]*)(»)/iu', "$1$2&bdquo;$4&ldquo;", $str, 1);
			}
		}
		/* end of quotes */
		
		// removing excess whitespaces in text
		$str = preg_replace('/([ \t])+/mui', '$1', $str);                             
		// removing whitespaces in the beginning and in the end of the string
		$str = preg_replace('/^[ \t]*(.*?)[ \t]*$/mui', "$1", $str);
		// em-dash              
		$str = preg_replace('/([ \t]+|&nbsp;)\-(\s+|&nbsp;)/miu', '&nbsp;&#8212;$2', $str);

		$str = ' '.$str.' ';
		
		// Standardize Newlines to make matching easier
		$str = preg_replace("/(\r\n|\r)/", "\n", $str);

		
		/*
		 * Reduce line breaks
		 *
		 * If there are more than two consecutive line
		 * breaks we'll compress them down to a maximum
		 * of two since there's no benefit to more.
		 *
		 */
		$str = preg_replace("/\n\n+/", "\n\n", $str);

		/*
		 * Convert quotes within tags to temporary marker
		 *
		 * We don't want quotes converted within
		 * tags so we'll temporarily convert them to
		 * {@DQ} and {@SQ}
		 *
		 */			
		if (preg_match_all("#\<.+?>#si", $str, $matches))
		{
			for ($i = 0; $i < count($matches['0']); $i++)
			{
				$str = str_replace($matches['0'][$i],
									str_replace(array("'",'"'), array('{@SQ}', '{@DQ}'), $matches['0'][$i]),
									$str);
			}
		}
	

		/*
		 * Add closing/opening paragraph tags before/after "block" elements
		 *
		 * Since block elements (like <blockquotes>, <pre>, etc.) do not get
		 * wrapped in paragraph tags we will add a closing </p> tag just before
		 * each block element starts and an opening <p> tag right after the block element
		 * ends.  Later on we'll do some further clean up.
		 *
		 */
		$str = preg_replace("#(<)(".self::$block_elements.")(.*?>)#", "</p>\\1\\2\\3", $str);
		$str = preg_replace("#(</)(".self::$block_elements.")(.*?>)#", "\\1\\2\\3<p>", $str);
	
		/*
		 * Convert "ignore" tags to temporary marker
		 *
		 * The parser splits out the string at every tag
		 * it encounters.  Certain inline tags, like image
		 * tags, links, span tags, etc. will be adversely
		 * affected if they are split out so we'll convert
		 * the opening < temporarily to: {@TAG}
		 *
		 */		
		$str = preg_replace("#<(/*)(".self::$ignore_elements.")#i", "{@TAG}\\1\\2", $str);	
		
		/*
		 * Split the string at every tag
		 *
		 * This creates an array with this prototype:
		 *
		 *	[array]
		 *	{
		 *		[0] = <opening tag>
		 *		[1] = Content contained between the tags
		 *		[2] = <closing tag>
		 *		Etc...
		 *	}
		 *
		 */			
		$chunks = preg_split('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		
		/*
		 * Build our finalized string
		 *
		 * We'll cycle through the array, skipping tags,
		 * and processing the contained text
		 *
		 */			
		$str = '';
		$process = TRUE;
		foreach ($chunks as $chunk)
		{
			/*
			 * Are we dealing with a tag?
			 *
			 * If so, we'll skip the processing for this cycle.
			 * Well also set the "process" flag which allows us
			 * to skip <pre> tags and a few other things.
			 *
			 */
			if (preg_match("#<(/*)(".self::$block_elements.").*?\>#", $chunk, $match))
			{
				if (preg_match("#".self::$skip_elements."#", $match['2']))
				{
					$process =  ($match['1'] == '/') ? TRUE : FALSE;		
				}
		
				$str .= $chunk;
				continue;
			}
		
			if ($process == FALSE or $singleString == TRUE)
			{
				$str .= $chunk;
				continue;
			}
			
			//  Convert Newlines into <p> and <br /> tags
			$str .= self::format_newlines($chunk);
		}

		// FINAL CLEAN UP
		// IMPORTANT:  DO NOT ALTER THE ORDER OF THE ITEMS BELOW!
		
		/*
		 * Clean up paragraph tags before/after "block" elements
		 *
		 * Earlier we added <p></p> tags before/after block level elements.
		 * Then, we added paragraph tags around double line breaks.  This
		 * potentially created incorrectly formatted paragraphs so we'll
		 * clean it up here.
		 *
		 */
		$str = preg_replace("#<p>({@TAG}.*?)(".self::$block_elements.")(.*?>)#", "\\1\\2\\3", $str);
		$str = preg_replace("#({@TAG}/.*?)(".self::$block_elements.")(.*?>)</p>#", "\\1\\2\\3", $str);

		// Convert Quotes and other characters
		$str = self::format_characters($str);
		
		// Fix an artifact that happens during the paragraph replacement
		$str = preg_replace('#(<p>\n*</p>)#', '', $str);

		// If the user submitted their own paragraph tags with class data
		// in them we will retain them instead of using our tags.
		$str = preg_replace('#(<p.*?>)<p>#', "\\1", $str);

		// Final clean up
		$str = str_replace(
							array(
									'</p></p>',
									'</p><p>',
									'<p> ',
									' </p>',
									'{@TAG}',
									'{@DQ}',
									'{@SQ}',
									'<p></p>'
								),
							array(
									'</p>',
									'<p>',
									'<p>',
									'</p>',
									'<',
									'"',
									"'",
									'',
								),
							$str
						);


/**
 * Исправляем кривую расстановку знаков препинания
 */
		$str = preg_replace('/([?!,*;\'():_-])\1{2,}/','$1', $str); // удаляем повторные знаки припенания
		
		$str = preg_replace('/\.{2}|\.{4,}/','.', $str); // удаляем повторные точки
		
		$str = preg_replace('/(\S)\s+([,\!\.\?\:])/ium', '$1$2', $str); // пробелы перед символами
		
		$str = preg_replace('/(\D),(\S)/ium','$1, $2', $str); // добавляем пробелы после запятых
		$str = preg_replace('/(.),([^\d\s])/ium','$1, $2', $str); // добавляем пробелы после запятых
		$str = preg_replace('/(.)([\.\?]+)([а-я]{2,})/ium','$1$2 $3', $str); // добавляем пробелы после точек		
		$str = preg_replace('/([а-я])\.+([^\pP\s]{2,})/ium','$1. $2', $str); // добавляем пробелы после точек
		$str = preg_replace('/([а-я]{3,})\.+([^\pP\s])/ium','$1. $2', $str); // добавляем пробелы после точек
		$str = preg_replace('/(.)\!([^\pP\s])/ium','$1! $2', $str); // добавляем пробелы после восклицательных знаков
		
	
		return $str;
		
	}
	
	// --------------------------------------------------------------------

	/**
	 * Format Characters
	 *
	 * This function mainly converts double and single quotes
	 * to entities, but since these are directional, it does
	 * it based on some rules.  It also converts em-dashes
	 * and a couple other things.
	 */
	static function format_characters($str)
	{	
		$table = array(
						' "'		=> " &#8220;",
						'" '		=> "&#8221; ",
						" '"		=> " &#8216;",
						"' "		=> "&#8217; ",
						
						'>"'		=> ">&#8220;",
						'"<'		=> "&#8221;<",
						">'"		=> ">&#8216;",
						"'<"		=> "&#8217;<",

						"\"."		=> "&#8221;.",
						"\","		=> "&#8221;,",
						"\";"		=> "&#8221;;",
						"\":"		=> "&#8221;:",
						"\"!"		=> "&#8221;!",
						"\"?"		=> "&#8221;?",
						
						".  "		=> ".&nbsp; ",
						"?  "		=> "?&nbsp; ",
						"!  "		=> "!&nbsp; ",
						":  "		=> ":&nbsp; ",
					);

		// These deal with quotes within quotes, like:  "'hi here'"
		$start = 0;
		$space = array("\n", "\t", " ");
		
		while(TRUE)
		{
			$current = strpos(substr($str, $start), "\"'");
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+2, 1);
			
			if ( ! in_array($one_after, $space, TRUE) && $one_after != "<")
			{
				$str = str_replace(	$one_before."\"'".$one_after,
									$one_before."&#8220;&#8216;".$one_after,
									$str);
			}
			elseif ( ! in_array($one_before, $space, TRUE) && (in_array($one_after, $space, TRUE) OR $one_after == '<'))
			{
				$str = str_replace(	$one_before."\"'".$one_after,
									$one_before."&#8221;&#8217;".$one_after,
									$str);
			}
			
			$start = $start+$current+2;
		}
		
		$start = 0;
		
		while(TRUE)
		{
			$current = strpos(substr($str, $start), "'\"");
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+2, 1);
			
			if ( in_array($one_before, $space, TRUE) && ! in_array($one_after, $space, TRUE) && $one_after != "<")
			{
				$str = str_replace(	$one_before."'\"".$one_after,
									$one_before."&#8216;&#8220;".$one_after,
									$str);
			}
			elseif ( ! in_array($one_before, $space, TRUE) && $one_before != ">")
			{
				$str = str_replace(	$one_before."'\"".$one_after,
									$one_before."&#8217;&#8221;".$one_after,
									$str);
			}
			
			$start = $start+$current+2;
		}
		
		// Are there quotes within a word, as in:  ("something")
		if (preg_match_all("/(.)\"(\S+?)\"(.)/", $str, $matches))
		{
			for ($i=0, $s=sizeof($matches['0']); $i < $s; ++$i)
			{
				if ( ! in_array($matches['1'][$i], $space, TRUE) && ! in_array($matches['3'][$i], $space, TRUE))
				{
					$str = str_replace(	$matches['0'][$i],
										$matches['1'][$i]."&#8220;".$matches['2'][$i]."&#8221;".$matches['3'][$i],
										$str);
				}
			}
		}
		
		if (preg_match_all("/(.)\'(\S+?)\'(.)/", $str, $matches))
		{
			for ($i=0, $s=sizeof($matches['0']); $i < $s; ++$i)
			{
				if ( ! in_array($matches['1'][$i], $space, TRUE) && ! in_array($matches['3'][$i], $space, TRUE))
				{
					$str = str_replace(	$matches['0'][$i],
										$matches['1'][$i]."&#8216;".$matches['2'][$i]."&#8217;".$matches['3'][$i],
										$str);
				}
			}
		}
		
		// How about one apostrophe, as in Rick's
		$start = 0;
		
		while(TRUE)
		{
			$current = strpos(substr($str, $start), "'");
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+1, 1);
			
			if ( ! in_array($one_before, $space, TRUE) && ! in_array($one_after, $space, TRUE))
			{
				$str = str_replace(	$one_before."'".$one_after,
									$one_before."&#8217;".$one_after,
									$str);
			}
			
			$start = $start+$current+2;
		}

		// Em-dashes
		$start = 0;
		while(TRUE)
		{
			$current = strpos(substr($str, $start), "--");
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+2, 1);
			$two_before = substr($str, $start+$current-2, 1);
			$two_after = substr($str, $start+$current+3, 1);
			
			if (( ! in_array($one_before, $space, TRUE) && ! in_array($one_after, $space, TRUE))
				OR
				( ! in_array($two_before, $space, TRUE) && ! in_array($two_after, $space, TRUE) && $one_before == ' ' && $one_after == ' ')
				)
			{
				$str = str_replace(	$two_before.$one_before."--".$one_after.$two_after,
									$two_before.trim($one_before)."&#8212;".trim($one_after).$two_after,
									$str);
			}
			
			$start = $start+$current+2;
		}
		
		// Ellipsis
		$str = preg_replace("#(\w)\.\.\.(\s|<br />|</p>)#", "\\1&#8230;\\2", $str);
		$str = preg_replace("#(\s|<br />|</p>)\.\.\.(\w)#", "\\1&#8230;\\2", $str);
		
		// Run the translation array we defined above		
		$str = str_replace(array_keys($table), array_values($table), $str);
		
		// If there are any stray double quotes we'll catch them here
		
		$start = 0;
		
		while(TRUE)
		{
			$current = strpos(substr($str, $start), '"');
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+1, 1);
			
			if ( ! in_array($one_after, $space, TRUE))
			{
				$str = str_replace(	$one_before.'"'.$one_after,
									$one_before."&#8220;".$one_after,
									$str);
			}
			elseif( ! in_array($one_before, $space, TRUE))
			{
				$str = str_replace(	$one_before."'".$one_after,
									$one_before."&#8221;".$one_after,
									$str);
			}
			
			$start = $start+$current+2;
		}
		
		$start = 0;
		
		while(TRUE)
		{
			$current = strpos(substr($str, $start), "'");
			
			if ($current === FALSE) break;
			
			$one_before = substr($str, $start+$current-1, 1);
			$one_after = substr($str, $start+$current+1, 1);
			
			if ( ! in_array($one_after, $space, TRUE))
			{
				$str = str_replace(	$one_before."'".$one_after,
									$one_before."&#8216;".$one_after,
									$str);
			}
			elseif( ! in_array($one_before, $space, TRUE))
			{
				$str = str_replace(	$one_before."'".$one_after,
									$one_before."&#8217;".$one_after,
									$str);
			}
			
			$start = $start+$current+2;
		}
		
		return $str;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Format Newlines
	 *
	 * Converts newline characters into either <p> tags or <br />
	 *
	 */	
	static function format_newlines($str)
	{
		if ($str == '')
		{
			return $str;
		}

		if (strpos($str, "\n") === FALSE)
		{
			return '<p>'.$str.'</p>';
		}
			
		$str = str_replace("\n\n", "</p>\n\n<p>", $str);
		$str = preg_replace("/([^\n])(\n)([^\n])/", "\\1<br />\\2\\3", $str);
		
		return '<p>'.$str.'</p>';
	}	
	
	static function replace_words(&$str, $words = NULL){
		if(empty($words)) $words = Lib::config('text.replace_words');
		
		foreach($words as &$word):
			$word = '~([\s^])*'.$word.'(\s+|$)~ui';
		endforeach;
		$str = preg_replace($words, '$1', $str, -1, $count);
		return $count;
	}



	public static $tr = array(
		"Ґ"=>"G","Ё"=>"Yo","Є"=>"E","Ї"=>"Yi","І"=>"I",
		"і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"e",
		"ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
		"Д"=>"D","Е"=>"E","Ж"=>"Zh","З"=>"Z","И"=>"I",
		"Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
		"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
		"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"Ts","Ч"=>"Ch",
		"Ш"=>"Sh","Щ"=>"Sch","Ъ"=>"'","Ы"=>"Yi","Ь"=>"",
		"Э"=>"E","Ю"=>"Yu","Я"=>"YA","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'",
		"ы"=>"i","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
		"—"=>'-',
	);

	
	public static function transliterate($text) {
		return preg_replace('/[^,\+\.!\?:;_a-z\d#%@\[\]\{\}\/\\(\)\s\'\"><-]+/iu', '', strtr($text, self::$tr));
	}



}
/* ?> */