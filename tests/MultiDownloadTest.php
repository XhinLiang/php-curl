<?php

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use CUrl\MultiDownload;

class MultiDownloadTest extends TestCase
{
    public function testConstruct()
    {
        $downloader = new MultiDownload();
        $this->assertNotNull($downloader);
        return $downloader;
    }

    /**
     * @depends testConstruct
     */
    public function testDownload($downloader)
    {
        $urls = array(
            "http://resume.xhinliang.com/index.html"
        );
        $downloader->setSavePath('/tmp/');
        $downloader->setURLs($urls);
        $downloader->download();
    }
}
