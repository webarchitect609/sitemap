<?php


namespace WebArch\Sitemap\Model;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;


/**
 * Class Sitemap
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot("urlset",namespace="http://www.sitemaps.org/schemas/sitemap/0.9")
 * @XmlNamespace(uri="http://www.google.com/schemas/sitemap-image/1.1", prefix="image")
 */
class ImageSitemap extends Sitemap
{
    /**
     * @var UrlSet
     * @Type("WebArch\Sitemap\Model\ImageUrlSet")
     * @XmlList(inline=true, entry="urlset")
     * @Groups({"sitemap"})
     */
    protected $urlSet;

    /**
     * ImageSitemap constructor.
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);
        $this->withUrlSet(new ImageUrlSet());
    }
}