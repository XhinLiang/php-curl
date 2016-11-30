<?php

namespace CUrl;

use \Exception;

/**
 * cURL 的封装类
 *
 * ## Simple Single Sample
 *
 * $fetcher = new CUrlFetcher();
 * $result = $fetcher->fetch('www.qq.com', array('queryKey' => 'queryValue'));
 *
 * @author xhinliang
 */

class CUrlFetcher
{
    const RETRY_TIME = 3;

    /**
     * 预置的随机 ua
     */
    private $randomAgents = array(
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36 OPR/38.0.2220.29',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.12 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/600.8.9 (KHTML, like Gecko) Version/9.1.1 Safari/601.6.17',
    );

    /**
     * 默认的 cURL 参数
     */
    private $defaultOptions = array(
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSLVERSION => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        //CURLOPT_SSL_VERIFYPEER => true,
        //CURLOPT_CAPATH => '/etc/ssl/certs/ca-bundle.crt',
    );

    /**
     * 默认 HTTP 头
     */
    private $defaultHeaders = array(
        'Connection: keep-alive',
        'Cache-Control:max-age=0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: zh-CN,zh;q=0.8',
        'Upgrade-Insecure-Requests: 1',
    );

    /**
     * @var bool 是否使用随机的 UA
     */
    private $useRandomAgent = true;

    /**
     * CUrlGet constructor.
     *
     * @author xhinliang
     * @param bool $useRandomAgent
     * @param array $options
     */
    public function __construct($useRandomAgent = true, $options = array())
    {
        $this->useRandomAgent = $useRandomAgent;
        $this->options = $options;
    }

    /**
     * 在预置的随机 UA 中选择一个
     *
     * @author xhinliang
     * @return string
     */
    private function getRandomAgent()
    {
        $roll = rand(0, count($this->randomAgents) - 1);
        return $this->randomAgents[$roll];
    }


    /**
     * 发起 GET 请求，并返回
     *
     * @param string $url
     * @param $queryArray
     * @param array $headers
     * @param array $options
     * @return string cUrl result
     * @throws Exception
     */
    public function get($url, $queryArray = array(), $headers = array(), $options = array())
    {
        $headers = array_merge($this->defaultHeaders, $headers);
        # 初始化会话
        $ch = curl_init();
        # 设置 ua
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useRandomAgent ? $this->getRandomAgent() : $this->randomAgents[0]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt_array($ch, $this->defaultOptions);
        curl_setopt_array($ch, $this->options);
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_URL, $this->buildUrl($url, $queryArray));

