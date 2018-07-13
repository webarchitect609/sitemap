Sitemap generation and deploy tool
==================================

**Be careful**: this version can be unstable and all interfaces can be changed in near future. 

Features
--------

- Respect of [Sitemaps XML format](https://www.sitemaps.org/protocol.html) and it's limits;
- Sitemapindex is always supported;
- Automatic setup 'lastmod' for sitemap in sitemapindex with the most fresh 'lastmod' from it's urls;
- Automatic adding hostname for urls; 
- Respect of [robots.txt](https://developers.google.com/search/reference/robots_txt) `Disallow` rules for '*' agent to help you avoid undesired urls in your sitemap;
- Write to a temporary folder and deploy to final destination to avoid damaging old copy in a case of troubles;
- No dependencies from any framework;

How to use
----------

1 Install via [composer](https://getcomposer.org/)

`composer require webarchitect609/sitemap`

2 Create writer instance

`$sitemapWriter = new \WebArch\Sitemap\SitemapWriter('http://example.org', '/var/www/example.org/htdocs');`

3 For simple usage just start to add urls directly to writer

```
use WebArch\Sitemap\Enum\ChangeFreq;
use WebArch\Sitemap\Model\Url;

$url1 = (new Url('/index.php'))->withChangefreq(ChangeFreq::CHANGE_FREQ_DAILY)
                               ->withPriority(0.9)
                               ->withLastmod(
                                   DateTimeImmutable::createFromFormat(
                                       'Y-m-d H:i:s',
                                       '2018-07-13 10:37:48'
                                   )
                               );

$url2 = (new Url('/news/index.php'))->withChangefreq(ChangeFreq::CHANGE_FREQ_HOURLY);

$sitemapWriter->addUrl($url1)
              ->addUrl($url2);

```

4 For more complicated case you can create as many sitemaps as you need and give them to writer: 

```
$newsSitemap = new \WebArch\Sitemap\Model\Sitemap('/sitemap_news.xml');

$newsSitemap->addUrl(
                (new Url('/news/detail/1/'))
            )
            ->addUrl(
                (new Url('/news/detail/2/'))
            )
            ->addUrl(
                (new Url('/news/detail/3/'))
            );

$sitemapWriter->addSitemap($newsSitemap);

```

5 Additional options can be applied to respect sitemap limitations

```
/**
 * Limit maximum urls count. When it's overflowed an `\WebArch\Sitemap\Exception\UrlCountLimitException` would be issued.
 * It's ON by default.
 */
$newsSitemap->getUrlSet()->withMaxUrlCount(50000);

/**
 * Limit maximum file size
 * It's OFF by default.
 * WARNING: it WILL slow down everything: after adding new url estimated size calculations would be executed.
 * (Hope to get rid of this in the future)
 */
$newsSitemap->getUrlSet()->withMaxXmlSizeBytes(10*1024*1024);

```

6 And then just let it work 

```
$sitemapWriter->write();
```

After this everything will be written to `sys_get_temp_dir()`. If there were no errors new version of sitemapindex + all 
sitemaps will be deployed to it's final destination at `$sitemapWriter->getBaseDir()` and file permissions will be 
changed in a way to let everybody read them. 


Running Unit-tests
------------------
`composer test`

