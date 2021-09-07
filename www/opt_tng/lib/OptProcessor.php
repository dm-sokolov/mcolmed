<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 09.08.13
 * Time: 18:41
 * To change this template use File | Settings | File Templates.
 */

class OptProcessor {

    protected $cmd;
    protected $text;

    public function __construct($cmd, $text) {
        $this->cmd = $cmd;
        $this->text = $text;
    }


    protected function h_replace() {
        $matches = array();
        $h_s = array();
        preg_match_all('@<h[1-6]{1}(.*?)</h[1-6]{1}>@si', $this->text, $matches);
        foreach ($matches as $key => $value) {
            $h_s[$key] = $value[0];
        }

        $h_replacements = array();
        for ($i = 0; $i < count($h_s); $i++) {
            if (isset($this->cmd->h[$i])) {
                $old_h = $h_s[$i];
                if (isset($this->cmd->h[$i][1]) && $this->cmd->h[$i][1] != 0 && $this->cmd->h[$i][1] != '0' && $this->cmd->h[$i][1] != '') {
                    $h_s[$i] = preg_replace('@<(h[1-6]{1})(.*?)</(h[1-6]{1})>@si', '<' . $this->cmd->h[$i][1] . '$2' . '</' . $this->cmd->h[$i][1] . '>', $h_s[$i]);
                }
                if (isset($this->cmd->h[$i][2])) {
                    if (strpos($h_s[$i], 'style')) {
                        $h_s[$i] = preg_replace('@(<h[1-6]{1}([^>]*?)style=(\'|"))(.*?)((\'|")(.*?)>)@si', '$1' . base64_decode($this->cmd->h[$i][2]) . '$5', $h_s[$i]);
                    } else {
                        $h_s[$i] = preg_replace('@(<h[1-6]{1}(.*?))(>(.*?)</h[1-6]{1}>)@si', '$1 style="' . base64_decode($this->cmd->h[$i][2]) . '"$3', $h_s[$i]);
                    }
                }

                $h_s[$i] = preg_replace('@(<h[1-6]{1}(.*?)>)(.*?)(</h[1-6]{1}>)@si', '$1' . base64_decode($this->cmd->h[$i][0]) . '$4', $h_s[$i]);

                $h_replacements[$old_h] = $h_s[$i];

            }
        }

        $this->text = my_strtr($this->text, $h_replacements);
    }

    protected function title_replace() {
        
        $title_replacements = array();
        $title = base64_decode($this->cmd->title['1']);
        $title_prefix = base64_decode($this->cmd->title['2']);
        $title_suffix = base64_decode($this->cmd->title['3']);
        $matches = array();
        if (preg_match('@(<title>)(.*?)(</title>)@si', $this->text, $matches)) {
            if (isset($title) && $title != '') {
                $title_replacements[$matches[0]] = $matches[1] . $title . $matches[3];
            } else {
                $title_replacements[$matches[1]] = $matches[1] . $title_prefix;
                $title_replacements[$matches[3]] = $title_suffix . $matches[3];
            }
        } else {
            preg_match('@</head>@si', $this->text, $matches);
            if (isset($title) && $title != '') {
                $title_replacements[$matches[0]] = '<title>' . $title . '</title>' . $matches[0];
            } else {
                $title_replacements[$matches[0]] = '<title>' . $title_prefix . $title_suffix . '</title>' . $matches[0];
            }
        }

        $this->text = my_strtr($this->text, $title_replacements);
    }

    protected function meta_replace() {
        $meta_replacements = array();

        foreach (array('keywords', 'description') as $value) if ($this->cmd->$value) {
            $matches = array();
            $content = $this->cmd->$value;
            $content = base64_decode($content[1]);
            if (preg_match("@<meta([^>]*?)name=('|\")$value('|\")([^>]*?)/?>@si", $this->text, $matches)) {
                $meta_replacements[$matches[0]] = '<meta name="'.$value.'" content="' . $content . '" />';
            } else {
                preg_match('@</head>@si', $this->text, $matches);
                $meta_replacements[$matches[0]] = '<meta name="'.$value.'" content="' . $content . '" />' . $matches[0];
            }
            $this->text = my_strtr($this->text, $meta_replacements);

        }
    }

