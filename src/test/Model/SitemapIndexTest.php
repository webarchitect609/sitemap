<?php

namespace WebArch\Sitemap\Test\Model;

use DateTimeImmutable;
use JMS\Serializer\SerializationContext;
use UnexpectedValueException;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Sitemap;
use WebArch\Sitemap\Model\SitemapIndex;
use WebArch\Sitemap\Model\Url;
use WebArch\Sitemap\Test\TestBase;

class SitemapIndexTest extends TestBase
{
    /**
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * @throws UnexpectedValueException
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    protected function setUp()
    {
        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer = $this->getSerializer($this->sitemapWriter);

        $this->sitemapIndex = new SitemapIndex('');

        /**
         * Добавляем url, но в XML он попасть не должен
         */
        $url = (new Url())->withLoc('/123/')
                          ->withLastmod(DateTimeImmutable::createFromFormat('Y-m-d', '2018-07-14'))
                          ->withPriority(0.3);

        $this->sitemapIndex->add(
            (new Sitemap(''))->withLoc('foo/bar.xml')
                           ->withLastmod(
                               DateTimeImmutable::createFromFormat(
                                   'Y-m-d H:i:s',
                                   '2016-05-17 13:55:06'
                               )
                           )
                           ->addUrl($url)
        );

        $this->sitemapIndex->add(
            (new Sitemap(''))->withLoc('bar/baz')
        );
    }

    public function testSerialize()
    {
        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>foo/bar.xml</loc>
    <lastmod>2016-05-17T13:55:06+0000</lastmod>
  </sitemap>
  <sitemap>
    <loc>bar/baz</loc>
  </sitemap>
</sitemapindex>

END;

        self::assertEquals(
            $expectedXml,
            $this->serializer->serialize(
                $this->sitemapIndex,
                'xml',
                SerializationContext::create()->setGroups([SerializationContextGroups::SITEMAP_INDEX])
            )
        );
    }

}
