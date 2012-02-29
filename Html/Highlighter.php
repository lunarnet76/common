<?php
namespace Common\Html {
    class Highlighter {

        public static function php($code) {
            $html=substr(highlight_string($code, 1),strlen('<code><span style="color: #000000"><span style="color: #0000BB">&lt;?php')+1,-strlen('<span style="color: #0000BB">?&gt;</span>'.PHP_EOL.'</span>'.PHP_EOL.'</code>'));
           
            $html= preg_replace_callback('|<span style="color: #([^"]*)">(\$)?([^<]*)?<|', function($match) {
                                if (!empty($match[2]))
                                    return '<span class="php-var">' . $match[2] . $match[3] . '<';
                                switch ($match[1]) {
                                    case 'FF8000':
                                        return '<span class="php-comment">' . $match[3] . '<';
                                        break;
                                    case '007700':
                                        return '<span class="php-class">' . $match[3] . '<';
                                        break;
                                    case '0000BB':
                                        return '<span class="php-keyword">' . $match[3] . '<';
                                        break;
                                    case 'DD0000':
                                        return '<span class="php-text">' . $match[3] . '<';
                                        break;
                                    default:
                                        return $match[0];
                                }
                            }, $html);
            return '<div class="code"><code>' . self::_addLineNumbers('<span class="php-tag">&lt;?php'.$html) . '<span class="php-tag">?&gt;</span></code></div>';
        }

        public static function javascript($code) {
            $html= preg_replace_callback('|<span style="color: #([^"]*)">(\$)?([^<]*)?<|', function($match) {
                                if (!empty($match[2]))
                                    return '<span class="php-var">' . $match[2] . $match[3] . '<';
                                switch ($match[1]) {
                                    case 'FF8000':
                                        return '<span class="php-comment">' . $match[3] . '<';
                                        break;
                                    case '007700':
                                        return '<span class="php-class">' . $match[3] . '<';
                                        break;
                                    case '0000BB':
                                        return '<span class="php-keyword">' . $match[3] . '<';
                                        break;
                                    case 'DD0000':
                                        return '<span class="php-text">' . $match[3] . '<';
                                        break;
                                    default:
                                        return $match[0];
                                }
                            }, substr(highlight_string('<?php'.$code.PHP_EOL.'?>', 1),80,-50));
            return '<div class="code"><code>' . self::_addLineNumbers($html) . '<span class="php-class">&lt;</span><span class="php-keyword">/script</span><span class="php-class">&gt;</code></div>';
        }

        public static function css($code) {
            $code=str_replace(array('<style type="text/css">','</style>'),'',$code);
            $code = preg_replace_callback('|\s*([^{]*)\s*{\s*([^}]*)(\s*;\s*)?}|', function($match) {
                        return '<span class="css-identifier">' . $match[1] . '<span class="css-bracket">{</span></span><br />' . preg_replace_callback('|(;)?\s*([^:]*):([^;]*)(;)?|', function($match) {
                                            return '<span class="css-key">' . $match[2] . '</span><span class="css-colon">:</span><span class="css-value">' . $match[3] . '</span><span class="css-semicolon">&semi;</span><br />';
                                        }, $match[2]) . '<span class="css-bracket">}</span><br />';
                    }, $code);
            return '<div class="code"><code>' . self::_addLineNumbers($code) . '</code></div>';
        }
        public static function xml($code) {
            return '<div class="code"><code>' . self::_addLineNumbers(nl2br(str_replace(' ','&nbsp;',htmlentities($code)))) . '</code></div>';
        }

        protected static function _addLineNumbers($html) {
            $ex = explode('<br />', $html);
            $ret = '';
            $t = 0;
            $c=count($ex);
            foreach ($ex as $i => $line) {
                if (empty($line))
                    continue;
                $ret.='<div class="numbers">' . (++$t) . '</div>' . $line . '<br/>';
            }
            return $ret;
        }
    }
}