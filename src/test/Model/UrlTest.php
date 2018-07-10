<?php

namespace WebArch\Sitemap\Test\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\Exception;
use WebArch\Sitemap\Enum\ChangeFreq;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Model\Url;
use WebArch\Sitemap\Test\TestBase;

class UrlTest extends TestBase
{
    protected function setUp()
    {
        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer = $this->getSerializer($this->sitemapWriter);
    }

    /**
     * @dataProvider urlDataProvider
     *
     * @param Url $url
     * @param string $expectedXml
     */
    public function testSerialize(Url $url, string $expectedXml)
    {
        self::assertEquals(
            $expectedXml,
            $this->serializer->serialize(
                $url,
                'xml',
                SerializationContext::create()->setGroups([SerializationContextGroups::SITEMAP])
            )
        );
    }

    public function urlDataProvider(): array
    {
        $dataSet = [];

        $url = (new Url())->withLoc('/foo/bar/index.html')
                          ->withChangefreq(ChangeFreq::CHANGE_FREQ_HOURLY)
                          ->withPriority(0.4)
                          ->withLastmod(
                              DateTimeImmutable::createFromFormat(
                                  'Y-m-d H:i:s',
                                  '2018-02-28 12:58:36'
                              )
                          );

        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/foo/bar/index.html</loc>
  <lastmod>2018-02-28T12:58:36+00:00</lastmod>
  <changefreq>hourly</changefreq>
  <priority>0.4</priority>
</url>

END;
        $dataSet[] = [$url, $expectedXml];

        $url = (new Url())->withLoc('/foo/bar/index.html')
                          ->withChangefreq(ChangeFreq::CHANGE_FREQ_HOURLY)
                          ->withPriority(0.4);
        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/foo/bar/index.html</loc>
  <changefreq>hourly</changefreq>
  <priority>0.4</priority>
</url>

END;
        $dataSet[] = [$url, $expectedXml];

        $url = (new Url())->withLoc('/foo/bar/index.html')
                          ->withChangefreq(ChangeFreq::CHANGE_FREQ_HOURLY);
        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/foo/bar/index.html</loc>
  <changefreq>hourly</changefreq>
</url>

END;
        $dataSet[] = [$url, $expectedXml];

        $url = (new Url())->withLoc('/foo/bar/index.html');
        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/foo/bar/index.html</loc>
</url>

END;
        $dataSet[] = [$url, $expectedXml];

        $url = (new Url());
        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc></loc>
</url>

END;
        $dataSet[] = [$url, $expectedXml];

        return $dataSet;
    }

    /**
     * @throws Exception
     */
    public function testPriority()
    {
        $url = new Url('/foo/bar');

        $this->assertNull($url->getPriority());

        $priority = 0.3;
        $url->withPriority($priority);
        $this->assertEquals($priority, $url->getPriority());

        $priority = '1.0';
        $url->withPriority($priority);
        $this->assertEquals($priority, $url->getPriority());

        $url->resetPriority();
        $this->assertNull($url->getPriority());

        $this->expectException(InvalidArgumentException::class);
        $url->withPriority(1.2);
    }
}
