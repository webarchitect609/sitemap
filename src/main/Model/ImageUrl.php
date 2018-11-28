<?php


namespace WebArch\Sitemap\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use WebArch\Sitemap\Model\Abstraction\BaseUrl;

/**
 * Class ImageUrl
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot("url")
 */
class ImageUrl extends BaseUrl
{
    const IMAGES_MAX_COUNT = 1000;

    /**
     * @var string
     * @XmlElement(cdata=false)
     * @Type("string")
     * @Groups({"sitemap"})
     * @Accessor(getter="getLoc",setter="withLoc")
     */
    protected $loc = '';

    /**
     * @var ArrayCollection
     * @Groups({"sitemap"})
     * @XmlList(inline=true, entry="image:image")
     */
    protected $images;


    /**
     * @return string
     */
    public function getLoc(): string
    {
        return $this->loc;
    }

    /**
     * @param string $loc
     *
     * @return $this
     */
    public function withLoc(string $loc)
    {
        $this->loc = $loc;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection $images
     *
     * @return $this
     */
    public function withImages($images): self
    {
        $this->images = $images;

        return $this;
    }

}

