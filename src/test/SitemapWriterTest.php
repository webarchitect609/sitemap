<?php

namespace WebArch\Sitemap\Test;

use DateTimeImmutable;
use RuntimeException;
use UnexpectedValueException;
use WebArch\Sitemap\Enum\ChangeFreq;
use WebArch\Sitemap\Exception\DeployException;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Sitemap;
use WebArch\Sitemap\Model\Url;

class SitemapWriterTest extends TestBase
{
    /**
     * @var string
     */
    protected $expectedSitemapIndexXml = '';

    /**
     * @var string
     */
    protected $expectedSitemapDefaultXml = '';

    /**
     * @var Sitemap
     */
    protected $sitemapDefault;

    /**
     * @var string
     */
    protected $expectedSecondSitemapXml = '';

    /**
     * @var Sitemap
     */
    protected $secondSitemap;

    /**
     * @var string
     */
    protected $expectedEmptySitemapXml = '';

    /**
     * @var Sitemap
     */
    protected $emptySitemap;

    /**
     * @throws UnexpectedValueException
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     * @throws RuntimeException
     * @throws DeployException
     */
    protected function setUp()
    {
        $this->sitemapWriter = $this->getDummySitemapWriter();

        $this->sitemapDefault = $this->sitemapWriter->getSitemapIndex()->first();

        $this->expectedSitemapIndexXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>http://example.org/sitemap_default.xml</loc>
    <lastmod>2015-03-08T00:00:00+0000</lastmod>
  </sitemap>
  <sitemap>
    <loc>http://example.org/sitemap_second.xml</loc>
    <lastmod>2018-07-11T23:50:49+0000</lastmod>
  </sitemap>
  <sitemap>
    <loc>http://example.org/sitemap_empty.xml</loc>
  </sitemap>
</sitemapindex>

END;

        $this->expectedSitemapDefaultXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://example.org/foo/bar</loc>
  </url>
  <url>
    <loc>http://example.org/baz/wer</loc>
    <changefreq>hourly</changefreq>
  </url>
  <url>
    <loc>http://example.org/baz/qwer</loc>
    <changefreq>weekly</changefreq>
    <priority>0.2</priority>
  </url>
  <url>
    <loc>http://example.org/xcvx/sswer</loc>
    <lastmod>2015-03-08T00:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>http://example.org/xcvx/sswer</loc>
    <lastmod>2014-01-08T00:00:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>

END;

        $this->sitemapWriter->addUrl((new Url())->withLoc('/foo/bar'))
                            ->addUrl(
                                (new Url())->withLoc('/baz/wer')
                                           ->withChangefreq(ChangeFreq::CHANGE_FREQ_HOURLY)
                            )
                            ->addUrl(
                                (new Url())->withLoc('/baz/qwer')
                                           ->withChangefreq(ChangeFreq::CHANGE_FREQ_WEEKLY)
                                           ->withPriority(0.2)
                            )
                            ->addUrl(
                                (new Url())->withLoc('/xcvx/sswer')
                                           ->withChangefreq(ChangeFreq::CHANGE_FREQ_WEEKLY)
                                           ->withPriority(0.5)
                                           ->withLastmod(
                                               DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2015-03-08 00:00:00')
                                           )
                            )
                            ->addUrl(
                                (new Url())->withLoc('/xcvx/sswer')
                                           ->withChangefreq(ChangeFreq::CHANGE_FREQ_WEEKLY)
                                           ->withPriority(1.0)
                                           ->withLastmod(
                                               DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2014-01-08 00:00:00')
                                           )
                            )
            /**
             * To test respect of robots.txt
             */
                            ->addUrl(new Url('/admin/index.php'))
                            ->addUrl(new Url('/catalog/search/index.html'));

        $this->expectedSecondSitemapXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://example.org/index.php</loc>
  </url>
</urlset>

END;

        $this->secondSitemap = new Sitemap('/sitemap_second.xml');
        $this->secondSitemap->withLastmod(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-07-11 23:50:49'));
        $this->secondSitemap->addUrl((new Url('/index.php')), $this->sitemapWriter->getDomain());

        /**
         * To test respect of robots.txt
         */
        $url = (new Url('/news/index.shtml'))->withChangefreq(ChangeFreq::CHANGE_FREQ_WEEKLY);
        $this->secondSitemap->addUrl($url, $this->sitemapWriter->getDomain());
        $this->sitemapWriter->addSitemap($this->secondSitemap);

        $this->expectedEmptySitemapXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>

END;

        $this->emptySitemap = new Sitemap('/sitemap_empty.xml');
        $this->sitemapWriter->addSitemap($this->emptySitemap);

        $copyRes = copy(
            __DIR__ . '/../../resources/robots.txt',
            $this->sitemapWriter->getBaseDir() . $this->sitemapWriter->getRobotsTxt()
        );

        if (false == $copyRes) {
            throw new RuntimeException('Error copying robots.txt');
        }

        $this->sitemapWriter->write();
    }

    public function testWrite()
    {
        /**
         * Использование DataProvider невозможно
         */
        $assertList = [
            [
                $this->expectedSitemapDefaultXml,
                $this->sitemapWriter->getBaseDir() . $this->sitemapDefault->getFilename(),
            ],
            [
                $this->expectedSecondSitemapXml,
                $this->sitemapWriter->getBaseDir() . $this->secondSitemap->getFilename(),
            ],
            [
                $this->expectedEmptySitemapXml,
                $this->sitemapWriter->getBaseDir() . $this->emptySitemap->getFilename(),
            ],
            [
                $this->expectedSitemapIndexXml,
                $this->sitemapWriter->getBaseDir() . $this->sitemapWriter->getSitemapIndex()->getFilename(),
            ],

        ];

        foreach ($assertList as $assertion) {

            $this->assertEquals($assertion[0], file_get_contents($assertion[1]), $assertion[1]);
        }

    }

}
