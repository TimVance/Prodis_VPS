<?php
/**
 * Подключение для работы с bbCode
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Bbcode_inc
 */
class Bbcode_inc extends Model
{
	/**
	 * Заменяет HTML-теги на bbCode
	 *
	 * @param string $text текст
	 * @return string
	 */
	public function add($text)
	{
		$text = preg_replace("/<li>(.*?)<\/li>/",  "[*]$1\n",  $text);
		$text = preg_replace('/<a href=\"([^\"]+)\" target=\"_blank\">/', '[url=$1]', $text);
		$text = preg_replace('/<a href=([^>]+)>/', '[url=$1]', $text);

		$text = str_replace(
			array(
			      '<div class="code"><div class="code_header">'.$this->diafan->_('Код', false).'</div><code>',
			      '</code></div>',
			      '</a>',
			      '<i>',
			      '</i>',
			      '<b>',
			      '</b>',
			      '<u>',
			      '</u>',
			      '<ul>',
			      '<ol>',
			      '</ol>',
			      '</ul>',
			      '<br>',

				'<img src="'.BASE_PATH.'modules/bbcode/smiles/acute.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bad.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/biggrin.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/blum.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/blush.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bomb.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/boredom.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bye.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/clapping.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/cool.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/cray.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/dance.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/diablo.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/drinks.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/empathy.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/flag_of_truce.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/good.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/help.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/hi.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/i_am_so_happy.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/lol.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/nea.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/negative.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/new_russian.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/wink.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/smile.gif">'
			      ),

			array(
				'[code]',
				'[/code]',
				'[/url]',
				'[i]',
				'[/i]',
				'[b]',
				'[/b]',
				'[u]',
				'[/u]',
				'[LIST]',
				'[LIST=1]',
				'[/LIST]',
				'[/LIST]',
				"\n",

				'[:acute:]',
				'[:bad:]',
				'[:biggrin:]',
				'[:blum:]',
				'[:blush:]',
				'[:bomb:]',
				'[:boredom:]',
				'[:bye:]',
				'[:clapping:]',
				'[:cool:]',
				'[:cray:]',
				'[:dance:]',
				'[:diablo:]',
				'[:drinks:]',
				'[:empathy:]',
				'[:flag_of_truce:]',
				'[:good:]',
				'[:help:]',
				'[:hi:]',
				'[:i_am_so_happy:]',
				'[:lol:]',
				'[:nea:]',
				'[:negative:]',
				'[:new_russian:]',
				'[:wink:]',
				'[:smile:]'
			      ),
			$text
		);
		$text = preg_replace("/<img src=([^>]+)>/", "[img]$1[/img]", $text);
		$text = preg_replace("/<div class=\"quote\"><div class=\"quote_header\">".$this->diafan->_('Цитата', false)."<\/div>([^\t]*?)<\/div>/",
				     '[quote]$1[/quote]',
				     $text);
		return $text;
	}

	/**
	 * Заменяет bbCode на HTML-теги
	 *
	 * @param string $text текст
	 * @param boolean $auto_url автоопределение ссылок
	 * @return string
	 */
	public function replace($text, $auto_url = true)
	{
		$text = htmlspecialchars($text);

		$text = preg_replace("/\[LIST=1\]([^\t]*?)\[\/LIST\]/", "<ol>$1</ol>", $text);
		$text = preg_replace("/\[\*\]([^\r\n]+)\r\n/", "<li>$1</li>", $text);

		Custom::inc('includes/validate.php');
		$text = preg_replace_callback("/\[url=([^\]]+)\](.*?)\[\/url\]/", array('Bbcode_inc', '_url_callback'), $text);
		$text = preg_replace_callback("/\[url\](.*?)\[\/url\]/", array('Bbcode_inc', '_url_full_callback'), $text);
		$text = preg_replace_callback("/\[img\](.*?)\[\/img\]/", array('Bbcode_inc', '_img_callback'), $text);

		$text = str_replace(
			array(
				'[code]',
				'[/code]',
				'[quote]',
				'[/quote]',
				'[i]',
				'[/i]',
				'[b]',
				'[/b]',
				'[u]',
				'[/u]',
				'[LIST]',
				'[/LIST]',
				"\n",

				'[:acute:]',
				'[:bad:]',
				'[:biggrin:]',
				'[:blum:]',
				'[:blush:]',
				'[:bomb:]',
				'[:boredom:]',
				'[:bye:]',
				'[:clapping:]',
				'[:cool:]',
				'[:cray:]',
				'[:dance:]',
				'[:diablo:]',
				'[:drinks:]',
				'[:empathy:]',
				'[:flag_of_truce:]',
				'[:good:]',
				'[:help:]',
				'[:hi:]',
				'[:i_am_so_happy:]',
				'[:lol:]',
				'[:nea:]',
				'[:negative:]',
				'[:new_russian:]',
				'[:wink:]',
				'[:smile:]'
			      ),

			array(
			       '<div class="code"><div class="code_header">'.$this->diafan->_('Код', false).'</div><code>',
				'</code></div>',
				'<div class="quote"><div class="quote_header">'.$this->diafan->_('Цитата', false).'</div>',
				'</div>',
				'<i>',
				'</i>',
				'<b>',
				'</b>',
				'<u>',
				'</u>',
				'<ul>',
				'</ul>',
				'<br>',

				'<img src="'.BASE_PATH.'modules/bbcode/smiles/acute.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bad.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/biggrin.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/blum.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/blush.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bomb.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/boredom.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/bye.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/clapping.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/cool.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/cray.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/dance.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/diablo.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/drinks.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/empathy.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/flag_of_truce.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/good.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/help.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/hi.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/i_am_so_happy.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/lol.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/nea.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/negative.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/new_russian.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/wink.gif">',
				'<img src="'.BASE_PATH.'modules/bbcode/smiles/smile.gif">'
			      ),
			$text
		);
		if($auto_url)
		{
			Custom::inc('includes/validate.php');
			preg_match_all('/(\S+)(\s*)/', $text, $text_array);
	
			$text = '';
			for ($i = 0; $i < count($text_array[1]); $i++)
			{
				if(Validate::url($text_array[1][$i], true))
				{
					$text_array[1][$i] = '<a href="'.$text_array[1][$i].'" target="_blank">'.$text_array[1][$i].'</a>';
				}
				$text .= $text_array[1][$i].$text_array[2][$i];
			}
		}
		return $text;
	}

	/*
	 * Callback функция. Заменяет bbCode изображения на HTML-тег
	 *
	 * @param array $match найденные совпадения
	 * @return string
	 */
	static public function _img_callback($match)
	{
		$url = explode(' ', $match[1]);
		return (Validate::url($url[0], true) ? '<img src='.$url[0].'>' : '');
	}

	/*
	 * Callback функция. Заменяет bbCode ссылки на HTML-тег
	 *
	 * @param array $match найденные совпадения
	 * @return string
	 */
	static public function _url_callback($match)
	{
		$url = explode(' ', $match[1]);
		return (Validate::url($url[0], true) ? '<a href="'.$url[0].'" target="_blank">'.$match[2].'</a>' : $match[2]);
	}

	/*
	 * Callback функция. Заменяет bbCode ссылки с текстом на HTML-тег
	 *
	 * @param array $match найденные совпадения
	 * @return string
	 */
	static public function _url_full_callback($match)
	{
		$url = explode(' ', $match[1]);
		return (Validate::url($url[0], true) ? '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a>' : '');
	}
}