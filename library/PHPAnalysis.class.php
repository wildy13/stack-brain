<?php

define('_SP_', chr(0xFF) . chr(0xFE));
define('UCS2', 'ucs-2be');
class PhpAnalysis
{

	public $mask_value = 0x000F;

	public $sourceCharSet = 'utf-8';
	public $targetCharSet = 'utf-8';
	public $resultType = 1;
	public $notSplitLen = 5;
	public $toLower = false;
	public $differMax = false;
	public $unitWord = true;
	public static $loadInit = false;
	public $differFreq = false;
	private $sourceString = '';
	public $addonDic = array();
	public $addonDicFile = 'dict/words_addons.dic';
	public $dicStr = '';
	public $mainDic = array();
	public $mainDicHand = false;
	public $mainDicInfos = array();
	public $mainDicFile = 'dict/base_dic_full.dic';
	private $isLoadAll = false;
	private $dicWordMax = 14;
	private $simpleResult = array();
	private $finallyResult = '';
	public $isLoadDic = false;
	public $newWords = array();
	public $foundWordStr = '';
	public $loadTime = 0;

	/**
	 * 
	 * @param $source_charset
	 * @param $target_charset
	 * @param $load_alldic 
	 * @param $source
	 *
	 * @return void
	 */
	public function __construct($source_charset = 'utf-8', $target_charset = 'utf-8', $load_all = true, $source = '')
	{
		$this->addonDicFile = __DIR__ . '/' . $this->addonDicFile;
		$this->mainDicFile  = __DIR__ . '/' . $this->mainDicFile;
		$this->SetSource($source, $source_charset, $target_charset);
		$this->isLoadAll = $load_all;
		if (self::$loadInit)
			$this->LoadDict();
	}


	function __destruct()
	{
		if ($this->mainDicHand !== false) {
			@fclose($this->mainDicHand);
		}
	}

	/**
	 * 
	 * @param $key
	 * @return short int
	 */
	private function _get_index($key)
	{
		$l = strlen($key);
		$h = 0x238f13af;
		while ($l--) {
			$h += ($h << 5);
			$h ^= ord($key[$l]);
			$h &= 0x7fffffff;
		}
		return ($h % $this->mask_value);
	}

	/**
	 * 
	 * @param $key
	 * @param $type 
	 * @return short int
	 */
	public function GetWordInfos($key, $type = 'word')
	{
		if (!$this->mainDicHand) {
			$this->mainDicHand = fopen($this->mainDicFile, 'r');
		}
		$p      = 0;
		$keynum = $this->_get_index($key);
		if (isset($this->mainDicInfos[$keynum])) {
			$data = $this->mainDicInfos[$keynum];
		} else {
			//rewind( $this->mainDicHand );
			$move_pos = $keynum * 8;
			fseek($this->mainDicHand, $move_pos, SEEK_SET);
			$dat = fread($this->mainDicHand, 8);
			$arr = unpack('I1s/n1l/n1c', $dat);
			if ($arr['l'] == 0) {
				return false;
			}
			fseek($this->mainDicHand, $arr['s'], SEEK_SET);
			$data                        = @unserialize(fread($this->mainDicHand, $arr['l']));
			$this->mainDicInfos[$keynum] = $data;
		}
		if (!is_array($data) || !isset($data[$key])) {
			return false;
		}
		return ($type == 'word' ? $data[$key] : $data);
	}

	/**
	 * 
	 * @param $source
	 * @param $source_charset
	 * @param $target_charset
	 *
	 * @return bool
	 */
	public function SetSource($source, $source_charset = 'utf-8', $target_charset = 'utf-8')
	{
		$this->sourceCharSet = strtolower($source_charset);
		$this->targetCharSet = strtolower($target_charset);
		$this->simpleResult  = array();
		$this->finallyResult = array();
		$this->finallyIndex  = array();
		if ($source != '') {
			$rs = true;
			if (preg_match("/^utf/", $source_charset)) {
				$this->sourceString = iconv('utf-8', UCS2, $source);
			} else if (preg_match("/^gb/", $source_charset)) {
				$this->sourceString = iconv('utf-8', UCS2, iconv('gb18030', 'utf-8', $source));
			} else if (preg_match("/^big/", $source_charset)) {
				$this->sourceString = iconv('utf-8', UCS2, iconv('big5', 'utf-8', $source));
			} else {
				$rs = false;
			}
		} else {
			$rs = false;
		}
		return $rs;
	}

