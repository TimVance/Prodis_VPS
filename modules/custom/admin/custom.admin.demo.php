<?php
/**
 * Демо-данные для темы
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
 * Custom_admin_demo
 */
class Custom_admin_demo extends Diafan
{
	/**
	 * @var array структура демо-данных для модулей
	 * тип function означает, что данные будут готовятся функцией row_func_NAME()
	 * елси значение будет иметь false, то оно не попадет в файл с данными,
	 * во всех остальных случаях даже пустые значени будут попадать в файл с демо-данными
	 */
	private $tpl = array(
		"site" => array(
			"site" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "theme",
						"type" => "string",
					),
					array(
						"name" => "title_no_show",
						"type" => "boolean",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
				),
			),
			"site_blocks" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "title_no_show",
						"type" => "boolean",
					),
					array(
						"name"=>"rel",
						"type"=>"function"
					)
				),
			),
			"site_dynamic" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "module",
						"type" => "function",
					),
					array(
						"name"=>"element",
						"type"=>"function"
					)
				),
			),
		),
		"menu" => array(
			"menu_category" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "current_link",
						"type" => "boolean",
					),
					array(
						"name" => "show_all_level",
						"type" => "boolean",
					),
					array(
						"name" => "show_title",
						"type" => "boolean",
					),
				),
			),
		),
		"ab" => array(
			"ab_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"ab_param" => array(
				"element_type" => "param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "measure_unit",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "search",
						"type" => "boolean",
					),
					array(
						"name" => "list",
						"type" => "boolean",
					),
					array(
						"name" => "block",
						"type" => "boolean",
					),
					array(
						"name" => "page",
						"type" => "boolean",
					),
					array(
						"name" => "id_page",
						"type" => "boolean",
					),
					array(
						"name" => "display_in_sort",
						"type" => "boolean",
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"ab" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
					array(
						"name" => "param",
						"type" => "function",
					),
				),
			),
		),
		"bs" => array(
			"bs_category" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
				),
			),
			"bs" => array(
				"fields" => array(
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "alt",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "html",
						"type" => "string",
					),
					array(
						"name" => "title",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "integer",
					),
					array(
						"name" => "file",
						"type" => "string",
					),
					array(
						"name" => "link",
						"type" => "text",
						"multilang" => true,
					),
					array(
						"name" => "cat_id",
						"type" => "integer",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "copy",
						"type" => "function",
					),
				),
			),
		),
		"comments" => array(
			"comments_param" => array(
				"element_type" => "param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_list",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form_auth",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form_no_auth",
						"type" => "boolean",
					),
					array(
						"name" => "text",
						"type" => "string",
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"comments" => array(
				"fields" => array(
					array(
						"name" => "text",
						"type" => "string",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "element_type",
						"type" => "string",
					),
					array(
						"name" => "element_id",
						"type" => "integer",
					),
					array(
						"name" => "user_id",
						"type" => "integer",
					),
					array(
						"name" => "param",
						"type" => "function",
					),
				),
			),
		),
		"clauses" => array(
			"clauses_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"clauses" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
				),
			),
		),
		"faq" => array(
			"faq_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"faq" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
				),
			),
		),
		"feedback" => array(
			"feedback_param" => array(
				"element_type" => "param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"feedback" => array(
				"fields" => array(
					array(
						"name" => "user_id",
						"type" => "integer",
					),
					array(
						"name" => "readed",
						"type" => "boolean",
					),
					array(
						"name" => "param",
						"type" => "function",
					),
				),
			),
		),
		"files" => array(
			"files_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"files" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
					array(
						"name" => "attachments",
						"type" => "function",
					),
				),
			),
		),
		"forum" => array(
			"forum" => array(
				"fields" => array(
					array(
						"name" => "cat_id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "user_id",
						"type" => "integer",
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "messages",
						"type" => "function",
					),
				),
			),
			"forum_blocks" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
				),
			),
			"forum_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "block_id",
						"type" => "integer",
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
				),
			),
		),
		"images" => array(
			"images_variations" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "folder",
						"type" => "string",
					),
					array(
						"name" => "param",
						"type" => "string",
					),
					array(
						"name" => "quality",
						"type" => "integer",
					),
				),
			),
		),
		"map" => array(
		),
		"mistakes" => array(
			"mistakes" => array(
				"no_trash" => true,
				"fields" => array(
					array(
						"name" => "url",
						"type" => "string",
					),
					array(
						"name" => "selected_text",
						"type" => "string",
					),
					array(
						"name" => "comment",
						"type" => "string",
					),
				),
			),
		),
		"news" => array(
			"news_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"news" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
				),
			),
		),
		"photo" => array(
			"photo_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"photo" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
				),
			),
		),
		"rating" => array(
			"rating" => array(
				"fields" => array(
					array(
						"name" => "rating",
						"type" => "string",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "element_type",
						"type" => "string",
					),
					array(
						"name" => "element_id",
						"type" => "integer",
					),
					array(
						"name" => "count_votes",
						"type" => "integer",
					),
				),
			),
		),
		"reviews" => array(
			"reviews_param" => array(
				"element_type" => "param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_list",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form_auth",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form_no_auth",
						"type" => "boolean",
					),
					array(
						"name" => "text",
						"type" => "string",
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"reviews" => array(
				"fields" => array(
					array(
						"name" => "text",
						"type" => "string",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					array(
						"name" => "element_type",
						"type" => "string",
					),
					array(
						"name" => "element_id",
						"type" => "integer",
					),
					array(
						"name" => "user_id",
						"type" => "integer",
					),
					array(
						"name" => "param",
						"type" => "function",
					),
				),
			),
		),
		"shop" => array(
			"shop_category" => array(
				"element_type" => "cat",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
					array(
						"name" => "menu",
						"type" => "function",
					),
				),
			),
			"shop_param" => array(
				"element_type" => "param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "measure_unit",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "search",
						"type" => "boolean",
					),
					array(
						"name" => "list",
						"type" => "boolean",
					),
					array(
						"name" => "block",
						"type" => "boolean",
					),
					array(
						"name" => "page",
						"type" => "boolean",
					),
					array(
						"name" => "id_page",
						"type" => "boolean",
					),
					array(
						"name" => "display_in_sort",
						"type" => "boolean",
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"shop" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "cat_id",
						"type" => "function",
					),
					array(
						"name" => "brand_id",
						"type" => "integer",
					),
					array(
						"name" => "article",
						"type" => "string",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
					array(
						"name" => "rel",
						"type" => "function",
					),
					array(
						"name" => "param",
						"type" => "function",
					),
					array(
						"name" => "price",
						"type" => "function",
					),
					array(
						"name" => "hit",
						"type" => "boolean",
					),
					array(
						"name" => "new",
						"type" => "boolean",
					),
					array(
						"name" => "action",
						"type" => "boolean",
					),
					array(
						"name" => "no_buy",
						"type" => "boolean",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
				),
			),
			"shop_brand" => array(
				"element_type" => "brand",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "anons",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "rewrite",
						"type" => "function",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "images",
						"type" => "function",
					),
				),
			),
			"shop_currency" => array(
				"fields" => array(
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "exchange_rate",
						"type" => "string",
					),
				),
			),
			"shop_delivery" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "status",
						"type" => "integer",
					),
					array(
						"name" => "thresholds",
						"type" => "function",
					),
				),
			),
			"shop_discount" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "discount",
						"type" => "string",
					),
					array(
						"name" => "threshold_cumulative",
						"type" => "integer",
					),
					array(
						"name" => "threshold",
						"type" => "integer",
					),
					array(
						"name" => "deduction",
						"type" => "integer",
					),
					array(
						"name" => "role_id",
						"type" => "integer",
					),
					array(
						"name" => "object",
						"type" => "function",
					),
				),
			),
			"shop_import_category" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "format",
						"type" => "string",
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "count_part",
						"type" => "integer",
					),
					array(
						"name" => "delimiter",
						"type" => "string",
					),
					array(
						"name" => "encoding",
						"type" => "string",
					),
					array(
						"name" => "sub_delimiter",
						"type" => "string",
					),
				),
			),
			"shop_import" => array(
				"fields" => array(
					array(
						"name" => "name",
						"type" => "string",
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "params",
						"type" => "string",
					),
					array(
						"name" => "cat_id",
						"type" => "integer",
					),
				),
			),
			
			"shop_order_param" => array(
				"element_type" => "order_param",
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "type",
						"type" => "string",
					),
					array(
						"name" => "info",
						"type" => "string",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
					array(
						"name" => "required",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form",
						"type" => "boolean",
					),
					array(
						"name" => "show_in_form_one_click",
						"type" => "boolean",
					),
					array(
						"name" => "config",
						"type" => "string",
					),
					array(
						"name" => "select",
						"type" => "function",
					),
				),
			),
			"shop_order_status" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "status",
						"type" => "integer",
					),
				),
			),
		),
		"votes" => array(
			"votes" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "answers",
						"type" => "function",
					),
					array(
						"name" => "no_result",
						"type" => "boolean",
					),
				),
			),
		),
		"search" => array(),
		"payment" => array(
			"payment" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "name",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "text",
						"type" => "string",
						"multilang" => true,
					),
					array(
						"name" => "payment",
						"type" => "string",
					),
					array(
						"name" => "params",
						"type" => "string",
					),
					array(
						"name" => "sort",
						"type" => "integer",
					),
				),
			),
		),
		"keywords" => array(
			"keywords" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "text",
						"type" => "string",
					),
					array(
						"name" => "link",
						"type" => "string",
					)
				),
			),
		),
	
		"geomap" => array(
			"geomap" => array(
				"fields" => array(
					array(
						"name" => "id",
						"type" => "integer",
					),
					array(
						"name" => "element_id",
						"type" => "integer",
					),
					array(
						"name" => "module_name",
						"type" => "string",
					),
					 array(
						"name" => "point",
						"type" => "string",
					),
					array(
						"name" => "element_type",
						"type" => "string",
					)
				),
			),
		),
		"subscription" => array(),
		"csseditor" => array(),
		"consultant" => array(),
	);

	/**
	 * Генерирование файлов с демо-данными, возвращает количество сгенерированных файлов
	 * 
	 * @return integer
	 */
	public function generate()
	{
		if(! class_exists('ZipArchive'))
		{
			throw new Exception('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
		}
		$custom_name = $this->diafan->filter($_GET, "string", "name");
		if(! $custom_name || ! is_dir(ABSOLUTE_PATH.'custom/'.$custom_name))
		{
			return;
		}
		
		$count = 0;

		Custom::inc('includes/install.php');
		
		$installed_module = array('site', 'menu', 'images');
		$ms = DB::query_fetch_all("SELECT module_name, name FROM {modules}");
		foreach($ms as $m)
		{
			if($m["module_name"] != 'core' && ! in_array($m["module_name"], $installed_module))
			{
				$installed_module[] = $m["module_name"];
			}
			$modules[$m["name"]] = $m["module_name"];
		}

		$install_sql = '';

		foreach($this->tpl as $module => $tpl)
		{
			// делаем инсталлы только для установленных модулей
			if(! in_array($module, $installed_module))
			{
				File::delete_file("custom/".$custom_name."/modules/".$module."/".$module.".install.demo.php");
				if(is_dir(ABSOLUTE_PATH. "custom/".$custom_name."/modules/".$module))
				{
					$dir = opendir(ABSOLUTE_PATH."custom/".$custom_name."/modules/".$module);
					$flag = false;
					while(($file = readdir($dir)) !== false)
					{
						if(is_file(ABSOLUTE_PATH. "custom/".$custom_name."/modules/".$module."/".$file))
						{
							$flag = true;
							break;
						}
					}
					closedir($dir);
				  
					if(! $flag)
					{
						rmdir(ABSOLUTE_PATH."custom/".$custom_name."/modules/".$module);
					}
				}
				continue;
			}

			$result = array();
			foreach($tpl as $table => $config)
			{
				// добавляем проверку отключения визуального редактора для всех таблиц
				$config["fields"][] = array(
						"name" => "hide_htmleditor",
						"type" => "function",
				);
				$tpl[$table] = $config;
				$config["module"] = $module;
				$config["table"] = $table;
				
				$rows = DB::query_fetch_all("SELECT * FROM {%s}".(empty($config["no_trash"]) ? " WHERE trash='0'" : "")." ORDER BY id ASC", $table);
				if($table == 'site')
				{
					foreach($rows as $i => $row)
					{
						if($row["id"] == 1)
						{
							$s = $this->row($config, $row);
							$s["rewrite"] = '';
							$result[$table][] = $s;
							unset($rows[$i]);
						}
						// страница пользователя
						if($row["id"] == 3)
						{
							unset($rows[$i]);
						}
						if($row["module_name"])
						{
							$this->cache["sites"][$modules[$row["module_name"]]][] = $this->row($config, $row);
							if(empty($this->cache["sites_count"][$row["module_name"]]))
							{
								$this->cache["sites_count"][$row["module_name"]] = 1;
							}
							else
							{
								$this->cache["sites_count"][$row["module_name"]]++;
							}
							unset($rows[$i]);
						}
					}
					// страница пользователя
					$result[$table][] = array(
						"id" => 3,
						"sort" => 5,
					);
				}
				$rows2 = array();
				foreach($rows as $row)
				{
					if(isset($row["parent_id"]))
					{
						$config["parent"] = true;
						$rows2[$row["parent_id"]][] = $row;
					}
				}
				if(! empty($config["parent"]))
				{
					$rows = $rows2;
					foreach($rows[0] as $row)
					{
						$result[$table][] = $this->row($config, $row, $rows);
					}
				}
				else
				{
					foreach($rows as $row)
					{
						$result[$table][] = $this->row($config, $row, $rows);
					}
				}
				
				// формируем файл install.sql
				// добавляем в него все записи, id которых выведены в шаблонах
				switch($table)
				{
					case 'menu_category':
						$ids = array();
						foreach($result[$table] as $row)
						{
							$ids[] = $row["id"];
						}
						$install_sql .= "INSERT IGNORE INTO {menu_category} (`id`) VALUES (".implode("),(", $ids).");\n";
						foreach($result[$table] as $row)
						{
							$install_sql .= "UPDATE {menu_category} SET [name]='".str_replace("'", "\\'", $row["name"][0])."', [act]='1' WHERE id=".$row["id"].";\n";
						}
						$install_sql .= "DELETE FROM {menu_category_site_rel};\n";
						$install_sql .= "INSERT IGNORE INTO {menu_category_site_rel} (`element_id`) VALUES (".implode("),(", $ids).");\n";
						break;

					case 'bs_category':
						$ids = array();
						foreach($result[$table] as $row)
						{
							$ids[] = $row["id"];
						}
						$install_sql .= "INSERT IGNORE INTO {bs_category} (`id`) VALUES (".implode("),(", $ids).");\n";
						foreach($result[$table] as $row)
						{
							$install_sql .= "UPDATE {bs_category} SET `name`='".str_replace("'", "\\'", $row["name"])."', `act`='1' WHERE id=".$row["id"].";\n";
						}
						break;

					case 'site':
						$row = $result[$table][0];
						$install_sql .= "UPDATE {site} SET `theme`='".$row["theme"]."', [name]='".str_replace("'", "\\'", $row["name"][0])."', [text]='".str_replace("'", "\\'", $row["text"][0])."' WHERE id=1;\n";
						break;

					case 'site_blocks':
						$ids = array();
						foreach($result[$table] as $row)
						{
							$ids[] = $row["id"];
						}
						$install_sql .= "INSERT IGNORE INTO {site_blocks} (`id`) VALUES (".implode("),(", $ids).");\n";
						foreach($result[$table] as $row)
						{
							$install_sql .= "UPDATE {site_blocks} SET [name]='".str_replace("'", "\\'", $row["name"][0])."', [text]='".str_replace(array("'", "[", "]"), array("\\'", '&lt;', '&gt;'), $row["text"][0])."', [act]='1', title_no_show='".($row["title_no_show"] ? 1 : 0)."' WHERE id=".$row["id"].";\n";
						}
						$install_sql .= "DELETE FROM {site_blocks_site_rel};\n";

						
						$rows = DB::query_fetch_all("SELECT element_id, site_id FROM {site_blocks_site_rel} WHERE trash='0'", $row["module"]);
						foreach($rows as $r)
						{
							$install_sql .= "INSERT IGNORE INTO {site_blocks_site_rel} (`element_id`,`site_id`) VALUES (".$r['element_id'].",".$r['site_id'].");\n";
						}
						break;
				}
			}

			$string = '';
			if(! empty($this->cache["sites"][$module]))
			{
				$string .= "\n\t\t".'"site" => array(';
				foreach($this->cache["sites"][$module] as $row)
				{
					$this->tpl["site"]["site"]["module"] = "site";
					$string .= $this->row_string($this->tpl["site"]["site"], $row);
				}
				$string .= "\n\t\t),";
			}
			$string .= $this->config($module);
			foreach($tpl as $table => $config)
			{
				$config["module"] = $module;
				if(! empty($result[$table]))
				{
					$string .= "\n\t\t".'"'.$table.'" => array(';
					foreach($result[$table] as $row)
					{
						$string .= $this->row_string($config, $row);
					}
					$string .= "\n\t\t),";
				}
			}
			$string = '<?php'."\n"
			.'/**'."\n"
			.' * Установка модуля'."\n"
			.' *'."\n"
			.' * @package    DIAFAN.CMS'."\n"
			.' * @author     diafan.ru'."\n"
			.' * @version    6.0'."\n"
			.' * @license    http://www.diafan.ru/license.html'."\n"
			.' * @copyright  Copyright (c) 2003-'.date("Y").' OOO «Диафан» (http://www.diafan.ru/)'."\n"
			.' */'."\n"
			."\n"
			.'if (! defined("DIAFAN"))'."\n"
			.'{'."\n"
			."\t".'$path = __FILE__;'."\n"
			."\t".'while(! file_exists($path.\'/includes/404.php\'))'."\n"
			."\t".'{'."\n"
			."\t\t".'$parent = dirname($path);'."\n"
			."\t\t".'if($parent == $path) exit;'."\n"
			."\t\t".'$path = $parent;'."\n"
			."\t".'}'."\n"
			."\t".'include $path.\'/includes/404.php\';'."\n"
			.'}'."\n"
			."\n"
			.'class '.Ucfirst($module).'_install_demo extends Install'."\n"
			.'{'."\n"
			."\t".'/**'."\n"
			."\t".' * @var array демо-данные'."\n"
			."\t".' */'."\n"
			."\t".'public $demo = array('.$string."\n"
			."\t".');'."\n"
			.'}';
			//echo "<p>modules/".$module."/".$module.".install.demo.php<p>";
			//echo '<pre>'.htmlspecialchars($string).'</pre><br><br>';
			File::save_file($string, "custom/".$custom_name."/modules/".$module."/".$module.".install.demo.php");

			// добавляем поле parent_id для страниц сайт, прикрепленных к модулям после того, как сформирован инсталл для модуля site
			if($module == 'site')
			{
				$this->tpl["site"]["site"]["fields"][] = array("name" => "parent_id", "type" => "integer");
			}
			$count++;
		}
		if(file_exists(ABSOLUTE_PATH."custom/".$custom_name."/demo.zip"))
		{
			unlink(ABSOLUTE_PATH."custom/".$custom_name."/demo.zip");
		}
		if(! empty($this->cache["demo_files"]))
		{
			$zip = new ZipArchive;
			if ($zip->open(ABSOLUTE_PATH."custom/".$custom_name."/demo.zip", ZipArchive::CREATE) === true)
			{
				foreach($this->cache["demo_files"] as $k => $file)
				{
					if(is_string($k))
					{
						$zip->addFile(ABSOLUTE_PATH.USERFILES.'/'.$k, $file);
					}
					else
					{
						$zip->addFile(ABSOLUTE_PATH.USERFILES.'/'.$file, $file);
					}
				}
				$zip->close();
			}
		}
		if($install_sql)
		{
			File::save_file($install_sql, "custom/".$custom_name."/install.sql");
		}
		else
		{
			if(file_exists(ABSOLUTE_PATH."custom/".$custom_name."/install.sql"))
			{
				unlink(ABSOLUTE_PATH."custom/".$custom_name."/install.sql");
			}
		}
		return $count;
	}

	/**
	 * Формирует строку файла, содержащую настройки модуля при установки
	 * настройки будут применены поверх дефолтных настроек модуля
	 * 
	 * @param string $module название модуля
	 * @return string
	 */
	private function config($module)
	{
		Custom::inc('modules/'.$module.'/'.$module.'.install.php');
		
		$name = ucfirst($module.'_install');
		$class = new $name($this->diafan);

		$all_demo_config = array();
		foreach($class->config as $row)
		{
			if(empty($row["module_name"]) || $row["module_name"] == $module)
			{
				$all_demo_config[$row["name"]] = $row["value"];
			}
		}

		// добавляем в настройки значения, которых нет в демо-конфигурации
		$rows = DB::query_fetch_all("SELECT * FROM {config} WHERE module_name='%s' AND (lang_id=%d OR lang_id=0) ORDER BY site_id ASC", $module, _LANG);
		foreach($rows as $row)
		{
			if(empty($all_demo_config[$row["name"]]) || $all_demo_config[$row["name"]] != $row["value"])
			{
				$config[$row["name"]] = array(
					"name" => $row["name"],
					"value" => $row["value"]
				);
			}
			$all_config[$row["name"]] = $row["value"];
			
		}

		// значения из демо-конфигурации, которые не заданы в новой конфигурации, добавляем в настройки как пустые
		foreach($all_demo_config as $k => $v)
		{
			if(! isset($all_config[$k]))
			{
				$config[$k] = array(
					"name" => $k,
					"value" => ''
				);
			}
		}
		$result = '';
		if(! empty($config))
		{
			$result .= "\n\t\t".'"config" => array(';
			foreach($config as $r)
			{
				$result .= "\n\t\t\tarray(";
				$result .= "\n\t\t\t\t".'"name" => "'.$r["name"].'",';
				$result .= "\n\t\t\t\t".'"value" => "'.str_replace('"', '\\"', $r["value"]).'",';
				$result .= "\n\t\t\t),";
			}
			$result .= "\n\t\t),";
		}
		return $result;
	}

	/**
	 * Формирует массив с данным о конкретном элемненте модуля
	 * 
	 * @param array $config часть схемы демо-данных модулей $tpl, относящаяся к элементам текущего модуля
	 * @param array $row данные из таблицы БД о текущем элементе модуля
	 * @param array $rows данные из таблицы БД обо всех элементах модуля текущей таблицы
	 * @return array
	 */
	private function row($config, $row, $rows = array())
	{
		foreach($config["fields"] as $a)
		{
			if($a["type"] == 'function')
			{
				if(empty($row["module"]))
				{
					$row["module"] = $config["module"];
				}
				$row["table"] = $config["table"];
				$row["element_type"] = (! empty($config["element_type"]) ? $config["element_type"] : 'element');
				$row[$a["name"]] = call_user_func_array(array(&$this, "row_func_".$a["name"]), array($row));
				continue;
			}
			if(! empty($a["multilang"]))
			{
				$row[$a["name"]] = array(
					(! empty($row[$a["name"].'1']) ? $row[$a["name"].'1'] : ''),
					(! empty($row[$a["name"].'2']) ? $row[$a["name"].'2'] : false),
				);
			}
			else
			{
				switch($a["type"])
				{
					case 'boolean':
						$row[$a["name"]] = (! empty($row[$a["name"]]) ? $row[$a["name"]] : false);
						break;

					case 'integer':
						$row[$a["name"]] = (! empty($row[$a["name"]]) ? (int) $row[$a["name"]] : false);
						break;

					default:
						$row[$a["name"]] = (! empty($row[$a["name"]]) ? $row[$a["name"]] : false);
						break;
				}
			}
		}
		if(isset($row["parent_id"]))
		{
			$row["children"] = array();
			if(! empty($rows[$row["id"]]))
			{
				foreach($rows[$row["id"]] as $r)
				{
					$row["children"][] = $this->row($config, $r, $rows);
				}
			}
			if(! $row["parent_id"])
			{
				$row["parent_id"] = false;
			}
		}
		return $row;
	}

	/**
	 * Формирует строку файла, содержащую информацию о конкретном элемненте модуля
	 * 
	 * @param array $config часть схемы демо-данных модулей $tpl, относящаяся к элементам текущего модуля
	 * @param array $row данные о текущем элементе модуля, сформированные функцией row()
	 * @return string
	 */
	private function row_string($config, $row)
	{
		$result = "\n\t\t\tarray(";
		foreach($config["fields"] as $a)
		{
			$result .= $this->string_value($row, $a['name'], $a['type'], (! empty($a["multilang"]) ? true : false));
		}
		if(! empty($row["children"]))
		{
			$result .= $this->row_string_ch($config, $row);
		}
		if(isset($row["site_id"]) && isset($this->cache["sites_count"][$config["module"]]) &&  $this->cache["sites_count"][$config["module"]] > 1)
		{
			$result .= "\n\t\t\t\t".'"site_id" => '.$row["site_id"].",";
		}
		$result .= "\n\t\t\t),";
		return $result;
	}

	/**
	 * Формирует строку файла, содержащую информацию о конкретном элемненте модуля
	 * 
	 * @param array $config часть схемы демо-данных модулей $tpl, относящаяся к элементам текущего модуля
	 * @param array $row данные о текущем элементе модуля, сформированные функцией row()
	 * @return string
	 */
	private function row_string_ch($config, $row, $l = 1)
	{
		$result = "\n\t\t".str_repeat("\t\t", $l).'"children" => array(';
		foreach($row["children"] as $r)
		{
			$result .= "\n\t\t\t".str_repeat("\t\t", $l)."array(";
			foreach($config["fields"] as $a)
			{
				$result .= $this->string_value($r, $a['name'], $a['type'], (! empty($a["multilang"]) ? true : false), $l);
			}
			if(! empty($r["children"]))
			{
				$result .= $this->row_string_ch($config, $r, $l+1);
			}
			$result .= "\n\t\t\t".str_repeat("\t\t", $l)."),";
		}
		$result .= "\n\t\t".str_repeat("\t\t", $l)."),";
		return $result;
	}

	/**
	 * Преобразуем массив данных о текущем элементе модуля в строку для файла демо-данных
	 * 
	 * @param array $row данные о текущем элементе модуля, сформированные функцией row()
	 * @param string $name название текущей переменной в массиве
	 * @param string $type тип текущей переменной в массиве
	 * @param boolean $multilang текущей переменной в массиве имеет разное значение для разных языковых версий сайта
	 * @param integer $level уровень вложенности данных в массиве
	 * @return string
	 */
	private function string_value($row, $name, $type, $multilang = false, $level = 0)
	{
		$result = '';
		if(! $multilang && (! isset($row[$name]) || $row[$name] === false))
		{
			return $result;
		}
		if($multilang && empty($row[$name][0]) && empty($row[$name][1]))
		{
			return $result;
		}
		$result .= "\n\t\t\t\t".str_repeat("\t\t", $level);
		$result .= '"'.$name.'" => ';
		if(is_array($row[$name]))
		{
			$result .= $this->array_string($row[$name]);
		}
		else
		{
			if(is_string($row[$name]))
			{
				$result .= '"'.str_replace('"', '\\"', $row[$name]).'"';
			}
			elseif(is_bool($row[$name]))
			{
				$result .= 'true';
			}
			else
			{
				$result .= $row[$name];
			}
		}
		$result .= ',';
		return str_replace(BASE_PATH, 'BASE_PATH', $result);
	}

	/**
	 * Преобразуем массив в строку
	 * 
	 * @param array $value исходный массив
	 * @return string
	 */
	private function array_string($value)
	{
		$result = 'array(';
		$i = 0;
		if(array_values($value) == $value)
		{
			$is_key = false;
		}
		else
		{
			$is_key = true;
		}
		foreach($value as $k => $v)
		{
			if($v === false)
				continue;

			if($i)
			{
				$result .= ', ';
			}
			if($is_key)
			{
				$result .= '"'.str_replace('"', '\\"', $k).'" => ';
			}
			if(is_string($v))
			{
				$result .= '"'.str_replace('"', '\\"', $v).'"';
			}
			elseif(is_bool($v))
			{
				$result .= 'true';
			}
			elseif(is_array($v))
			{
				
				$result .= $this->array_string($v);
			}
			else
			{
				$result .= $v;
			}
			$i++;
		}
		$result .= ')';
		return $result;
	}

	/**
	 * Готовит данные о псевдоссылке текущего элемента модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return string|boolean false
	 */
	private function row_func_rewrite($row)
	{
		if(! isset($this->cache["rewrites"]))
		{
			$this->cache["rewrites"] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {rewrite} WHERE trash='0'");
			foreach($rows as $r)
			{
				$this->cache["rewrites"][$r["module_name"]][$r["element_type"]][$r["element_id"]] = $r["rewrite"];
			}
		}
		return (isset($this->cache["rewrites"][$row["module"]][$row["element_type"]][$row["id"]]) ? $this->cache["rewrites"][$row["module"]][$row["element_type"]][$row["id"]] : false);
	}

	/**
	 * Готовит данные о пунктах меню на текущий элемент модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_menu($row)
	{
		if(! isset($this->cache["menu"]))
		{
			$this->cache["menu"] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {menu} WHERE trash='0'");
			foreach($rows as $r)
			{
				$i = array(
					"id" => $r["id"],
					"module" => "menu",
					"element_type" => "element",
				);
				if(isset($this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]]) && ! is_array($this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]]))
				{
					$this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]] = array($this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]]);
				}
				if($imgs = $this->row_func_images($i))
				{
					if(isset($this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]]))
					{
						$this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]][] = array(
							"cat_id" => (int) $r["cat_id"],
							"image" => $imgs[0],
						);
					}
					else
					{
						$this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]] = array(array(
							"cat_id" => (int) $r["cat_id"],
							"image" => $imgs[0],
						));
					}
				}
				else
				{
					if(isset($this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]]))
					{
						$this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]][] = $r["cat_id"];
					}
					else
					{
						$this->cache["menu"][$r["module_name"]][$r["element_type"]][$r["element_id"]] = $r["cat_id"];
					}
				}
			}
		}
		return (! empty($this->cache["menu"][$row["module"]][$row["element_type"]][$row["id"]]) ? $this->cache["menu"][$row["module"]][$row["element_type"]][$row["id"]] : false);
	}

	/**
	 * Готовит данные о изображениях для текущего элемента модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_images($row)
	{
		if(! isset($this->cache["images"]))
		{
			$this->cache["images"] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE trash='0' ORDER BY sort ASC");
			foreach($rows as $r)
			{
				$this->cache["images"][$r["module_name"]][$r["element_type"]][$r["element_id"]][] = $r["name"];
			}
		}
		$imgs = (! empty($this->cache["images"][$row["module"]][$row["element_type"]][$row["id"]]) ? $this->cache["images"][$row["module"]][$row["element_type"]][$row["id"]] : false);
		if($imgs)
		{
			foreach($imgs as $img)
			{
				$this->cache["demo_files"][] = 'original/'.$img;
			}
		}
		return $imgs;
	}

	/**
	 * Готовит данные о прикрепленных к текущему элементу модуля файлах
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_attachments($row)
	{
		if(! isset($this->cache["attachments"]))
		{
			$this->cache["attachments"] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {attachments} WHERE trash='0'");
			foreach($rows as $r)
			{
				$this->cache["attachments"][$r["module_name"]][$r["element_id"]][] = array(
					'id' => $r["id"],
					'name' => $r["name"],
					'extension' => $r["extension"],
					'is_image' => (bool) $r["is_image"],
				);
			}
		}
		$as = (! empty($this->cache["attachments"][$row["module"]][$row["id"]]) ? $this->cache["attachments"][$row["module"]][$row["id"]] : false);
		if($as)
		{
			foreach($as as $i => $a)
			{
				if($a["is_image"])
				{
					$this->cache["demo_files"][$row["module"].'/imgs/'.$a["name"]] = 'attachments/'.$a["name"];
				}
				else
				{
					$this->cache["demo_files"][$row["module"].'/files/'.$a["id"]] = 'attachments/'.$a["name"];
				}
				unset($as[$i]["id"]);
			}
		}
		return $as;
	}

	/**
	 * Готовит данные о данных из конструктора дополнетельных полей для текущего элемента модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_param($row)
	{
		if(! isset($this->cache["param"][$row["module"]]))
		{
			$this->cache["param"][$row["module"]] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {%s_param_element} WHERE trash='0'", $row["module"]);
			foreach($rows as $r)
			{
				if(isset($r["value"]))
				{
					$value = $r["value"];
				}
				elseif(! empty($r["value2"]))
				{
					$value = array($r["value1"], $r["value2"]);
				}
				else
				{
					$value = $r["value1"];
				}
				if(! empty($this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]]))
				{
					if(is_string($this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]]))
					{
						$this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]] = array($this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]], $value);
					}
					else
					{
						$this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]][] = $value;
					}
				}
				else
				{
					$this->cache["param"][$row["module"]][$r["element_id"]][$r["param_id"]] = $value;
				}
			}
		}
		return (! empty($this->cache["param"][$row["module"]][$row["id"]]) ? $this->cache["param"][$row["module"]][$row["id"]] : false);
	}

	/**
	 * Готовит данные о возможных значениях для текущий поля конструктора с типом "список" и "список с выбором значений"
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_select($row)
	{
		if($row["element_type"] == 'param' && ! empty($row["type"]) && in_array($row["type"], array('select', 'multiple', 'radio')))
		{
			if(! isset($this->cache["select"][$row["module"]]))
			{
				$this->cache["select"][$row["module"]] = array();
				$rows = DB::query_fetch_all("SELECT * FROM {%s_param_select} WHERE trash='0'", $row["module"]);
				foreach($rows as $r)
				{
					if(isset($r["name"]))
					{
						$name = $r["name"];
					}
					else
					{
						$name = array($r["name1"], (isset($r["name2"]) ? $r["name2"] : false));
					}
					$this->cache["select"][$row["module"]][$r["param_id"]][] = array(
						'id' => $r["id"],
						'name' => $name,
					);
				}
			}
			return (! empty($this->cache["select"][$row["module"]][$row["id"]]) ? $this->cache["select"][$row["module"]][$row["id"]] : false);
		}
	}

	/**
	 * Готовит данные о связанных элементах модуля для текущего элемента модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_rel($row)
	{
		if(! isset($this->cache["rel"][$row["module"]]))
		{
			$this->cache["rel"][$row["module"]] = array();
			if($row["table"] == 'site_blocks')
			{
				$t = 'site_blocks_site';
			}
			else
			{
				$t = $row["module"];
			}
			$rows = DB::query_fetch_all("SELECT * FROM {%s_rel} WHERE trash='0'", $t);
			foreach($rows as $r)
			{
				if(isset($r["rel_element_id"]))
				{
					$rel = $r["rel_element_id"];
				}
				else
				{
					$rel = $r["site_id"];
				}
				$this->cache["rel"][$row["module"]][$r["element_id"]][] = (int) $rel;
			}
		}
		$rel = (! empty($this->cache["rel"][$row["module"]][$row["id"]]) ? $this->cache["rel"][$row["module"]][$row["id"]] : false);
		if($rel && count($rel) == 1)
		{
			$rel = $rel[0];
		}
		return $rel;
	}

	/**
	 * Готовит данные о принадлежности к категориям для текущего элемента модуля
	 * используется для случаев множественного соединения с категориями
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_cat_id($row)
	{
		if(! isset($this->cache["cats"][$row["module"]][$row["element_type"]]))
		{
			$this->cache["cats"][$row["module"]][$row["element_type"]] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {%s_category_rel} WHERE trash='0'", $row["module"].($row["element_type"] != 'element' ? '_'.$row["element_type"] : ''));
			foreach($rows as $r)
			{
				$this->cache["cats"][$row["module"]][$row["element_type"]][$r["element_id"]][] = (int) $r["cat_id"];
			}
		}
		$cats = (! empty($this->cache["cats"][$row["module"]][$row["element_type"]][$row["id"]]) ? $this->cache["cats"][$row["module"]][$row["element_type"]][$row["id"]] : false);
		if(count($cats) == 1)
		{
			if($cats[0] == 0)
			{
				return false;
			}
			return $cats[0];
		}
		if(isset($row["cat_id"]) && $cats)
		{
			foreach($cats as $i => $v)
			{
				if($v == $row["cat_id"])
				{
					unset($cats[$i]);
				}
			}
			$cats = array_merge(array((int)$row["cat_id"]), $cats);
		}
		return $cats;
	}

	/**
	 * Готовит данные о файлах, которые нужно скопировать, для текущего элемента модуля
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_copy($row)
	{
		if(empty($row["file"]))
			return false;

		$this->cache["demo_files"][] = $row["module"].'/'.$row["file"];
		return array($row["module"].'/'.$row["file"]);
	}

	/**
	 * Готовит данные о скрытии визуального редактора для некоторых полей
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_hide_htmleditor($row)
	{
		$hide_htmleditor = $this->diafan->configmodules("hide_".$row["table"]."_".$row["id"], "htmleditor");
		return $hide_htmleditor ? $hide_htmleditor : false;
	}

	/**
	 * Готовит данные о ссылках на баннер
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_link($row)
	{
		if(empty($row["link"]))
			return false;

		return $row["link"];
	}

	/**
	 * Готовит данные о сообщениях в текущей теме форума
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_messages($row)
	{
		if(! isset($this->cache["messages"][$row["module"]]))
		{
			$this->cache["messages"][$row["module"]] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {%s_messages} WHERE trash='0'", $row["module"]);
			foreach($rows as $r)
			{
				$this->cache["messages"][$row["module"]][$r["forum_id"]][] = $r;
			}
		}
		$messages = (! empty($this->cache["messages"][$row["module"]][$row["id"]]) ? $this->cache["messages"][$row["module"]][$row["id"]] : false);
		if(! $messages)
		{
			return false;
		}
		$rows = array();
		foreach($messages as $r)
		{
			$rows[$r["parent_id"]][] = $r;
		}
		return $this->_row_func_messages_rows($rows, 0);
	}

	/**
	 * Готовит данные о вложенных сообщениях для текущего сообщения на форуме
	 * 
	 * @param array $rows все сообщения форума
	 * @param integer $parent_id идентификатор текущего сообщения
	 * @return mixed
	 */
	private function _row_func_messages_rows($rows, $parent_id)
	{
		if(empty($rows[$parent_id]))
		{
			return false;
		}
		foreach($rows[$parent_id] as $r)
		{
			$r["module"] = "forum";
			$r["element_type"] = "element";
			$m = array(
				'name' => $r["name"] ? $r["name"] : false,
				'text' => $r["text"] ? $r["text"] : false,
				'user_id' => $r["user_id"] ? (int) $r["user_id"] : false,
				'attachments' => $this->row_func_attachments($r),
			);
			$m["children"] = $this->_row_func_messages_rows($rows, $r["id"]);
			$result[] = $m;
		}
		return $result;
	}

	/**
	 * Готовит данные о вариантах ответа на опрос
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_answers($row)
	{
		if(! isset($this->cache["answers"][$row["module"]]))
		{
			$this->cache["answers"][$row["module"]] = array();
			$rows = DB::query_fetch_all("SELECT * FROM {%s_answers} WHERE trash='0'", $row["module"]);
			foreach($rows as $r)
			{
				$this->cache["answers"][$row["module"]][$r["votes_id"]][] = $r;
			}
		}
		$answers = (! empty($this->cache["answers"][$row["module"]][$row["id"]]) ? $this->cache["answers"][$row["module"]][$row["id"]] : false);
		if(! $answers)
		{
			return false;
		}
		$rows = array();
		foreach($answers as $r)
		{
			$rows[] = array('text' => array($r["text1"], (! empty($r["text2"]) ? $r["text2"] : false)));
		}
		return $rows;
	}

	/**
	 * Готовит данные о стоимости доставки в магазине
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_thresholds($row)
	{
		if(! isset($this->cache["thresholds"]))
		{
			$this->cache["thresholds"] = DB::query_fetch_key_array("SELECT * FROM {shop_delivery_thresholds} WHERE trash='0'", "delivery_id");
		}
		$rows = (! empty($this->cache["thresholds"][$row["id"]]) ? $this->cache["thresholds"][$row["id"]] : false);
		if(! $rows)
		{
			return false;
		}
		$rs = array();
		foreach($rows as $r)
		{
			$rs[] = array(
				'price' => $r["price"] ? $r["price"] : false,
				'amount' => $r["amount"] ? $r["amount"] : false,
			);
		}
		return $rs;
	}

	/**
	 * Готовит данные о цене товара
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_price($row)
	{
		if(! isset($this->cache["price"]))
		{
			$this->cache["price"] = DB::query_fetch_key_array("SELECT * FROM {shop_price} WHERE trash='0' AND price_id=id", "good_id");
			$this->cache["price_image"] = DB::query_fetch_key_value("SELECT s.price_id, i.name FROM {shop_price_image_rel} AS s INNER JOIN {images} AS i ON i.id=s.image_id", "price_id", "name");
			$this->cache["price_param"] = DB::query_fetch_key_array("SELECT * FROM {shop_price_param} WHERE trash='0'", "price_id");
		}
		$rows = (! empty($this->cache["price"][$row["id"]]) ? $this->cache["price"][$row["id"]] : false);
		if(! $rows)
		{
			return false;
		}
		$rs = array();
		foreach($rows as $r)
		{
			$param = ! empty($this->cache["price_param"][$r["id"]]) ? $this->cache["price_param"][$r["id"]] : false;
			if($param)
			{
				foreach($param as $p)
				{
					$param_rs[$p["param_id"]] = $p["param_value"];
				}
				$param = $param_rs;
			}
			$rs[] = array(
				'price' => $r["price"] ? $r["price"] : false,
				'param' => $param,
				'image_rel' => ! empty($this->cache["price_image"][$r["id"]]) ? $this->cache["price_image"][$r["id"]] : false,
			);
		}
		return $rs;
	}

	/**
	 * Готовит данные об объектах применения ссылки на товары
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_object($row)
	{
		if(! isset($this->cache["object"]))
		{
			$this->cache["object"] = DB::query_fetch_key_array("SELECT * FROM {shop_discount_object} WHERE trash='0'", "discount_id");
		}
		$rows = (! empty($this->cache["object"][$row["id"]]) ? $this->cache["object"][$row["id"]] : false);
		if(! $rows)
		{
			return false;
		}
		$rs = array();
		foreach($rows as $r)
		{
			$rs[] = array(
				'good_id' => $r["good_id"] ? $r["good_id"] : false,
				'cat_id' => $r["cat_id"] ? $r["cat_id"] : false,
			);
		}
		return $rs;
	}

	/**
	 * Готовит данные о модулях, к которым прикреплен динамический блок
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_module($row)
	{
		if($row["table"] == 'site_dynamic')
		{
			if(! isset($this->cache["module"]))
			{
				$rows = DB::query_fetch_all("SELECT * FROM {site_dynamic_module} WHERE trash='0'");
				foreach($rows as $r)
				{
					$this->cache["module"][$r["dynamic_id"]][] = array(
						'element_type' => $r['element_type'],
						'module_name' => $r['module_name'],
					);
				}
			}
			return (! empty($this->cache["module"][$row["id"]]) ? $this->cache["module"][$row["id"]] : false);
		}
	}

	/**
	 * Готовит данные о модулях, к которым прикреплен динамический блок
	 * 
	 * @param array $row данные о текущем элементе модуля, полученные из базы данных
	 * @return mixed
	 */
	private function row_func_element($row)
	{
		if($row["table"] == 'site_dynamic')
		{
			if(! isset($this->cache["element"]))
			{
				$rows = DB::query_fetch_all("SELECT * FROM {site_dynamic_element} WHERE trash='0'");
				foreach($rows as $r)
				{
					$this->cache["element"][$r["dynamic_id"]][] = array(
						'element_type' => $r['element_type'],
						'module_name' => $r['module_name'],
						'element_id' => $r['element_id'],
						'value' => array($r["value1"], (! empty($r["value2"]) ? $r["value2"] : false)),
						'parent' => $r['parent'],
						'category' => $r['category'],
					);
				}
			}
			return (! empty($this->cache["element"][$row["id"]]) ? $this->cache["element"][$row["id"]] : false);
		}
	}
}
