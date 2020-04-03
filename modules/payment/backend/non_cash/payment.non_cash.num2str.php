<?php
/**
 * Перевод чисел в слова
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

class Num_to_str
{
    private $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;

    public function __construct()
    {
		$this->_1_2[1] = "одна ";
		$this->_1_2[2] = "две ";

		$this->_1_19[1] = "один ";
		$this->_1_19[2] = "два ";
		$this->_1_19[3] = "три ";
		$this->_1_19[4] = "четыре ";
		$this->_1_19[5] = "пять ";
		$this->_1_19[6] = "шесть ";
		$this->_1_19[7] = "семь ";
		$this->_1_19[8] = "восемь ";
		$this->_1_19[9] = "девять ";
		$this->_1_19[10] = "десять ";

		$this->_1_19[11] = "одиннацать ";
		$this->_1_19[12] = "двенадцать ";
		$this->_1_19[13] = "тринадцать ";
		$this->_1_19[14] = "четырнадцать ";
		$this->_1_19[15] = "пятнадцать ";
		$this->_1_19[16] = "шестнадцать ";
		$this->_1_19[17] = "семнадцать ";
		$this->_1_19[18] = "восемнадцать ";
		$this->_1_19[19] = "девятнадцать ";

		$this->des[2] = "двадцать ";
		$this->des[3] = "тридцать ";
		$this->des[4] = "сорок ";
		$this->des[5] = "пятьдесят ";
		$this->des[6] = "шестьдесят ";
		$this->des[7] = "семьдесят ";
		$this->des[8] = "восемьдесят ";
		$this->des[9] = "девяносто ";

		$this->hang[1] = "сто ";
		$this->hang[2] = "двести ";
		$this->hang[3] = "триста ";
		$this->hang[4] = "четыреста ";
		$this->hang[5] = "пятьсот ";
		$this->hang[6] = "шестьсот ";
		$this->hang[7] = "семьсот ";
		$this->hang[8] = "восемьсот ";
		$this->hang[9] = "девятьсот ";

		$this->namerub[1] = "рубль ";
		$this->namerub[2] = "рубля ";
		$this->namerub[3] = "рублей ";

		$this->nametho[1] = "тысяча ";
		$this->nametho[2] = "тысячи ";
		$this->nametho[3] = "тысяч ";

		$this->namemil[1] = "миллион ";
		$this->namemil[2] = "миллиона ";
		$this->namemil[3] = "миллионов ";

		$this->namemrd[1] = "миллиард ";
		$this->namemrd[2] = "миллиарда ";
		$this->namemrd[3] = "миллиардов ";

		$this->kopeek[1] = "копейка ";
		$this->kopeek[2] = "копейки ";
		$this->kopeek[3] = "копеек ";
    }

    private function semantic($i, &$words, &$fem, $f)
    {
		$words = "";
		$fl = 0;
		if ($i >= 100)
		{
			$jkl = intval($i / 100);
			$words .= $this->hang[$jkl];
			$i %= 100;
		}
		if ($i >= 20)
		{
			$jkl = intval($i / 10);
			$words .= $this->des[$jkl];
			$i %= 10;
			$fl = 1;
		}
		switch ($i)
		{
			case 1:
				$fem = 1;
				break;
			case 2:
			case 3:
			case 4:
				$fem = 2;
				break;
			default:
				$fem = 3;
				break;
		}
		if ($i)
		{
			if ($i < 3 && $f > 0)
			{
			if ($f >= 2)
			{
				$words .= $this->_1_19[$i];
			}
			else
			{
				$words .= $this->_1_2[$i];
			}
			}
			else
			{
			$words .= $this->_1_19[$i];
			}
		}
    }

    public function get($L, $format_price_1 = 2)
    {
		$L = number_format($L, $format_price_1, '.', '');
		$s = " ";
		$s1 = " ";
		$s2 = " ";
		$kop = intval($L * 100 - intval($L) * 100);
		$L = intval($L);
		if ($L >= 1000000000)
		{
			$many = 0;
			$this->semantic(intval($L / 1000000000), $s1, $many, 3);
			$s .= $s1.$this->namemrd[$many];
			$L %= 1000000000;
		}

		if ($L >= 1000000)
		{
			$many = 0;
			$this->semantic(intval($L / 1000000), $s1, $many, 2);
			$s .= $s1.$this->namemil[$many];
			$L %= 1000000;
			if ($L == 0)
			{
			$s .= "рублей ";
			}
		}

		if ($L >= 1000)
		{
			$many = 0;
			$this->semantic(intval($L / 1000), $s1, $many, 1);
			$s .= $s1.$this->nametho[$many];
			$L %= 1000;
			if ($L == 0)
			{
			$s .= "рублей ";
			}
		}

		if ($L != 0)
		{
			$many = 0;
			$this->semantic($L, $s1, $many, 0);
			$s.=$s1.$this->namerub[$many];
		}

		if ($kop > 0)
		{
			$many = 0;
			$this->semantic($kop, $s1, $many, 1);
			//$s.=$s1.$this->kopeek[$many]; 
			if ($kop < 10)
			{
			$s .= "0";
			}
			$s .= $kop." ".$this->kopeek[$many];
		}
		else
		{
			$s.=" 00 копеек";
		}

		$s = strtoupper(substr($s, 0, 2)).substr($s, 2);

		return $s;
    }
}