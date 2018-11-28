<?php

namespace WebArch\Sitemap\Test\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Model\Image;
use WebArch\Sitemap\Model\ImageUrl;
use WebArch\Sitemap\Test\TestBase;

class ImageUrlTest extends TestBase
{
    protected function setUp()
    {
        $this->sitemapWriter = $this->getDummySitemapWriter();
        $this->serializer    = $this->getSerializer($this->sitemapWriter);
    }

    /**
     * @dataProvider urlDataProvider
     *
     * @param ImageUrl $url
     * @param string $expectedXml
     */
    public function testSerialize(ImageUrl $url, string $expectedXml)
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

        $url = (new ImageUrl())->withLoc('/upload/example/1');
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/upload/example/1</loc>
</url>

END;


        $dataSet[] = [$url, $expectedXml];

        $images = new ArrayCollection();
        $images->add((new Image())->withLoc('/upload/example/1'));
        $images->add((new Image())->withLoc('/upload/example/2'));

        $url = (new ImageUrl())->withLoc('/foo/bar/index.html')
                               ->withImages($images);


        /** @noinspection HtmlUnknownTag */
        $expectedXml = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<url>
  <loc>/foo/bar/index.html</loc>
  <image:image>
    <image:loc>/upload/example/1</image:loc>
  </image:image>
  <image:image>
    <image:loc>/upload/example/2</image:loc>
  </image:image>
</url>

END;
        $dataSet[]   = [$url, $expectedXml];

        return $dataSet;
    }

}
