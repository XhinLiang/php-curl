# PHP-cURL

### CUrlFetcher

#### constructor
```
// options
$fetcher = new CUrlFetcher(true, array(
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_CAPATH => '/etc/ssl/certs/',
));

// default
$fetcher = new CUrlFetcher();
```

#### GET
```
$content = $fetcher->get('http://www.qq.com');
```

#### GET with multi threads
```
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
```
#### POST
```
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
```

### MultiDownload
```
$downloader = new MultiDownload();
$urls = array(
    "http://resume.xhinliang.com/index.html"
);
$downloader->setSavePath('/tmp/');
$downloader->setURLs($urls);
$downloader->download();
```
