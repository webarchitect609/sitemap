<?php

namespace WebArch\Sitemap\Test\Model;

use DateTimeImmutable;
use JMS\Serializer\SerializationContext;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Sitemap;
use WebArch\Sitemap\Test\TestBase;

class SitemapTest extends TestBase
{
    /**
     * @var Sitemap
     */
    protected $sitemap;

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
    <priority>0.4</priority>
  </url>
  <url>
    <loc>/bar?sdf=234</loc>
  </url>
</urlset>

END;

        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer = $this->getSerializer($this->sitemapWriter);
        $this->urlSet = $this->getUrlSet();
        $this->sitemap = (new Sitemap(''))->withLoc('foo/bar.xml')
                                          ->withLastmod(
                                              DateTimeImmutable::createFromFormat(
                                                  'Y-m-d H:i:s',
                                                  '2016-05-17 13:55:06'
                                              )

                                          )
                                          ->withUrlSet($this->urlSet);
    }

    public function testSerialize()
    {
        self::assertEquals(
            $this->expectedXml,
            $this->serializer->serialize(
                $this->sitemap,
                'xml',
                SerializationContext::create()->setGroups([SerializationContextGroups::SITEMAP])
            )
        );
    }
}
