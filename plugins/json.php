<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2015 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	include dirname(dirname(__FILE__)).'/includes/404.php';
}

/**
 * JSON конвертирует данные в формат JSON
 */
function to_json($data)
{
	if(is_array($data))
	{
		ob_start(); Dev::get_profiler(); $data["profiler"] = ob_get_contents(); ob_end_clean();
	}
	header('Content-Type: text/html; charset=utf-8');
	$data = str_replace('&', '&amp;', json_encode($data));
	return str_replace(array('<', '>'), array('&lt;', '&gt;'), $data);
}

function from_json($string)
{
	return json_decode($string, true);
}
if (! function_exists('json_encode'))
{
    /**
     * Convert a PHP scalar, array or hash to JS scalar/array/hash. This function is
     * an analog of json_encode(), but it can work with a non-UTF8 input and does not
     * analyze the passed data. Output format must be fully JSON compatible.
     *
     * A port of JsHttpRequest (http://en.dklab.ru)
     * (C) Dmitry Koterov, http://en.dklab.ru
     *
     * @param mixed $a   Any structure to convert to JS.
     * @return string    JavaScript equivalent structure.
     */
    function json_encode($a = false)
    {
        if (is_null($a)) return 'null';
        if ($a === false) return 'false';
        if ($a === true) return 'true';
        if (is_scalar($a)) {
            if (is_float($a)) {
                // Always use "." for floats.
                $a = str_replace(",", ".", strval($a));
            }

            static $jsonReplaces = array(
                array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
            );
            return '"'.str_replace($jsonReplaces[0], $jsonReplaces[1], $a).'"';
        }
        $isList = true;
        for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
            if (key($a) !== $i) {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList) {
            foreach ($a as $v) {
                $result[] = json_encode($v);
            }
            return '[ '.join(', ', $result).' ]';
        } else {
            foreach ($a as $k => $v) {
                $result[] = json_encode($k).': '.json_encode($v);
            }
            return '{ '.join(', ', $result).' }';
        }
    }
}
