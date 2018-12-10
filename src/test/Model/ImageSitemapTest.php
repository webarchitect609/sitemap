<?php

namespace WebArch\Sitemap\Test\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Image;
use WebArch\Sitemap\Model\ImageSitemap;
use WebArch\Sitemap\Model\ImageUrl;
use WebArch\Sitemap\Model\ImageUrlSet;
use WebArch\Sitemap\Model\UrlSet;
use WebArch\Sitemap\Test\TestBase;

class ImageSitemapTest extends TestBase
{
    /**
     * @var ImageSitemap
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
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc>/upload/example/1</loc>
    <lastmod>2018-02-28T12:58:36+00:00</lastmod>
  </url>
  <url>
    <loc>/foo/bar/index.html</loc>
    <image:image>
      <image:loc>/upload/example/1</image:loc>
    </image:image>
    <image:image>
      <image:loc>/upload/example/2</image:loc>
    </image:image>
    <lastmod>2018-02-28T12:58:36+00:00</lastmod>
  </url>
</urlset>

END;

        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer    = $this->getSerializer($this->sitemapWriter);
        $this->urlSet        = $this->getUrlSet();
        $this->sitemap       = (new ImageSitemap(''))->withLoc('foo/bar.xml')
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

    public function getUrlSet(): UrlSet
    {
        //  TODO - придумать больше тестов

        $urlSet = new ImageUrlSet();

        $images = new ArrayCollection();
        $images->add((new Image())->withLoc('/upload/example/1'));
        $images->add((new Image())->withLoc('/upload/example/2'));

        //  Начало заполнения UrlSet
        $urlSet->add((new ImageUrl())
            ->withLoc('/upload/example/1')
            ->withLastmod(DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                '2018-02-28 12:58:36'
            ))
        );

        $urlSet->add((new ImageUrl())->withLoc('/foo/bar/index.html')
                                     ->withImages($images)
                                     ->withLastmod(DateTimeImmutable::createFromFormat(
                                         'Y-m-d H:i:s',
                                         '2018-02-28 12:58:36'
                                     ))
        );

        return $urlSet;
    }
}
