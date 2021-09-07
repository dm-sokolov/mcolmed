<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 25.07.13
 * Time: 13:52
 * To change this template use File | Settings | File Templates.
 */

class PageCmd {

    protected $_url;
    public $_text;
    public $_commands = array();
    protected $_filename = '';
    protected $_cache_path = 'cache/';
    protected $_opt_path = '/opt_tng/';
    protected $_folder_path = '';
    var $_cmd_path = 'http://opt.mediasite.ru/opt_ae/cmd/';

    public function __construct($url, $filename = false) {
        $url=preg_replace('@\?_openstat=[^?&]*@si', '', $url);
        $this->_opt_path = $_SERVER['DOCUMENT_ROOT'].$this->_opt_path;
        if(!file_exists($this->_opt_path.$this->_cache_path) && !is_dir($this->_opt_path.$this->_cache_path)) {
            mkdir($this->_opt_path.$this->_cache_path);
        }
        if($filename) {
            $this->_filename = $filename;
            $folder_name = md5(normalize_domain(preg_replace('@:80$@i', '', $_SERVER['HTTP_HOST']), 1, 1, 1));
            $this->_folder_path = $this->_cmd_path . $folder_name . '/';
        } else {
            $this->_url = $url;
            if($url == '' || $url == '/') $url = '*main*';
            $this->_filename = md5($url);
            $folder_name = md5(normalize_domain(preg_replace('@:80$@i', '', $_SERVER['HTTP_HOST']), 1, 1, 1));
            $this->_folder_path = $this->_cmd_path . $folder_name . '/';
            if ($this->read()) {
                $this->parse();
            }
        }
    }

    public function __get($name) {
        if (isset($this->_commands[$name])) {
            
            return $this->_commands[$name];
        } else {
            return false;
        }
    }

    protected function parse() {
        $content = $this->_text;

        $content = vladson_crypt(base64_decode($content));

        //Бьем его на строки.
        $strings = explode("\r\n", $content);

        //Пробегаемся по строкам...
        foreach ($strings as $i => $string) {

            //Каждую делим по пробелам
            $string_parts = explode(' ', $string);

            $this->_commands[$string_parts[0]][] = array();
            if ($string_parts[0] == 'h') {
                for ($i = 2; $i < count($string_parts); $i++) {
                    $this->_commands[$string_parts[0]][$string_parts[1]][] = $string_parts[$i];
                }
            } else {
                for ($i = 1; $i < count($string_parts); $i++) {
                    $this->_commands[$string_parts[0]][] = $string_parts[$i];
                }
            }
        }

    }

    public function get() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_folder_path . $this->_filename);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $text = curl_exec($ch);

        if ($text != $this->_text) {

            $this->_text = $text;
            $this->parse();
            return 1;
        } else {
            return 0;
        }
    }

    public function set($text) {
        if ($text != $this->_text) {
            $this->_text = $text;
            $this->parse();
            return 1;
        } else {
            return 0;
        }
    }

    public function read() {
        if (file_exists($this->_opt_path . $this->_cache_path . $this->_filename)) {
            $this->_text = file_get_contents($this->_opt_path . $this->_cache_path . $this->_filename);
            return true;
        } else {
            return false;
        }
    }

    public function save() {
        if($this->chpu) {
            $hrefs_rewrite = new HrefsRewrite();
            if($this->_url) {
                $hrefs_rewrite->add($this->_url, base64_decode($this->chpu[1]));
            } else {
                $hrefs_rewrite->add($this->_filename, base64_decode($this->chpu[1]));
            }
            $hrefs_rewrite->save();
        }
        if($this->thisischpu) {
            $hrefs_rewrite = new HrefsRewrite();
            if(isset($hrefs_rewrite->hrefs_rewrite[md5(base64_decode($this->thisischpu[1]))])) {
                $hrefs_rewrite->hrefs_rewrite[base64_decode($this->thisischpu[1])] = $hrefs_rewrite->hrefs_rewrite[md5(base64_decode($this->thisischpu[1]))];
            }
            $hrefs_rewrite->save();
        }
        if ($this->_text != '') {
            if($this->_text == 'deleteme') {
                unlink($this->_opt_path . $this->_cache_path . $this->_filename);
            } else {
                file_put_contents($this->_opt_path . $this->_cache_path . $this->_filename, $this->_text);
            }
        }
    }

}