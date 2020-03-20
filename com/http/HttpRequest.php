<?php

class HttpRequest
{

    var $Handle; //Socket"句柄"


    var $Request; //用于保存HTTP请求字符串


    var $CRLF = "\r\n"; //行结束标示


    var $Version = '1.1'; //HTTP版本


    var $ProxyHost; //代理服务器的IP


    var $ProxyPort; //代理服务器的端口


    var $ProxyUser; //代理服务器的用户名


    var $ProxyPass; //代理服务器的用户密码


    var $Server; //用于保存web服务器的IP


    var $Port = 80; //web服务器的端口


    var $Document; //用于保存HTTP请求的文档


    var $Header = array('Accept' => "*/*", 'Accept-Language' => "zh-cn,zh", 'Accept-Encoding' => "gzip, deflate", 'User-Agent' => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2)", 'Pragma' => "no-cache", 'Cache-Control' => "no-cache", 'Host' => "", 'Referer' => "", 'Cookie' => "", 'Connection' => "Close"); //用于保存http的头请求


    var $Response; //用于保存服务器的返回状态


    /**
     *
     * URL解码函数
     * 把URL中一些中文之类的字符转换为服务器能识别的URL
     */

    public function decode_url($URL, $Method = 'get')
    {
        $Info = parse_url($URL);
        $Method = strtoupper($Method);
        empty ($Info ['scheme']) ? $Info ['scheme'] = 'http' : null;
        if (strtolower($Info ['scheme']) != 'http') {
            return false;
        }
        $this->Header ['Host'] = empty ($Info ['host']) ? false : $Info ['host'];
        $this->Server = gethostbyname($this->Header ['Host']);
        if (ip2long($this->Server) == -1 || ip2long($this->Server) == false) {
            return false;
        }
        $this->Port = isset($Info['port']) && is_numeric($Info['port']) ? intval($Info ['port']) : (empty ($Info['port']) ? 80 : false);

        if (empty ($Info ['path']) || $Info ['path'] == '/') {
            return $this->Document = '/';
        } else {
            $Tmp = explode('/', $Info ['path']);
            array_shift($Tmp);
            if (count($Tmp) == 1 && empty ($Info ['query'])) {
                return $this->Document = '/' . ($Method == 'POST' ? urlencode($Tmp [0]) : rawurlencode($Tmp [0]));
            }
            foreach ($Tmp as $Str) {
                $T [] = $Method == 'POST' ? urlencode($Str) : rawurlencode($Str);
            }
            $Info ['path'] = '/' . implode('/', $T);
            if (!empty ($Info ['query'])) {
                $Info ['query'] = str_replace(array('&', '&'), '&', $Info ['query']);
                $Tmp = explode('&', $Info ['query']);
                foreach ($Tmp as $Query) {
                    $Str = explode('=', $Query);
                    if (preg_match('/%[A-F0-9][A-F0-9]/', $Str [1])) {
                        $Q [] = ($Method == 'POST' ? urlencode($Str [0]) : rawurlencode($Str [0])) . '=' . $Str [1];
                    } else {
                        $Q [] = ($Method == 'POST' ? urlencode($Str [0]) : rawurlencode($Str [0])) . '=' . ($Method == 'POST' ? urlencode($Str [1]) : rawurlencode($Str [1]));
                    }
                }
                $Info ['query'] = implode('&', $Q);
                return $this->Document = $Info ['path'] . '?' . $Info ['query'];
            } else {
                return $this->Document = $Info ['path'];
            }
        }
    }

    function connect($Server, $Port, $Timeout = 8)
    {
        $this->Handle = (!empty ($this->ProxyHost) && !empty ($this->ProxyPort)) ? fsockopen($this->ProxyHost, $this->ProxyPort, $ErrorNo, $ErrorStr, $Timeout) : fsockopen($Server, $Port, $ErrorNo, $ErrorStr, $Timeout);
        if (!$this->Handle) {
            return false;
        }
        return $this->Handle;
    }

    function add_head($Name, $Value)
    {
        return $this->Header [$Name] = $Value;
    }

    function make_head($Method)
    {
        $Method = strtoupper($Method);
        $AllowMethod = array('GET', 'HEAD', 'POST');

        in_array($Method, $AllowMethod) ? null : exit ('No Allow HTTP Method');
        $this->Request = (!empty ($this->ProxyHost) && !empty ($this->ProxyPort)) ? "{$Method} http://{$this->Header['Host']}{$this->Document} HTTP/{$this->Version}{$this->CRLF}" : "$Method $this->Document HTTP/{$this->Version}{$this->CRLF}";
        foreach ($this->Header as $Key => $Var) {
            empty ($Var) ? null : $this->Request .= "$Key: $Var{$this->CRLF}";
        }
        if ($Method == 'GET' || $Method == 'HEAD') {
            $this->Request .= $this->CRLF;
        }
    }

    function decode_head($Header)
    {
        $Regexp = "/HTTP\/1\.[01] ([0-9]+) [a-z]+/i";
        if (preg_match($Regexp, $Header, $Tmp))
            $this->Response ['status'] = trim($Tmp [1]);
        $Regexp = "/location:([^\n]+)\n/i";

        if (preg_match($Regexp, $Header, $Tmp))
            $this->Response ['redirect'] = trim($Tmp [1]);

        $Regexp = "/Transfer-Encoding:([^\n]+)\n/i";

        if (preg_match($Regexp, $Header, $Tmp))
            $this->Response ['TransferEncoding'] = strtoupper(trim($Tmp [1]));

        $Regexp = "/Content-Encoding:([^\n]+)\n/i";

        if (preg_match($Regexp, $Header, $Tmp))
            $this->Response ['ContentEncoding'] = strtoupper(trim($Tmp [1]));
        else
            $this->Response ['ContentEncoding'] = null;

        $Regexp = "/Set-Cookie:((?:[^=]+)=(?:[^\n;]+)).*/i";

        if (preg_match_all($Regexp, $Header, $Tmp)) {
            foreach ($Tmp [1] as $Var) {

                $Var = trim($Var);

                $this->Header ['Cookie'] .= "$Var;";

            }
        }

        return true;

    }

