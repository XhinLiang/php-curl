# PHP-cURL

### Install
```
composer require xhinliang/php-curl
```

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

## Build

```
composer install
```


## Test
```
./runtest
```

## Licence

```
Copyright (c) 2016 Xhin Liang

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```
