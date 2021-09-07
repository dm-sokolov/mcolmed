<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 15.08.13
 * Time: 14:26
 * To change this template use File | Settings | File Templates.
 */

class HrefsRewrite {

    protected $_cache_path = 'cache/';
    protected $_opt_path = '/opt_tng/';
    protected $file_path;
    public $hrefs_rewrite;

    public function __construct() {
        $this->file_path = $_SERVER['DOCUMENT_ROOT'].$this->_opt_path.$this->_cache_path.'hrefs_rewrite';
        if(file_exists($this->file_path)) {
            $this->hrefs_rewrite = unserialize(file_get_contents($this->file_path));
        } else {
            $this->hrefs_rewrite = array();
        }
    }

    public function add($from, $to) {
        $this->hrefs_rewrite[$from] = $to;
    }

    public function clear() {
        unlink($this->file_path);
    }

    public function href_replacements() {
        $href_replacements = array();
        foreach($this->hrefs_rewrite as $from=>$to) {
            $a=array('"', "'");
            $prefixs=array('http://' . $_SERVER['HTTP_HOST'].'/', '/', '');
            foreach(array(true, false) as $trim) {
                foreach($a as $value) {
                    foreach($prefixs as $prefix) {
                        $href_replacements['href='.$value.$prefix.($trim ? rtrim($from, '/') : $from).$value] = 'href="http://' . $_SERVER['HTTP_HOST'] . '/' .  $to.'"';
                    }
                }
            }
        }
        return $href_replacements;
    }

    public function save() {
        file_put_contents($this->file_path, serialize($this->hrefs_rewrite));
    }
}