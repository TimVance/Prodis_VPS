<?php
/**
 * Обработка с поисковой фразы
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
 * Searchwords
 */
class Searchwords
{
	/**
	 * @var integer максимальная длина слова
	 */
	public $max_length;

	/**
	 * Выделение уникальных слов из текста
	 * 
	 * @param string $text индексируемый текст
	 * @return array
	 */
	public function prepare($text)
	{
		if(! $this->max_length)
		{
			$this->max_length = 3;
		}
		$lang_name = $this->get_lang(defined('_LANG')? _LANG : 0);
		
		if($lang_name == 'ru')
		{
			Custom::inc('plugins/stemmer.php');
			$processor = new Lingua_Stem_Ru();
		}
		
		$text = utf::strtolower(strip_tags($text));
		if(strlen($text) > 100000)
		{
			$text = substr($text, 0, 100000);
		}
		$text = html_entity_decode($text, ENT_COMPAT, "UTF-8");
		$text = str_replace(array('&nbsp;', '«', '»'), array(' ', '"', '"'), $text);
		$text = preg_replace('/\s+|[\.,:;\"\'\/\\!\?\(\)\-]/u', ' ', $text);
		$text = preg_replace('/[^a-zабвгдеёжзийклмнопрстуфхцчшщъыьэюя0-9 ]+/u', '', $text);
		$matchesarray = explode(' ', $text);

		foreach ($matchesarray as $key => $value)
		{
			if(utf::strlen($value) < $this->max_length)
			{
				unset ($matchesarray[$key]);
			}
		}

		$matchesarray = array_flip(array_flip($matchesarray));

		$words = array();
		foreach ($matchesarray as $word)
		{
			if($lang_name == 'ru')
			{
				$word = $processor->stem_word($word);
			}
			
			$words[] = $word; 
		}
		
		$words = array_unique($words);

		return $words;
	}
	
	/**
	 * Получение языка сайта 
	 *
	 * @param string $lang идентификатор языка
	 *  @return string
	 */
	private function get_lang($lang = 0)
	{
		global $diafan;
		$lang = (int) $lang;
		foreach ($diafan->_languages->all as $language)
		{
			if($language["id"] == $lang)
			{
				if($language["shortname"] == 'ru' || $language["shortname"] == 'rus')
				{
					return 'ru';
				}
				else
				{
					return $language["shortname"];
				}
			}
		}
		return 'ru';
	}
}