	/**
	 * 
	 * @param $rstype 1 
	 *
	 * @return void
	 */
	public function SetResultType($rstype)
	{
		$this->resultType = $rstype;
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function LoadDict($maindic = '')
	{
		$startt   = microtime(true);
		$dicAddon = $this->addonDicFile;
		if ($maindic == '' || !file_exists($maindic)) {
			$dicWords = $this->mainDicFile;
		} else {
			$dicWords          = $maindic;
			$this->mainDicFile = $maindic;
		}


		$this->mainDicHand = fopen($dicWords, 'r');


		$hw = '';
		$ds = file($dicAddon);
		foreach ($ds as $d) {
			$d = trim($d);
			if ($d == '')
				continue;
			$estr = substr($d, 1, 1);
			if ($estr == ':') {
				$hw = substr($d, 0, 1);
			} else {
				$spstr = _SP_;
				$spstr = iconv(UCS2, 'utf-8', $spstr);
				$ws    = explode(',', $d);
				$wall  = iconv('utf-8', UCS2, join($spstr, $ws));
				$ws    = explode(_SP_, $wall);
				foreach ($ws as $estr) {
					$this->addonDic[$hw][$estr] = strlen($estr);
				}
			}
		}
		$this->loadTime  = microtime(true) - $startt;
		$this->isLoadDic = true;
	}

	public function IsWord($word)
	{
		$winfos = $this->GetWordInfos($word);
		return ($winfos !== false);
	}

	/**
	 *
	 * 
	 * @return void
	 */
	public function GetWordProperty($word)
	{
		if (strlen($word) < 4) {
			return '/s';
		}
		$infos = $this->GetWordInfos($word);
		return isset($infos[1]) ? "/{$infos[1]}{$infos[0]}" : "/s";
	}

	/**
	 * 
	 * 
	 * ;
	 * @return void;
	 */
	public function SetWordInfos($word, $infos)
	{
		if (strlen($word) < 4) {
			return;
		}
		if (isset($this->mainDicInfos[$word])) {
			$this->newWords[$word]++;
			$this->mainDicInfos[$word]['c']++;
		} else {
			$this->newWords[$word]     = 1;
			$this->mainDicInfos[$word] = $infos;
		}
	}

	/**
	 * 
	 * 
	 * @return bool
	 */
	public function StartAnalysis($optimize = true)
	{
		if (!$this->isLoadDic) {
			$this->LoadDict();
		}
		$this->simpleResult = $this->finallyResult = array();
		$this->sourceString .= chr(0) . chr(32);
		$slen   = strlen($this->sourceString);
		$sbcArr = array();
		$j      = 0;
		for ($i = 0xFF00; $i < 0xFF5F; $i++) {
			$scb = 0x20 + $j;
			$j++;
			$sbcArr[$i] = $scb;
		}
		$onstr          = '';
		$lastc          = 1;
		$s              = 0;
		$ansiWordMatch  = "[0-9a-z@#%\+\.-]";
		$notNumberMatch = "[a-z@#%\+]";
		for ($i = 0; $i < $slen; $i++) {
			$c  = $this->sourceString[$i] . $this->sourceString[++$i];
			$cn = hexdec(bin2hex($c));
			$cn = isset($sbcArr[$cn]) ? $sbcArr[$cn] : $cn;
			if ($cn < 0x80) {
				if (preg_match('/' . $ansiWordMatch . '/i', chr($cn))) {
					if ($lastc != 2 && $onstr != '') {
						$this->simpleResult[$s]['w'] = $onstr;
						$this->simpleResult[$s]['t'] = $lastc;
						$this->_deep_analysis($onstr, $lastc, $s, $optimize);
						$s++;
						$onstr = '';
					}
					$lastc = 2;
					$onstr .= chr(0) . chr($cn);
				} else {
					if ($onstr != '') {
						$this->simpleResult[$s]['w'] = $onstr;
						if ($lastc == 2) {
							if (!preg_match('/' . $notNumberMatch . '/i', iconv(UCS2, 'utf-8', $onstr)))
								$lastc = 4;
						}
						$this->simpleResult[$s]['t'] = $lastc;
						if ($lastc != 4)
							$this->_deep_analysis($onstr, $lastc, $s, $optimize);
						$s++;
					}
					$onstr = '';
					$lastc = 3;
					if ($cn < 31) {
						continue;
					} else {
						$this->simpleResult[$s]['w'] = chr(0) . chr($cn);
						$this->simpleResult[$s]['t'] = 3;
						$s++;
					}
				}
			} else {
				if (($cn > 0x3FFF && $cn < 0x9FA6) || ($cn > 0xF8FF && $cn < 0xFA2D) || ($cn > 0xABFF && $cn < 0xD7A4) || ($cn > 0x3040 && $cn < 0x312B)) {
					if ($lastc != 1 && $onstr != '') {
						$this->simpleResult[$s]['w'] = $onstr;
						if ($lastc == 2) {
							if (!preg_match('/' . $notNumberMatch . '/i', iconv(UCS2, 'utf-8', $onstr)))
								$lastc = 4;
						}
						$this->simpleResult[$s]['t'] = $lastc;
						if ($lastc != 4)
							$this->_deep_analysis($onstr, $lastc, $s, $optimize);
						$s++;
						$onstr = '';
					}
					$lastc = 1;
					$onstr .= $c;
				} else {
					if ($onstr != '') {
						$this->simpleResult[$s]['w'] = $onstr;
						if ($lastc == 2) {
							if (!preg_match('/' . $notNumberMatch . '/i', iconv(UCS2, 'utf-8', $onstr)))
								$lastc = 4;
						}
						$this->simpleResult[$s]['t'] = $lastc;
						if ($lastc != 4)
							$this->_deep_analysis($onstr, $lastc, $s, $optimize);
						$s++;
					}

					if ($cn == 0x300A) {
						$tmpw = '';
						$n    = 1;
						$isok = false;
						$ew   = chr(0x30) . chr(0x0B);
						while (true) {
							$w = $this->sourceString[$i + $n] . $this->sourceString[$i + $n + 1];
							if ($w == $ew) {
								$this->simpleResult[$s]['w'] = $c;
								$this->simpleResult[$s]['t'] = 5;
								$s++;

								$this->simpleResult[$s]['w'] = $tmpw;
								$this->newWords[$tmpw]       = 1;
								if (!isset($this->newWords[$tmpw])) {
									$this->foundWordStr .= $this->_out_string_encoding($tmpw) . '/nb, ';
									$this->SetWordInfos($tmpw, array(
										'c' => 1,
										'm' => 'nb'
									));
								}
								$this->simpleResult[$s]['t'] = 13;

								$s++;

								if ($this->differMax) {
									$this->simpleResult[$s]['w'] = $tmpw;
									$this->simpleResult[$s]['t'] = 21;
									$this->_deep_analysis($tmpw, $lastc, $s, $optimize);
									$s++;
								}

								$this->simpleResult[$s]['w'] = $ew;
								$this->simpleResult[$s]['t'] = 5;
								$s++;

								$i     = $i + $n + 1;
								$isok  = true;
								$onstr = '';
								$lastc = 5;
								break;
							} else {
								$n = $n + 2;
								$tmpw .= $w;
								if (strlen($tmpw) > 60) {
									break;
								}
							}
						} //while
						if (!$isok) {
							$this->simpleResult[$s]['w'] = $c;
							$this->simpleResult[$s]['t'] = 5;
							$s++;
							$onstr = '';
							$lastc = 5;
						}
						continue;
					}

					$onstr = '';
					$lastc = 5;
					if ($cn == 0x3000) {
						continue;
					} else {
						$this->simpleResult[$s]['w'] = $c;
						$this->simpleResult[$s]['t'] = 5;
						$s++;
					}
				} //2byte symbol

			} //end 2byte char

		} //end for

		$this->_sort_finally_result();
	}

	/**
	 * 
	 * @return bool
	 */
	private function _deep_analysis(&$str, $ctype, $spos, $optimize = true)
	{

		if ($ctype == 1) {
			$slen = strlen($str);
			if ($slen < $this->notSplitLen) {
				$tmpstr   = '';
				$lastType = 0;
				if ($spos > 0)
					$lastType = $this->simpleResult[$spos - 1]['t'];
				if ($slen < 5) {
					//echo iconv(UCS2, 'utf-8', $str).'<br/>';
					if ($lastType == 4 && (isset($this->addonDic['u'][$str]) || isset($this->addonDic['u'][substr($str, 0, 2)]))) {
						$str2 = '';
						if (!isset($this->addonDic['u'][$str]) && isset($this->addonDic['s'][substr($str, 2, 2)])) {
							$str2 = substr($str, 2, 2);
							$str  = substr($str, 0, 2);
						}
						$ww                                 = $this->simpleResult[$spos - 1]['w'] . $str;
						$this->simpleResult[$spos - 1]['w'] = $ww;
						$this->simpleResult[$spos - 1]['t'] = 4;
						if (!isset($this->newWords[$this->simpleResult[$spos - 1]['w']])) {
							$this->foundWordStr .= $this->_out_string_encoding($ww) . '/mu, ';
							$this->SetWordInfos($ww, array(
								'c' => 1,
								'm' => 'mu'
							));
						}
						$this->simpleResult[$spos]['w'] = '';
						if ($str2 != '') {
							$this->finallyResult[$spos - 1][] = $ww;
							$this->finallyResult[$spos - 1][] = $str2;
						}
					} else {
						$this->finallyResult[$spos][] = $str;
					}
				} else {
					$this->_deep_analysis_cn($str, $ctype, $spos, $slen, $optimize);
				}
			} else {
				$this->_deep_analysis_cn($str, $ctype, $spos, $slen, $optimize);
			}
		} else {
			if ($this->toLower) {
				$this->finallyResult[$spos][] = strtolower($str);
			} else {
				$this->finallyResult[$spos][] = $str;
			}
		}
	}

	/**
	 * 
	 * @parem $str
	 * @return void
	 */
	private function _deep_analysis_cn(&$str, $lastec, $spos, $slen, $optimize = true)
	{
		$quote1 = chr(0x20) . chr(0x1C);
		$tmparr = array();
		$hasw   = 0;
		if ($spos > 0 && $slen < 11 && $this->simpleResult[$spos - 1]['w'] == $quote1) {
			$tmparr[] = $str;
			if (!isset($this->newWords[$str])) {
				$this->foundWordStr .= $this->_out_string_encoding($str) . '/nq, ';
				$this->SetWordInfos($str, array(
					'c' => 1,
					'm' => 'nq'
				));
			}
			if (!$this->differMax) {
				$this->finallyResult[$spos][] = $str;
				return;
			}
		}
		for ($i = $slen - 1; $i > 0; $i -= 2) {
			$nc = $str[$i - 1] . $str[$i];
			if ($i <= 2) {
				$tmparr[] = $nc;
				$i        = 0;
				break;
			}
			$isok = false;
			$i    = $i + 1;
			for ($k = $this->dicWordMax; $k > 1; $k = $k - 2) {
				if ($i < $k)
					continue;
				$w = substr($str, $i - $k, $k);
				if (strlen($w) <= 2) {
					$i = $i - 1;
					break;
				}
				if ($this->IsWord($w)) {
					$tmparr[] = $w;
					$i        = $i - $k + 1;
					$isok     = true;
					break;
				}
			}
			//echo '<hr />';
			if (!$isok)
				$tmparr[] = $nc;
		}
		$wcount = count($tmparr);
		if ($wcount == 0)
			return;
		$this->finallyResult[$spos] = array_reverse($tmparr);
		if ($optimize) {
			$this->_optimize_result($this->finallyResult[$spos], $spos);
		}
	}

	/**
	 * @return bool
	 */
	private function _optimize_result(&$smarr, $spos)
	{
		$newarr = array();
		$prePos = $spos - 1;
		$arlen  = count($smarr);
		$i      = $j = 0;
		if ($prePos > -1 && !isset($this->finallyResult[$prePos])) {
			$lastw = $this->simpleResult[$prePos]['w'];
			$lastt = $this->simpleResult[$prePos]['t'];
			if (($lastt == 4 || isset($this->addonDic['c'][$lastw])) && isset($this->addonDic['u'][$smarr[0]])) {
				$this->simpleResult[$prePos]['w'] = $lastw . $smarr[0];
				$this->simpleResult[$prePos]['t'] = 4;
				if (!isset($this->newWords[$this->simpleResult[$prePos]['w']])) {
					$this->foundWordStr .= $this->_out_string_encoding($this->simpleResult[$prePos]['w']) . '/mu, ';
					$this->SetWordInfos($this->simpleResult[$prePos]['w'], array(
						'c' => 1,
						'm' => 'mu'
					));
				}
				$smarr[0] = '';
				$i++;
			}
		}
		for (; $i < $arlen; $i++) {

			if (!isset($smarr[$i + 1])) {
				$newarr[$j] = $smarr[$i];
				break;
			}
			$cw      = $smarr[$i];
			$nw      = $smarr[$i + 1];
			$ischeck = false;
			if (isset($this->addonDic['c'][$cw]) && isset($this->addonDic['u'][$nw])) {
				if ($this->differMax) {
					$newarr[$j] = chr(0) . chr(0x28);
					$j++;
					$newarr[$j] = $cw;
					$j++;
					$newarr[$j] = $nw;
					$j++;
					$newarr[$j] = chr(0) . chr(0x29);
					$j++;
				}
				$newarr[$j] = $cw . $nw;
				if (!isset($this->newWords[$newarr[$j]])) {
					$this->foundWordStr .= $this->_out_string_encoding($newarr[$j]) . '/mu, ';
					$this->SetWordInfos($newarr[$j], array(
						'c' => 1,
						'm' => 'mu'
					));
				}
				$j++;
				$i++;
				$ischeck = true;
			} else if (isset($this->addonDic['n'][$smarr[$i]])) {
				$is_rs = false;
				if (strlen($nw) == 4) {
					$winfos = $this->GetWordInfos($nw);
					if (isset($winfos['m']) && ($winfos['m'] == 'r' || $winfos['m'] == 'c' || $winfos['c'] > 500)) {
						$is_rs = true;
					}
				}
				if (!isset($this->addonDic['s'][$nw]) && strlen($nw) < 5 && !$is_rs) {
					$newarr[$j] = $cw . $nw;
					if (strlen($nw) == 2 && isset($smarr[$i + 2]) && strlen($smarr[$i + 2]) == 2 && !isset($this->addonDic['s'][$smarr[$i + 2]])) {
						$newarr[$j] .= $smarr[$i + 2];
						$i++;
					}
					if (!isset($this->newWords[$newarr[$j]])) {
						$this->SetWordInfos($newarr[$j], array(
							'c' => 1,
							'm' => 'nr'
						));
						$this->foundWordStr .= $this->_out_string_encoding($newarr[$j]) . '/nr, ';
					}
					if (strlen($nw) == 4) {
						$j++;
						$newarr[$j] = chr(0) . chr(0x28);
						$j++;
						$newarr[$j] = $cw;
						$j++;
						$newarr[$j] = $nw;
						$j++;
						$newarr[$j] = chr(0) . chr(0x29);
					}

					$j++;
					$i++;
					$ischeck = true;
				}
			} else if (isset($this->addonDic['a'][$nw])) {
				$is_rs = false;
				if (strlen($cw) > 2) {
					$winfos = $this->GetWordInfos($cw);
					if (isset($winfos['m']) && ($winfos['m'] == 'a' || $winfos['m'] == 'r' || $winfos['m'] == 'c' || $winfos['c'] > 500)) {
						$is_rs = true;
					}
				}
				if (!isset($this->addonDic['s'][$cw]) && !$is_rs) {
					$newarr[$j] = $cw . $nw;
					if (!isset($this->newWords[$newarr[$j]])) {
						$this->foundWordStr .= $this->_out_string_encoding($newarr[$j]) . '/na, ';
						$this->SetWordInfos($newarr[$j], array(
							'c' => 1,
							'm' => 'na'
						));
					}
					$i++;
					$j++;
					$ischeck = true;
				}
			} else if ($this->unitWord) {
				if (strlen($cw) == 2 && strlen($nw) == 2 && !isset($this->addonDic['s'][$cw]) && !isset($this->addonDic['t'][$cw]) && !isset($this->addonDic['a'][$cw]) && !isset($this->addonDic['s'][$nw]) && !isset($this->addonDic['c'][$nw])) {
					$newarr[$j] = $cw . $nw;
					if (isset($smarr[$i + 2]) && strlen($smarr[$i + 2]) == 2 && (isset($this->addonDic['a'][$smarr[$i + 2]]) || isset($this->addonDic['u'][$smarr[$i + 2]]))) {
						$newarr[$j] .= $smarr[$i + 2];
						$i++;
					}
					if (!isset($this->newWords[$newarr[$j]])) {
						$this->foundWordStr .= $this->_out_string_encoding($newarr[$j]) . '/ms, ';
						$this->SetWordInfos($newarr[$j], array(
							'c' => 1,
							'm' => 'ms'
						));
					}
					$i++;
					$j++;
					$ischeck = true;
				}
			}

			if (!$ischeck) {
				$newarr[$j] = $cw;
				if ($this->differMax && !isset($this->addonDic['s'][$cw]) && strlen($cw) < 5 && strlen($nw) < 7) {
					$slen    = strlen($nw);
					$hasDiff = false;
					for ($y = 2; $y <= $slen - 2; $y = $y + 2) {
						$nhead = substr($nw, $y - 2, 2);
						$nfont = $cw . substr($nw, 0, $y - 2);
						if ($this->IsWord($nfont . $nhead)) {
							if (strlen($cw) > 2)
								$j++;
							$hasDiff    = true;
							$newarr[$j] = $nfont . $nhead;
						}
					}
				}
				$j++;
			}
		} //end for
		$smarr = $newarr;
	}

	/**
	 * @return void
	 */
	private function _sort_finally_result()
	{
		$newarr = array();
		$i      = 0;
		foreach ($this->simpleResult as $k => $v) {
			if (empty($v['w']))
				continue;
			if (isset($this->finallyResult[$k]) && count($this->finallyResult[$k]) > 0) {
				foreach ($this->finallyResult[$k] as $w) {
					if (!empty($w)) {
						$newarr[$i]['w'] = $w;
						$newarr[$i]['t'] = 20;
						$i++;
					}
				}
			} else if ($v['t'] != 21) {
				$newarr[$i]['w'] = $v['w'];
				$newarr[$i]['t'] = $v['t'];
				$i++;
			}
		}
		$this->finallyResult = $newarr;
		$newarr              = '';
	}

	private function _out_string_encoding(&$str)
	{
		$rsc = $this->_source_result_charset();
		if ($rsc == 1) {
			$rsstr = iconv(UCS2, 'utf-8', $str);
		} else if ($rsc == 2) {
			$rsstr = iconv('utf-8', 'gb18030', iconv(UCS2, 'utf-8', $str));
		} else {
			$rsstr = iconv('utf-8', 'big5', iconv(UCS2, 'utf-8', $str));
		}
		return $rsstr;
	}

	/**
	 * 
	 * @return string
	 */
	public function GetFinallyResult($spword = ' ', $word_meanings = false)
	{
		$rsstr = '';
		foreach ($this->finallyResult as $v) {
			if ($this->resultType == 2 && ($v['t'] == 3 || $v['t'] == 5)) {
				continue;
			}
			$m = '';
			if ($word_meanings) {
				$m = $this->GetWordProperty($v['w']);
			}
			$w = $this->_out_string_encoding($v['w']);
			if ($w != ' ') {
				if ($word_meanings) {
					$rsstr .= $spword . $w . $m;
				} else {
					$rsstr .= $spword . $w;
				}
			}
		}
		return $rsstr;
	}

	/**
	 * @return array()
	 */
	public function GetSimpleResult()
	{
		$rearr = array();
		foreach ($this->simpleResult as $k => $v) {
			if (empty($v['w']))
				continue;
			$w = $this->_out_string_encoding($v['w']);
			if ($w != ' ')
				$rearr[] = $w;
		}
		return $rearr;
	}

	/**
	 * @return array()
	 */
	public function GetSimpleResultAll()
	{
		$rearr = array();
		foreach ($this->simpleResult as $k => $v) {
			$w = $this->_out_string_encoding($v['w']);
			if ($w != ' ') {
				$rearr[$k]['w'] = $w;
				$rearr[$k]['t'] = $v['t'];
			}
		}
		return $rearr;
	}

	/**
	 * @return array('word'=>count,...)
	 */
	public function GetFinallyIndex()
	{
		$rearr = array();
		foreach ($this->finallyResult as $v) {
			if ($this->resultType == 2 && ($v['t'] == 3 || $v['t'] == 5)) {
				continue;
			}
			$w = $this->_out_string_encoding($v['w']);
			if ($w == ' ') {
				continue;
			}
			if (isset($rearr[$w])) {
				$rearr[$w]++;
			} else {
				$rearr[$w] = 1;
			}
		}
		arsort($rearr);
		return $rearr;
	}

	/**
	 * @return string
	 */
	public function GetFinallyKeywords($num = 10)
	{
		$n     = 0;
		$arr   = $this->GetFinallyIndex();
		$okstr = '';
		foreach ($arr as $k => $v) {
			if (strlen($k) == 1) {
				continue;
			} elseif (strlen($k) == 2 && preg_match('/[^0-9a-zA-Z]/', $k)) {
				continue;
			} elseif (strlen($k) < 4 && !preg_match('/[a-zA-Z]/', $k)) {
				continue;
			}
			$okstr .= ($okstr == '' ? $k : ',' . $k);
			$n++;
			if ($n > $num)
				break;
		}
		return $okstr;
	}

	/**
	 * @return int
	 */
	private function _source_result_charset()
	{
		if (preg_match("/^utf/", $this->targetCharSet)) {
			$rs = 1;
		} else if (preg_match("/^gb/", $this->targetCharSet)) {
			$rs = 2;
		} else if (preg_match("/^big/", $this->targetCharSet)) {
			$rs = 3;
		} else {
			$rs = 4;
		}
		return $rs;
	}

	/**
	 * @return void
	 */
	public function MakeDict($source_file, $target_file = '')
	{
		$target_file = ($target_file == '' ? $this->mainDicFile : $target_file);
		$allk        = array();
		$fp          = fopen($source_file, 'r');
		while ($line = fgets($fp, 64)) {
			if ($line[0] == '@')
				continue;
			list($w, $r, $a) = explode(',', $line);


			$a = trim($a);
			$w = iconv('utf-8', UCS2, $w);
			$k = $this->_get_index($w);
			if (isset($allk[$k]))
				$allk[$k][$w] = array(
					$r,
					$a
				);
			else
				$allk[$k][$w] = array(
					$r,
					$a
				);
		}
		fclose($fp);
		$fp         = fopen($target_file, 'w');
		$heade_rarr = array();
		$alldat     = '';
		$start_pos  = $this->mask_value * 8;
		foreach ($allk as $k => $v) {
			$dat  = serialize($v);
			$dlen = strlen($dat);
			$alldat .= $dat;

			$heade_rarr[$k][0] = $start_pos;
			$heade_rarr[$k][1] = $dlen;
			$heade_rarr[$k][2] = count($v);

			$start_pos += $dlen;
		}
		print_r($heade_rarr);
		unset($allk);
		for ($i = 0; $i < $this->mask_value; $i++) {
			if (!isset($heade_rarr[$i])) {
				$heade_rarr[$i] = array(
					0,
					0,
					0
				);
			}
			fwrite($fp, pack("Inn", $heade_rarr[$i][0], $heade_rarr[$i][1], $heade_rarr[$i][2]));
		}
		fwrite($fp, $alldat);
		fclose($fp);
	}

	/**
	 * @return void
	 */
	public function ExportDict($targetfile)
	{
		if (!$this->mainDicHand) {
			$this->mainDicHand = fopen($this->mainDicFile, 'r');
		}
		$fp = fopen($targetfile, 'w');
		for ($i = 0; $i <= $this->mask_value; $i++) {
			$move_pos = $i * 8;
			fseek($this->mainDicHand, $move_pos, SEEK_SET);
			$dat = fread($this->mainDicHand, 8);
			$arr = unpack('I1s/n1l/n1c', $dat);
			if ($arr['l'] == 0) {
				continue;
			}
			fseek($this->mainDicHand, $arr['s'], SEEK_SET);
			$data = @unserialize(fread($this->mainDicHand, $arr['l']));
			if (!is_array($data))
				continue;
			foreach ($data as $k => $v) {
				$w = iconv(UCS2, 'utf-8', $k);
				fwrite($fp, "{$w},{$v[0]},{$v[1]}\n");
			}
		}
		fclose($fp);
		return true;
	}
}
