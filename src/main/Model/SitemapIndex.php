<?php

namespace WebArch\Sitemap\Model;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use UnexpectedValueException;
use WebArch\Sitemap\Model\Traits\ArrayCollectionDecorator;

/**
 * Class SitemapIndex
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot(name="sitemapindex",namespace="http://www.sitemaps.org/schemas/sitemap/0.9")
 */
class SitemapIndex extends ArrayCollection
{
    use ArrayCollectionDecorator;

    /**
     * @var ArrayCollection
     * @Type("ArrayCollection<WebArch\Sitemap\Model\Sitemap>")
     * @XmlList(inline=true, entry="sitemap")
     * @Groups({"sitemapindex"})
     */
    protected $collection;

    /**
     * @var string
     * @Exclude
     */
    protected $tmpFilename = '';

    /**
     * @var string
     * @Exclude
     */
    protected $filename = '';

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * SitemapIndex constructor.
     *
     * @param string $filename
     * @param array $elements
     */
    public function __construct(string $filename, array $elements = [])
    {
        //Не надо вызывать parent::__construct() , т.к. нужно сохранить делигирование, но
        $this->collection = new ArrayCollection($elements);
        $this->withFilename($filename);
    }

    public function __clone()
    {
        $this->collection = clone $this->collection;
    }



    /**
     * @param Sitemap $sitemap
     * @param string $domain
     *
     * @return $this
     * @throws UnexpectedValueException
     */
    public function addSitemap(Sitemap $sitemap, string $domain = '')
    {
        if ('' != $domain) {
            $sitemap = (clone $sitemap)->withLoc($domain . $sitemap->getLoc());
        }

        $this->add($sitemap);

        return $this;
    }

    /**
     * @return string
     */
    public function getTmpFilename(): string
    {
        return $this->tmpFilename;
    }

    /**
     * @param string $tmpFilename
     *
     * @return $this
     */
    public function withTmpFilename(string $tmpFilename)
    {
        $this->tmpFilename = $tmpFilename;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function withFilename(string $filename)
    {
        $this->filename = $filename;

        return $this;
    }

}