    protected function txt_replace() {
        $txt_replacements = array();
        $text_block = base64_decode($this->cmd->txt['1']);
        $open_tag = base64_decode($this->cmd->txt['2']);
        $close_tag = base64_decode($this->cmd->txt['3']);
        $match = array();
        if ($open_tag != '' and $close_tag != '') {

            preg_match('/' . preg_quote($open_tag, '/') . '(.*?)' . preg_quote($close_tag, '/') . '/is',$this->text, $match);

            if (isset ($match[0])) if ($match[0] != '') {
                $txt_replacements[$match[0]] = $text_block;
            }

        } elseif ($open_tag != '' and $close_tag == '') {

            preg_match('/' . preg_quote($open_tag, '/') . '/si', $this->text, $match);

            if (isset ($match[0])) if ($match[0] != '') {
                $txt_replacements[$match[0]] = $text_block;
            }

        }
        $this->text = my_strtr($this->text, $txt_replacements);
    }

    protected function hrefs_replace() {
        $hrefs_rewrite = new HrefsRewrite();
        $href_replacements = $hrefs_rewrite->href_replacements();
        $this->text = my_strtr($this->text, $href_replacements);
    }

    protected function metrika_insert() {
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/opt_tng/metrika.txt')) {
            $this->text = str_ireplace('</body>', file_get_contents($_SERVER['DOCUMENT_ROOT'].'/opt_tng/metrika.txt').'</body>', $this->text);
        } else {
            $this->text = str_ireplace('</body>', '<!--Metrika File Does Not Exist--></body>', $this->text);
        }
    }

    protected function meta_insert() {

        $this->text = str_ireplace('</head>', '<meta name="cmsmagazine" content="3360be466cca10e2bd98f686b509e4ba" />'.'</head>', $this->text);

    }



    public function process() {
        
        $matches = array();
        $encoding = false;
        if (preg_match("@<meta([^>]*?)charset=('|\"|)([^>]*?)('|\")([^>]*?)>@si", $this->text, $matches))  {
            $encoding = $matches[3];
            if(strpos($encoding, '1251')) {
                $encoding = false;
            }
        }
        if($encoding) {
            if (function_exists('mb_convert_encoding')) {
                $this->text = @mb_convert_encoding($this->text, 'CP1251', strtoupper(str_ireplace('-', '', $encoding)));
            } else {
                $this->text = @iconv($encoding, 'CP1251//IGNORE', $this->text);
            }
        }
        
        if(function_exists('onBeforeOpt')) {
           
            $this->text = onBeforeOpt($this->text);
        }
        if ($this->cmd->h) $this->h_replace();
        if ($this->cmd->title) $this->title_replace();
        if ($this->cmd->txt) $this->txt_replace();
        $this->meta_replace();
        $this->hrefs_replace();
        $this->metrika_insert();
        $this->meta_insert();
        if($_SERVER['HTTP_HOST'] == 'aimid.ru') {
            $this->text = str_replace('href="http://aimid.ru/detskie-kachalki-loshadki"', 'href="http://aimid.ru/konstruktor.html"', $this->text);
        }
        if($encoding) {
            if (function_exists('mb_convert_encoding')) {
                $this->text = @mb_convert_encoding($this->text, strtoupper(str_ireplace('-', '', $encoding)), 'CP1251');
            } else {
                $this->text = @iconv('cp1251', $encoding, $this->text);
            }
            header('Content-Type:text/html; charset='.$encoding);
        }
        if(function_exists('onAfterOpt')) {
            $this->text = onAfterOpt($this->text);
        }
        return $this->text;
    }


}