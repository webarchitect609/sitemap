<?php


namespace WebArch\Sitemap\Model;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Image
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot("image:image")
 */
class Image
{
    const IMAGES_MAX_COUNT = 1000;

    /**
     * @var string
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName(value="image:loc")
     * @Groups({"sitemap"})
     */
    protected $loc = '';

    public function __construct($loc = '')
    {
        $this->withLoc($loc);
    }

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

}

