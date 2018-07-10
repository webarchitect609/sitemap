<?php

namespace WebArch\Sitemap\Test\Model;

use PHPUnit\Framework\Exception;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Url;
use WebArch\Sitemap\Test\TestBase;

/**
 * Class UrlSetTest
 * @package WebArch\Sitemap\Test\Model
 *
 *
 */
class UrlSetTest extends TestBase
{
    /**
     * @var string
     */
    protected $expectedXml;

    /**
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    protected function setUp()
    {
        /** @noinspection HtmlUnknownTag */
        $this->expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>/some/url/location/goes/here</loc>
    <lastmod>2018-02-28T12:58:36+00:00</lastmod>
    <changefreq>always</changefreq>
    <priority>0.0</priority>
  </url>
  <url>
    <loc>/</loc>
    <changefreq>never</changefreq>
    <priority>0.0</priority>
  </url>
  <url>
    <loc>/FOO</loc>
    <priority>4.0</priority>
  </url>
  <url>
    <loc>/bar?sdf=234</loc>
  </url>
</urlset>

END;

        $this->urlSet = $this->getUrlSet();
    }

    public function testXmlEstimatedSize()
    {
        self::assertEquals(strlen($this->expectedXml), $this->urlSet->getXmlEstimatedSize());
    }

    /**
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     * @throws Exception
     */
    public function testUrlCountLimit()
    {
        $limit = 123;

        $this->urlSet->withMaxUrlCount($limit)
                     ->withMaxXmlSizeBytes(0);

        $beforeLimit = $limit - $this->urlSet->count();

        for ($i = 0; $i < ($beforeLimit); $i++) {
            $this->urlSet->add((new Url())->withLoc(uniqid('foo/bar/')));
        }

        $this->expectException(UrlCountLimitException::class);

        $this->urlSet->add((new Url())->withLoc('/break/limit/now!'));

    }

    /**
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     * @throws Exception
     */
    public function testXmlEstimatedSizeLimit()
    {
        $this->urlSet->withMaxUrlCount(0)
                     ->withMaxXmlSizeBytes($this->urlSet->getXmlEstimatedSize());

        $this->expectException(XmlSizeLimitException::class);

        $this->urlSet->add((new Url())->withLoc(''));

    }
}
