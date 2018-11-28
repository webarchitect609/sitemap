<?php

namespace WebArch\Sitemap\Test\Model;

use JMS\Serializer\SerializationContext;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Model\Image;
use WebArch\Sitemap\Test\TestBase;

class ImageTest extends TestBase
{
    protected function setUp()
    {
        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer    = $this->getSerializer($this->sitemapWriter);
    }

    /**
     * @dataProvider urlDataProvider
     *
     * @param Image $url
     * @param string $expectedXml
     */
    public function testSerialize(Image $url, string $expectedXml)
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

        $url         = (new Image())->withLoc('/upload/example/1');
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<image:image>
  <image:loc>/upload/example/1</image:loc>
</image:image>

END;


        $dataSet[] = [$url, $expectedXml];

        return $dataSet;
    }
}
