<?php
/**
 * preg_rand and preg_range functions
 * 
 * @author Avid [tg:@Av_id]
 * @version 1.0
 */


define("ASCII_RANGE", implode('', range("\0", "\xff")));
define("WORD_RANGE", "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_");
define("ALPHBA_RANGE", 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
define("LOWER_RANGE", 'abcdefghijklmnopqrstuvwxyz');
define("UPPER_RANGE", 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define("CONTROL_RANGE", "\0\1\2\3\4\5\6\7\x8\x9\xa\xb\xc\xd\xe\xf\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f\x7f");

// needed functions
function xor_chars(string $chars, string $string){
    $str = '';
    for($i = 0; isset($chars[$i]); ++$i)
        if(strpos($string, $chars[$i]) === false)
            $str .= $chars[$i];
    return $str;
}
function preg_range_list(string $list, bool $i = false, bool $notnot = false){
	if(!$notnot && $list[0] == '^'){
		$not = true;
		$list = substr($list, 1);
	}else
		$not = false;
	$list = preg_replace_callback("/\\\\\\\\|\\\\\]|\\\\[-\"'nrtevfsSdDwWRhHKLU]|\\\\[0-7]{1,3}|".
        "\\\\x[0-9a-fA-F]{1,2}|\\\\b[01]{1,8}|\\\\u[0-9a-fA-F]{1,4}|(?:.|\n)-(?:.|\n)/", function($range)use($i){
        $range = $range[0];
        switch($range){
            case '\\\\':
                return '\\';
            case '\-':
                return '-';
            case '\]':
                return ']';
            case '\\\\':
				return '\\';
			break;
			case '\"':
				return '"';
			break;
			case "\\'":
				return "'";
			break;
			case '\n':
				return "\n";
			break;
			case '\r':
				return "\r";
			break;
			case '\t':
				return "\t";
			break;
			case '\e':
				return "\e";
			break;
			case '\v':
				return "\v";
			break;
			case '\f':
				return "\f";
			break;
			case '\s':
				return " \n\r\t";
			break;
			case '\S':
				return str_replace(array(' ', "\n", "\r", "\t"), '', ASCII_RANGE);
			break;
			case '\d':
				return '0123456789';
			break;
			case '\D':
				return str_replace(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), '', ASCII_RANGE);
			break;
			case '\w':
				return WORD_RANGE;
			break;
			case '\W':
				return str_replace(str_split(WORD_RANGE), '', ASCII_RANGE);
			break;
			case '\R':
				return "\r\n";
			break;
			case '\h':
				return " \t";
			break;
			case '\H':
				return str_replace(array(' ', "\t"), '', ASCII_RANGE);
			break;
			case '\K':
				return '';
			break;
			case '\L':
				return LOWER_RANGE;
			break;
			case '\U':
				return UPPER_RANGE;
			break;
            default:
                if($range[0] == '\\'){
                    if(is_numeric($block[1]))
                        $list = chr(octdec(substr($block, 1)));
                    elseif($block[1] == 'x')
                        $list = chr(hexdec(substr($block, 2)));
                    elseif($block[1] == 'b')
                        $list = chr(bindec(substr($block, 2)));
                    elseif($block[1] == 'u')
                        $list = json_decode('"' . substr($block, 2) . '"');
                    else $list = $block[1];
                    return $list;
                }
        }
		return implode('', range($range[0], $range[2]));
	}, $list);
    $list = array_unique(array_merge(str_split(strtolower($list)), str_split(strtoupper($list))));
	if($not)
		$list = xor_chars(ASCII_RANGE, $list);
	return $list;
}
function preg_range_repeat(array $list, int $from = 0, int $to = null){
	if($to === null)$to = count($list);
	if($from < 0 || $to < 0)return false;
	if($to < $from)$to = 0;
	$ranges = array();
	while($from++ <= $to){
		if($from == 1)
			continue;
		$range = $list;
		for($i = 1;$i < $from - 1;++$i){
			$arr = array();
			foreach($range as $r)
				foreach($list as $c)
					$arr[] = $r . $c;
			$range = $arr;
		}
		$ranges[] = $range;
	}
	if($ranges === array())
		return array();
	return call_user_func_array('array_merge', $ranges);
}

/**
 * preg_range function
 * 
 * @param string $pattern
 * @return array
 */
function preg_range(string $pattern){
	if($pattern === '')
		return array();
	if(in_array($pattern[0], array('/', '#', '|')) && ($p = strrpos($pattern, $pattern[0])) !== 0){
		$flags = substr($pattern, $p);
		$pattern = substr($pattern, 1, $p - 1);
	}else $flags = '';
	$i = strpos($flags, 'i') !== false;
	$range = array('');
	preg_replace_callback("/\(\?i\)|(?:\\\\Q(?:\\\\[^E]|[^\\\\])*\\\\E|\[(?:\\\]|[^\]])+\]|".
        "(?<x>\((?:\g<x>|\\\\\)|\[(?:\\\]|[^\]])+\]|[^\)])*\))|(?<!\\\\)(?:\|(?:.|\n)*|\+(?:.|\n)*|\*(?:.|\n)*|\^(?:.|\n)*)|".
		"(?:\\\\\\\\|\\\\[0-7]{1,3}|\\\\x[0-9a-fA-F]{1,2}|\\\\b[01]{1,8}|\\\\u[0-9a-fA-F]{1,4}|\\\\[^x0-9bnrtveu]|".
		"\\\\.|.|\s))(?:\{(?:[0-9]+|[0-9]+,[0-9]+|,[0-9]+|[0-9]+,)\}|)/", function($block)use(&$range, &$i){
        $block = $block[0];
        $p = strrpos($block, '{');
        $braw = substr($block, 0, $p);
		switch($braw){
			case '\\\\':
				$list = array('\\');
			break;
			case '\"':
				$list = array('"');
			break;
			case "\\'":
				$list = array("'");
			break;
			case '\n':
				$list = array("\n");
			break;
			case '\r':
				$list = array("\r");
			break;
			case '\t':
				$list = array("\t");
			break;
			case '\e':
				$list = array("\e");
			break;
			case '\v':
				$list = array("\v");
			break;
			case '\f':
				$list = array("\f");
			break;
			case '\s':
				$list = array(' ', "\n", "\r", "\t");
			break;
			case '\S':
				$list = str_split(str_replace(array(' ', "\n", "\r", "\t"), '', ASCII_RANGE));
			break;
			case '\d':
				$list = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
			break;
			case '\D':
				$list = str_split(str_replace(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), '', ASCII_RANGE));
			break;
			case '\w':
				$list = str_split(WORD_RANGE);
			break;
			case '\W':
				$list = str_split(str_replace(str_split(WORD_RANGE), '', ASCII_RANGE));
			break;
			case '.':
				$list = $GLOBALS['ASCII_LIST'];
			break;
			case '\R':
				$list = array("\r\n");
			break;
			case '\h':
				$list = array(' ', "\t");
			break;
			case '\H':
				$list = str_split(str_replace(array(' ', "\t"), '', ASCII_RANGE));
			break;
			case '\K':
				$range = array('');
			break;
			case '\L':
				$list = str_split(LOWER_RANGE);
			break;
			case '\U':
				$list = str_split(UPPER_RANGE);
			break;
			case '[[:alnum:]]':
				$list = str_split(WORD_RANGE);
			break;
			case '[[:alpha:]]':
				$list = str_split(ALPHBA_RANGE);
			break;
			case '[[:ascii:]]':
				$list = $GLOBALS['ASCII_LIST'];
			break;
			case '[[:blank:]]':
				$list = array(' ', "\t");
			break;
			case '[[:cntrl:]]':
				$list = str_split(CONTROL_RANGE);
			break;
            case '(?i)':
                $i = true;
            return '';
			default:
				if($block[0] == '\\' && $block[1] == 'Q'){
                    $block = substr($block, 2, -2);
                    if($i){
                        $list = array('');
                        for($j = 0; isset($block[$j]); ++$j){
                            $p = array_unique(array(strtolower($block[$j]), strtoupper($block[$j])));
                            $arr = array();
                            foreach($list as $r)
                                foreach($p as $c)
                                    $arr[] = $r . $c;
                            $list = $arr;
                        }
                    }else $list = array($block);
                }else switch($block[0]){
					case '\\':
						$p = strpos($block, '{');
						if(is_numeric($block[1]))
							$list = chr(octdec(substr($block, 1, $p === false ? strlen($block) - 1 : $p)));
						elseif($block[1] == 'x')
							$list = chr(hexdec(substr($block, 2, $p === false ? strlen($block) - 2 : $p)));
						elseif($block[1] == 'b')
							$list = chr(bindec(substr($block, 2, $p === false ? strlen($block) - 2 : $p)));
						elseif($block[1] == 'u')
							$list = json_decode('"' . substr($block, 2, $p === false ? strlen($block) - 2 : $p) . '"');
						else $list = $block[1];
                        $list = $i ? array_unique(array(strtolower($list), strtoupper($list))) : array($list);
					break;
					case '|':
						$range = array_merge($range, preg_range(substr($block, 1)));
					break;
					case '+':
						$list = preg_range(substr($block, 1));
						$arr = array();
						foreach($range as $r)
							foreach($list as $c)
								$arr[] = $r . $c;
						$range = array_merge($range, $arr);
					break;
					case '*':
						$list = preg_range(substr($block, 1));
						$arr = array();
						foreach($range as $r)
							foreach($list as $c)
								$arr[] = $r . $c;
						$range = array_merge($range, $list, $arr);
					break;
					case '^':
						$list = preg_range(substr($block, 1));
						$arr = array();
						foreach($range as $r)
							if(array_search($list, $r) === false)
								$arr[] = $r;
						$range = $arr;
					break;
					case '[':
						if($block[strlen($block) - 1] == '}'){
							$p = strrpos($block, ']');
							$repeat = explode(',', substr($block, $p + 2, -1), 2);
							$block = substr($block, 1, $p - 1);
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = null;
							else
								$repeat[1] = (int)$repeat[1];
						}else{
							$block = substr($block, 1, -1);
							$repeat = array(1, 1);
                        }
						$list = preg_range_repeat(preg_range_list($block, $i), $repeat[0], $repeat[1]);
						$arr = array();
						foreach($range as $r)
							foreach($list as $c)
								$arr[] = $r . $c;
						$range = $arr;
					return '';
					case '(':
						if($block[strlen($block) - 1] == '}'){
							$p = strrpos($block, ')');
							$repeat = explode(',', substr($block, $p + 2, -1), 2);
							$block = substr($block, 1, $p - 1);
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = null;
							else
								$repeat[1] = (int)$repeat[1];
						}else{
							$block = substr($block, 1, -1);
							$repeat = array(1, 1);
						}
						$list = preg_range_repeat(preg_range($i ? "/$block/i" : "/$block/"), $repeat[0], $repeat[1]);
						$arr = array();
						foreach($range as $r)
							foreach($list as $c)
								$arr[] = $r . $c;
						$range = $arr;
					return '';
					default:
						if(isset($block[3]) && $block[1] == '{'){
							$repeat = explode(',', substr($block, 2, -1));
                            $block = $block[0];
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = null;
							else
								$repeat[1] = (int)$repeat[1];
						}else
							$repeat = array(1, 1);
						$block = $i ? array_unique(array(strtolower($block), strtoupper($block))) : array($block);
						$list = preg_range_repeat($block, $repeat[0], $repeat[1]);
						$arr = array();
						foreach($range as $r)
							foreach($list as $c)
								$arr[] = $r . $c;
						$range = $arr;
					return '';
				}
		}
		if(isset($list)){
			if($block[strlen($block) - 1] == '}'){
				$p = strrpos($block, '{');
				$repeat = explode(',', substr($block, $p + 1, -1));
				if($repeat[0] === '')
					$repeat[0] = 0;
				else $repeat[0] = (int)$repeat[0];
				if(!isset($repeat[1]))
					$repeat[1] = $repeat[0];
				elseif($repeat[1] === '')
					$repeat[1] = null;
				else
					$repeat[1] = (int)$repeat[1];
			}else
				$repeat = array(1, 1);
			$list = preg_range_repeat($list, $repeat[0], $repeat[1]);
			$arr = array();
			foreach($range as $r)
				foreach($list as $c)
					$arr[] = $r . $c;
			$range = $arr;
		}
		return '';
	}, $pattern);
	if($range === array(''))
		return array();
	return $range;
}