        $times = 0;
        while ($times++ < self::RETRY_TIME) {
            $response = curl_exec($ch);
            if (!curl_errno($ch)) {
                curl_close($ch);
                return $response;
            }
        }
        $e = new Exception($message = curl_error($ch), $code = curl_errno($ch));
        curl_close($ch);
        throw  $e;
    }

    /**
     * 用多线程获取相应的数据。
     * TODO 出现错误时抛出对应的错误码（5.3.10版本现在没有找到解决办法）
     *
     * @param string $url 查找的 url
     * @param array $queryArrayArray 包含 array 的 array，其中每个子 array 都包含了 query 信息
     * @param array $headers 对于所有请求的 HTTP 头配置
     * @param array $optionsArray 对于这次多线程抓取，对每个句柄单独设置 CURLOPT，通常使用在每个句柄单独设置 COOKIE的情况
     *                            这个array 的长度必须跟queryArrayArray一致！！！！
     * @return array 没有 key，$queryArrayArray 中含有几个元素，则返回的数组中就有多少个元素
     * @throws \Exception 访问错误时抛出的错误，目前无法正确处理错误码
     *
     * @author xhinliang
     */
    public function getMulti($url, $queryArrayArray, $headers = array(), $optionsArray = null)
    {
        $result = array();
        $headers = array_merge($this->defaultHeaders, $headers);
        $chArray = array();
        $i = 0;
        foreach ($queryArrayArray as $queryArray) {
            # 初始化会话
            $ch = curl_init();
            # 设置 ua
            curl_setopt($ch, CURLOPT_USERAGENT, $this->useRandomAgent ? $this->getRandomAgent() : $this->randomAgents[0]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt_array($ch, $this->defaultOptions);
            curl_setopt_array($ch, $this->options);
            if ($optionsArray)
                curl_setopt_array($ch, $optionsArray[$i]);
            curl_setopt($ch, CURLOPT_URL, $this->buildUrl($url, $queryArray));
            $chArray[] = $ch;
            $i++;
        }
        $mh = curl_multi_init();
        foreach ($chArray as $ch) {
            curl_multi_add_handle($mh, $ch);
        }
        $running = null;
        do {

            # 解决 CPU 占用率过高的问题 @from php.net
            curl_multi_select($mh);

            curl_multi_exec($mh, $running);
        } while ($running > 0);

        # remove all the curl_handles.
        foreach ($chArray as $ch) {
            if (curl_multi_getcontent($ch))
                $result[] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);
        if (count($result) === count($queryArrayArray)) {
            return $result;
        }
        throw new Exception('unknown error', -1);
    }



    /**
     * 重新构建url
     * @from capp.webdev.com
     *
     * @param $url
     * @param array $queryArray
     * @return string
     */
    private function buildUrl($url, $queryArray = array())
    {
        $parseUrl = parse_url($url);
        isset($parseUrl['query']) ? parse_str($parseUrl['query'], $urlQuery) : $urlQuery = array();
        $params = array_merge($urlQuery, $queryArray);
        $parseUrl['query'] = (!empty($params)) ? '?' . http_build_query($params) : '';
        if (!isset($parseUrl['path']))
            $parseUrl['path'] = '/';
        $port = '';
        if (isset($parseUrl['port'])) {
            $port = ':' . $parseUrl['port'];
        }
        $url = $parseUrl['scheme'] . '://' . $parseUrl['host'] . $port . $parseUrl['path'] . $parseUrl['query'];
        return $url;
    }

    /**
     * 对一个 url 获取其提供的 Set-Cookies
     *
     * @param string $url URL
     * @return string cookie
     * @throws Exception
     *
     * @author xhinliang
     */
    public function getCookie($url)
    {
        $ch = curl_init($url); //初始化
        curl_setopt_array($ch, $this->defaultOptions);
        curl_setopt_array($ch, $this->options);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $times = 0;
        while ($times++ < self::RETRY_TIME) {
            $response = curl_exec($ch);
            if (!curl_errno($ch)) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                list($header, $body) = explode("\r\n\r\n", $response);
                preg_match_all('/Set-Cookie: ([^;]*);/', $header, $match);
                $cookie = '';
                foreach ($match[1] as $item) {
                    $cookie = $cookie . '; ' . $item;
                }
                $cookie = ltrim($cookie, ';');
                $cookie = ltrim($cookie);
                curl_close($ch);
                return $cookie;
            }
        }
        $e = new Exception($message = '获取新的cookie失败 message: ' . curl_error($ch), $code = curl_errno($ch));
        curl_close($ch);
        throw  $e;
    }

    /**
     * 发起一个 POST 请求
     *
     * ****************** sample **********************
     *
     *   // 准备好 POST 需要的参数
     *   if (function_exists('curl_file_create')) { // php 5.6+
     *       $cFile = curl_file_create($filePath);
     *   } else {
     *       $cFile = '@' . realpath($filePath);
     *   }
     *   $params = array(
     *       'param1' => 'hehe',
     *       'file' => $cFile,
     *   );
     *   $fetcher = new CUrlFetcher();
     *   $response = $fetcher->post(self::UPLOAD_URL, $params);
     *
     * ****************** sample **********************
     *
     * @author xhinliang
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function post($url, $params = array(), $headers =array() , $options = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useRandomAgent ? $this->getRandomAgent() : $this->randomAgents[0]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt_array($ch, $this->options);
        curl_setopt_array($ch, $options);
        $times = 0;
        while ($times++ < self::RETRY_TIME) {
            $response = curl_exec($ch);
            if (!curl_errno($ch)) {
                curl_close($ch);
                return $response;
            }
        }
        $e = new Exception($message = 'POST 内容失败： ' . curl_error($ch), $code = curl_errno($ch));
        curl_close($ch);
        throw  $e;
    }

}
