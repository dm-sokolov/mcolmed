<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 14.08.13
 * Time: 18:51
 * To change this template use File | Settings | File Templates.
 */

class PreOpt {
    protected $cmd;

    protected $curl_chpu;

    public function __construct($cmd, $curl_chpu) {
        $this->cmd = $cmd;
        $this->curl_chpu = $curl_chpu;
    }

    protected function redirect($to, $rdr_type = 301) {
        if($_SERVER['REMOTE_ADDR'] != get_your_ip()) {
            $rdr_types = array(
                301 => "HTTP/1.1 301 Moved Permanently",
                302 => "HTTP/1.1 302 Moved Temporarily",
                303 => "HTTP/1.1 303 See Other",
                304 => "HTTP/1.1 304 Not Modified",
                305 => "HTTP/1.1 305 Use Proxy",
                307 => "HTTP/1.1 307 Temporary Redirect",
            );
            if(isset($rdr_types[$rdr_type])) {
                header($rdr_types[$rdr_type]);
            } else {
                header("HTTP/1.1 301 Moved Permanently");
            }
            header("Location: http://".$_SERVER['HTTP_HOST'].'/'.$to);
            die();
        }
    }

    protected function curl_chpu($url) {
        $headers = array();

        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        $headers_debug = print_r($headers, true);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $url;
        $ch = curl_init($url);
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
        }
        $cookie_text = '';
        $cookie_array = array();
        foreach($_COOKIE as $key=>$value) {
            $cookie_array[]=$key.'='.$value;
        }
        $cookie_text = implode('; ', $cookie_array);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_text);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($result, 0, $header_size);
        $text = substr($result, $header_size);



        $headers = explode("\n", $headers);
        foreach($headers as $header) {
            $header = str_replace("\r", '', $header);
            if($header != '') {
                foreach(array('HTTP/',
                            'Date:',
                            'Content-Type:',
                            'Expires:',
                            'Cache-Control:',
                            'Pragma:',
                            'Set-Cookie:',
                            "Accept-Charset:",
                            'Location:',
                            ) as $value) if(stripos($header, $value) !== FALSE) {
                    header($header);
                }
            }
        }

        echo $text;
        die;
    }

    public function process() {
        
        if($_SERVER['HTTP_HOST'] != 'aimid.ru') {

            if($this->cmd->chpu) {
              
                $this->redirect(base64_decode($this->cmd->chpu[1]));
            } elseif($this->cmd->thisischpu) {

                $old_url=base64_decode($this->cmd->thisischpu[1]);
                if($this->curl_chpu && function_exists('curl_exec')) {
                    $this->curl_chpu($old_url);
                } else {
                    $_SERVER['REQUEST_URI'] = '/'.$old_url;
                }
            } elseif($this->cmd->rdr) {
                $this->redirect(base64_decode($this->cmd->rdr[1]), $this->cmd->rdr[2]);
            }
        }
        
    }

}