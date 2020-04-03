<?php
/**
 * Формирует данные для формы платежной системы Robokassa
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

class Payment_robokassa_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы "Robokassa"
     *
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return void
     */
	public function get($params, $pay)
	{
		$link = "https://merchant.roboxchange.com/Index.aspx";
		if(! empty($params["robokassa_receipt"]))
		{
			$items = array();
			if(! empty($pay["details"]["goods"]))
			{
				foreach($pay["details"]["goods"] as $row)
				{
					$items[] = array(
						"name" => $row["name"].($row["article"] ? ' '.$row["article"] : ''),
						"quantity" => (float)$row["count"],
						"sum" => $row["price"] * $row["count"],
						"tax" => ! empty($params["robokassa_tax"]) ? $params["robokassa_tax"] : 'none',
					);
				}
			}
			if(! empty($pay["details"]["additional"]))
			{
				foreach($pay["details"]["additional"] as $row)
				{
					$items[] = array(
						"name" => $row["name"],
						"quantity" => 1,
						"sum" => (float)$row["summ"],
						"tax" => ! empty($params["robokassa_tax"]) ? $params["robokassa_tax"] : 'none',
					);
				}
			}
			if(! empty($pay["details"]["delivery"]))
			{
				$items[] = array(
					"name" => $this->diafan->_('Доставка', false),
					"quantity" => 1,
					"sum" => (float)$pay["details"]["delivery"]["summ"],
					"tax" => ! empty($params["robokassa_tax"]) ? $params["robokassa_tax"] : 'none',
				);
			}
			Custom::inc('plugins/json.php');
			$receipt = to_json(array(
				"sno" => ! empty($params["robokassa_sno"]) ? $params["robokassa_sno"] : '',
				"items" => $items,
			));

			//формирование подписи
			$crc = md5($params['robokassa_login'].":".$pay['summ'].":".$pay["id"].":".$receipt.":".$params['robokassa_pass_1']);

			echo '<!DOCTYPE html>
			<html>
			<head></head>
			<body onload="document.getElementById(\'fframe\').submit();">';
			echo '<form action="'.$link.'" style="display:none" method="post" id="fframe">
			<input type="hidden" name="MrchLogin" value="'.$params['robokassa_login'].'">
			<input type="hidden" name="OutSum" value="'.$pay["summ"].'">
			<input type="hidden" name="InvId" value="'.$pay["id"].'">
			<input type="hidden" name="Desc" value="'.$this->diafan->translit($pay["desc"]).'">
			<input type="hidden" name="Receipt" value="'.urlencode($receipt).'">
			<input type="hidden" name="SignatureValue" value="'.$crc.'">';
			if(! empty($params["robokassa_test"]))
			{
				echo '<input type="hidden" name="IsTest" value="1">';
			}
			echo '<input type="submit" value="ok">
			</form>
			</body>';
			echo '</html>';
		}
		else
		{
			//формирование подписи
			$crc = md5($params['robokassa_login'].":".$pay['summ'].":".$pay["id"].":".$params['robokassa_pass_1']);

			$link .= "?MrchLogin=".$params['robokassa_login']
			."&OutSum=".$pay["summ"]
			."&InvId=".$pay["id"]
			."&Desc=".$this->diafan->translit($pay["desc"])
			."&SignatureValue=".$crc;
			if(! empty($params["robokassa_test"]))
			{
				$link .= "&IsTest=1";
			}

			$this->diafan->redirect($link);
		}
		exit;
	}
}