    function decode_body($String, $EOF = "\r\n")
    {
        if (!isset ($this->Response ['TransferEncoding'])) {
            return $String;
        } elseif (strtoupper($this->Response ['TransferEncoding']) == 'CHUNKED') {
            $Return = null;
            $EndLength = strlen($EOF);
            do {
                $String = ltrim($String);
                $StartPos = strpos($String, $EOF);
                $Length = hexdec(substr($String, 0, $StartPos));
                if ($this->Response ['ContentEncoding'] == 'DEFLATE' || $this->Response ['ContentEncoding'] == 'GZIP') {
                    $Return .= gzinflate(substr($String, ($StartPos + $EndLength + 10), $Length));
                } else {
                    $Return .= substr($String, ($StartPos + $EndLength), $Length);
                }
                $String = substr($String, ($Length + $StartPos + $EndLength));
                $End = trim($String);
            } while (!empty ($End));
            return $Return;
        } elseif (strtoupper($this->Response ['ContentEncoding']) == 'GZIP' || strtoupper($this->Response ['ContentEncoding']) == 'DEFLATE') {
            return gzinflate(substr($String, 10));
        } else {
            return $String;
        }

    }

    function process($Data = null)
    {

        if (!$this->Handle) {

            return false;

        }

        fwrite($this->Handle, $this->Request, strlen($this->Request));

        //#http协议说了第一个CRLF前的部分为HTTP头,后面的才是"内容"
        $Response = '';
        do {

            $Response .= fgets($this->Handle, 512);

        } while (strpos($Response, "\r\n\r\n") === false);

        #//http协议说了第一个CRLF前的部分为HTTP头,后面的才是"内容"


        $this->decode_head($Response);
        $Response = null;
        while (!feof($this->Handle)) {
            $Response .= fgets($this->Handle, 1024);
        }
        fclose($this->Handle);
        return $this->decode_body($Response);
    }

    function get($URL, $data = null)
    {
        if ($data !== null) {
            if (strpos($URL, '?')) {
                $URL .= http_build_query($data);
            } else {
                $URL .= "?" . http_build_query($data);
            }
        }

        if ($this->decode_url($URL, 'GET') == false) {
            return false;
        }
        if (empty ($this->Header ['Referer'])) {
            $this->Header ['Referer'] = "http://{$this->Header['Host']}{$this->Document}";
        }
        if ($this->connect($this->Server, $this->Port) == false) {
            return false;
        }
        $this->make_head('get');
        return $this->process();

    }

    function head($URL)
    {
        if ($this->decode_url($URL, 'HEAD') == false) {
            return false;
        }
        if ($this->connect($this->Server, $this->Port) == false) {
            return false;
        }
        $this->make_head('HEAD');
        $this->process();
        return $this->Response ['status'] == 200 ? true : false;
    }

    function post($URL, $Data, $File = null)
    {
        if (!is_array($Data)) {
            throw new MException ('The Data Must Be Array');
        }
        if (!empty ($File)) {
            is_array($File) ? $Boundary = md5(time()) : exit ('The File Must Be Array');
        }

        foreach ($Data as $Key => $Var) {

            if (isset ($Boundary)) {

                $Tmp [] = "--$Boundary{$this->CRLF}Content-Disposition: form-data; name=\"$Key\"{$this->CRLF}{$this->CRLF}$Var";

            } else {

                //$Tmp[] = "$Key=" . urlencode($Var);


            }

        }

        if ($this->decode_url($URL, 'POST') == false) {

            return false;

        }

        unset ($this->Header ['Connection']);

        if (empty ($this->Header ['Referer'])) {
            $this->Header ['Referer'] = "http://{$this->Header['Host']}{$this->Document}";
        }

        if (isset ($Boundary)) {
            $this->Version = '1.0'; //注意不要更改,如果HTTP版本为1.1需要的时间是1.0的10倍以上!!我也不知道为啥...达牛指点下?

            foreach ($File as $Key => $Var) {
                if (!file_exists($Var)) {
                    exit ('POST File Was No Exits');
                }
                $Temp [] = "--$Boundary{$this->CRLF}Content-Disposition: form-data; name=\"$Key\"; filename=\"" . basename($Var) . "\"{$this->CRLF}" . "Content-Type: unknow{$this->CRLF}" . "Content-Transfer-Encoding:binary{$this->CRLF}{$this->CRLF}" . file_get_contents($Var);
            }
            $D = implode($this->CRLF, $Tmp);
            $F = implode($this->CRLF, $Temp);
            $Data = $D . $this->CRLF . $F . "{$this->CRLF}--$Boundary--{$this->CRLF}{$this->CRLF}";
            $this->Header ['Content-Type'] = 'multipart/form-data, boundary=' . $Boundary;
            $this->Header ['Content-Length'] = strlen($Data) . $this->CRLF;
            $this->make_head('POST');
            $this->Request .= $Data;
        } else {
            //$Data = implode('&', $Tmp);
            $Data = http_build_query($Data);
            $this->Header ['Content-Type'] = 'application/x-www-form-urlencoded';
            $this->Header ['Content-Length'] = strlen($Data);
            $this->Header ['Connection'] = 'Close';
            $this->make_head('POST');
            $this->Request .= $this->CRLF . $Data;
        }

        if ($this->connect($this->Server, $this->Port) == false) {
            return false;
        }
        return $this->process();
    }

}