/**
 * preg_rand function
 * 
 * @param string $pattern
 * @return string
 */
function preg_rand(string $pattern){
	if($pattern === '')
		return array();
	if(in_array($pattern[0], array('/', '#', '|')) && ($p = strrpos($pattern, $pattern[0])) !== 0){
		$flags = substr($pattern, $p);
		$pattern = substr($pattern, 1, $p - 1);
	}else $flags = '';
	$i = strpos($flags, 'i') !== false;
	$rand = '';
	preg_replace_callback("/\(\?i\)|(?:\\\\Q(?:\\\\[^E]|[^\\\\])*\\\\E|\[(?:\\\]|[^\]])+\]|(?<x>\((?:\g<x>|\\\\\)|".
    "\[(?:\\\]|[^\]])+\]|[^\)])*\))|(?<!\\\\)(?:\|(?:.|\n)*|\+(?:.|\n)*|\*(?:.|\n)*|\^(?:.|\n)*)|".
	"(?:\\\\\\\\|\\\\[0-7]{1,3}|\\\\x[0-9a-fA-F]{1,2}|\\\\b[01]{1,8}|\\\\u[0-9a-fA-F]{1,4}|\\\\[^x0-9bnrtveu]|".
	"\\\\.|.|\s))(?:\{(?:[0-9]+|[0-9]+,[0-9]+|,[0-9]+|[0-9]+,)\}|)/", function($block)use(&$rand, &$i){
        $block = $block[0];
        $p = strrpos($block, '{');
        $braw = substr($block, 0, $p);
		switch($braw){
			case '\\\\':
				$list = array('\\');
			break;
			case '\"':
				$list = array('"');
			break;
			case "\\'":
				$list = array("'");
			break;
			case '\n':
				$list = array("\n");
			break;
			case '\r':
				$list = array("\r");
			break;
			case '\t':
				$list = array("\t");
			break;
			case '\e':
				$list = array("\e");
			break;
			case '\v':
				$list = array("\v");
			break;
			case '\f':
				$list = array("\f");
			break;
			case '\s':
				$list = array(' ', "\n", "\r", "\t");
			break;
			case '\S':
				$list = str_split(str_replace(array(' ', "\n", "\r", "\t"), '', ASCII_RANGE));
			break;
			case '\d':
				$list = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
			break;
			case '\D':
				$list = str_split(str_replace(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), '', ASCII_RANGE));
			break;
			case '\w':
				$list = str_split(WORD_RANGE);
			break;
			case '\W':
				$list = str_split(str_replace(str_split(WORD_RANGE), '', ASCII_RANGE));
			break;
			case '.':
				$list = $GLOBALS['ASCII_LIST'];
			break;
			case '\R':
				$list = array("\r\n");
			break;
			case '\h':
				$list = array(' ', "\t");
			break;
			case '\H':
				$list = str_split(str_replace(array(' ', "\t"), '', ASCII_RANGE));
			break;
			case '\K':
				$range = array('');
			break;
			case '\L':
				$list = str_split(LOWER_RANGE);
			break;
			case '\U':
				$list = str_split(UPPER_RANGE);
			break;
			case '[[:alnum:]]':
				$list = str_split(WORD_RANGE);
			break;
			case '[[:alpha:]]':
				$list = str_split(ALPHBA_RANGE);
			break;
			case '[[:ascii:]]':
				$list = $GLOBALS['ASCII_LIST'];
			break;
			case '[[:blank:]]':
				$list = array(' ', "\t");
			break;
			case '[[:cntrl:]]':
				$list = str_split(CONTROL_RANGE);
			break;
            case '(?i)':
                $i = true;
            return '';
			default:
                if($block[0] == '\\' && $block[1] == 'Q'){
                    $block = substr($block, 2, -2);
                    if($i){
                        $list = '';
                        for($c = 0; isset($block[$c]); ++$c){
                            $p = array_unique(array(strtolower($block[$c]), strtoupper($block[$c])));
                            $list.= $p[array_rand($p)];
                        }
                        $list = array($list);
                    }else $list = array($block);
                }else switch($block[0]){
					case '\\':
						$p = strpos($block, '{');
						if(is_numeric($block[1]))
							$list = chr(octdec(substr($block, 1, $p === false ? strlen($block) - 1 : $p)));
						elseif($block[1] == 'x')
							$list = chr(hexdec(substr($block, 2, $p === false ? strlen($block) - 2 : $p)));
						elseif($block[1] == 'b')
							$list = chr(bindec(substr($block, 2, $p === false ? strlen($block) - 2 : $p)));
						elseif($block[1] == 'u')
							$list = json_decode('"' . substr($block, 2, $p === false ? strlen($block) - 2 : $p) . '"');
						else $list = $block[1];
                        $list = $i ? array_unique(array(strtolower($list), strtoupper($list))) : array($list);
					break;
					case '|':
						$rand .= preg_rand(substr($block, 1));
					break;
					case '+':
						if(rand(0, 1) === 1)
							$rand .= preg_rand(substr($block, 1));
					break;
					case '*':
						switch(rand(0, 2)){
							case 0:
								$rand = preg_rand(substr($block, 1));
							break;
							case 1:
								$rand.= preg_rand(substr($block, 1));
						}
					break;
					case '[':
						if($block[strlen($block) - 1] == '}'){
							$p = strrpos($block, ']');
							$repeat = explode(',', substr($block, $p + 2, -1), 2);
							$block = substr($block, 1, $p - 1);
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = null;
							else
								$repeat[1] = (int)$repeat[1];
						}else{
							$block = substr($block, 1, -1);
							$repeat = array(1, 1);
                        }
						$list = preg_range_list($block, $i);
						$l = count($list);
						if($repeat[1] === null)$repeat[1] = $l;
						$l = floor(rand($repeat[0] * $l, $repeat[1] * $l) / $l);
						for($c = 0;$c++ < $l;)
							$rand .= $list[array_rand($list)];
					return '';
					case '(':
						if($block[strlen($block) - 1] == '}'){
							$p = strrpos($block, ')');
							$repeat = explode(',', substr($block, $p + 2, -1), 2);
							$block = substr($block, 1, $p - 1);
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = 1;
							else
								$repeat[1] = (int)$repeat[1];
						}else{
							$block = substr($block, 1, -1);
							$repeat = array(1, 1);
						}
						$l = rand($repeat[0], $repeat[1]);
						for($c = 0;$c++ < $l;)
							$rand .= preg_rand($i ? "/$block/i" : "/$block/");
					return '';
					default:
						if(isset($block[3]) && $block[1] == '{'){
							$repeat = explode(',', substr($block, 2, -1));
                            $block = $block[0];
							if($repeat[0] === '')
								$repeat[0] = 0;
							else $repeat[0] = (int)$repeat[0];
							if(!isset($repeat[1]))
								$repeat[1] = $repeat[0];
							elseif($repeat[1] === '')
								$repeat[1] = 1;
							else
								$repeat[1] = (int)$repeat[1];
						}else
							$repeat = array(1, 1);
						$block = $i ? array_unique(array(strtolower($block), strtoupper($block))) : array($block);
						$l = rand($repeat[0], $repeat[1]);
						for($c = 0;$c++ < $l;)
							$rand .= $block[array_rand($block)];
					return '';
				}
		}
  		if(isset($list)){
			if($block[strlen($block) - 1] == '}'){
				$p = strrpos($block, '{');
				$repeat = explode(',', substr($block, $p + 1, -1));
				if($repeat[0] === '')
					$repeat[0] = 0;
				else $repeat[0] = (int)$repeat[0];
				if(!isset($repeat[1]))
					$repeat[1] = $repeat[0];
				elseif($repeat[1] === '')
					$repeat[1] = null;
				else
					$repeat[1] = (int)$repeat[1];
			}else
				$repeat = array(1, 1);
            $l = rand($repeat[0], $repeat[1]);
            for($c = 0;$c++ < $l;)
                $rand .= $list[array_rand($list)];
		}
		return '';
	}, $pattern);
	return $rand;
}

?>
