<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use CUrl\CUrlFetcher;

class CUrlTest extends TestCase
{
    public function testConstruct()
    {
        $fetcher = new CUrlFetcher();
        $this->assertNotNull($fetcher);

        return $fetcher;
    }

    public function testConstructWithParams()
    {
        $fetcher = new CUrlFetcher(true, array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAPATH => '/etc/ssl/certs/',
        ));
        $this->assertNotNull($fetcher);

        return $fetcher;
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetOnce($fetcher)
    {
        $content = $fetcher->get('http://www.qq.com');
        $this->assertNotNull($content);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetBaidu($fetcher)
    {
        $content = $fetcher->get('https://www.baidu.com', array(
            'wd' => 'XhinLiang',
        ));
        $this->assertNotNull($content);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetMulti($fetcher)
    {
        $queryArrayArray = array();
        for ($i = 1; $i < 2; $i += 1) {
            $queryArrayArray[] = array(
                'query' => 'XhinLiang',
                'page' => $i,
            );
        }
        $contentArray = $fetcher->getMulti('https://www.sogou.com/', $queryArrayArray);
        foreach ($contentArray as $content) {
            $this->assertNotNull($content);
        }
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetMultiBaidu($fetcher)
    {
        $queryArrayArray = array();
        for ($i = 1; $i < 2; $i += 1) {
            $queryArrayArray[] = array(
                'wd' => 'XhinLiang',
                'pn' => $i * 10,
            );
        }
        $contentArray = $fetcher->getMulti('https://www.baidu.com/', $queryArrayArray);
        foreach ($contentArray as $content) {
            $this->assertNotNull($content);
        }
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetCookie($fetcher)
    {
        $cookie = $fetcher->getCookie('https://www.baidu.com');
        $this->assertNotNull($cookie);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetCookieBing($fetcher)
    {
        $cookie = $fetcher->getCookie('http://www.bing.com');
        $this->assertNotNull($cookie);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testPostFile($fetcher)
    {
        $cfile = @realpath('/home/xhinliang/ef.ef');
        $params = array(
            'name' => 'efe',
            'id' => 121212,
            'position' => 'Web 前端',
            'phone' => '13222222222',
            'email' => 'xxxx@qq.vv',
            'description' => 'fewf',
            'file1' => $cfile
        );
        $postResult = $fetcher->post('http://studiobackend.xhinliang.com/api/submitResume', $params);
        $this->assertNotNull($postResult);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testPost($fetcher)
    {
        $params = array(
            'name' => 'efe',
            'id' => 121212,
            'position' => 'Web 前端',
            'phone' => '13222222222',
            'email' => 'xxxx@qq.vv',
            'description' => 'fewf',
        );
        $postResult = $fetcher->post('http://studiobackend.xhinliang.com/api/submitResume', $params);
        $this->assertNotNull($postResult);
    }

    /**
     * @depends testConstructWithParams
     */
    public function testPostError($fetcher)
    {
        $params = array(
            'name' => 'efe',
            'id' => 121212,
            'position' => 'Web 前端',
            'phone' => '13222222222',
            'email' => 'xxxx@qq.vv',
            'description' => 'fewf',
        );
        try{
            $postResult = $fetcher->post('http://studiobackend.xhinliang.com/xxxxxapi', $params);
        } catch(\Exception $e) {
            $this->assertEquals($e->getCode(), 403);
        }
    }

    /**
     * @depends testConstructWithParams
     */
    public function testGetError($fetcher)
    {
        try{
            $result = $fetcher->get('http://studiobackend.xhinliang.com/xxxxxapi');
        } catch(\Exception $e) {
            $this->assertEquals($e->getCode(), 403);
        }
    }
}
