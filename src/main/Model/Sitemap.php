<?php

namespace WebArch\Sitemap\Model;

use DateTimeImmutable;
use DateTimeInterface;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use vipnytt\RobotsTxtParser\Client\Cache\MySQL\Base;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Abstraction\BaseUrl;

/**
 * Class Sitemap
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot("urlset",namespace="http://www.sitemaps.org/schemas/sitemap/0.9")
 */
class Sitemap
{
    /**
     * @var string
     * @Type("string")
     * @Groups({"sitemapindex"})
     * @XmlElement(cdata=false)
     */
    protected $loc = '';

    /**
     * @var DateTimeImmutable
     * @Type("DateTimeImmutable")
     * @Groups({"sitemapindex"})
     * @XmlElement(cdata=false)
     */
    protected $lastmod;

    /**
     * @var UrlSet
     * @Type("WebArch\Sitemap\Model\UrlSet")
     * @XmlList(inline=true, entry="urlset")
     * @Groups({"sitemap"})
     */
    protected $urlSet;

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

    /**
     * Sitemap constructor.
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->urlSet = new UrlSet();
        $this->withFilename($filename)
             ->withLoc($filename);

    }

    public function __clone()
    {
        $this->urlSet = clone $this->urlSet;
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

    /**
     * @return $this
     */
    public function refreshLastmod()
    {
        $maxLastMod = $this->getLastmod();

        /** @var BaseUrl $url */
        foreach ($this->getUrlSet() as $url) {

            if(!$url instanceof Url){
                continue;
            }

            if (!($url->getLastmod() instanceof DateTimeInterface)) {
                continue;
            }

            if (
                is_null($maxLastMod)
                || (
                    $maxLastMod instanceof DateTimeInterface
                    && $maxLastMod < $url->getLastmod()
                )
            ) {
                $maxLastMod = $url->getLastmod();
            }

        }

        if ($maxLastMod instanceof DateTimeInterface) {
            $this->withLastmod($maxLastMod);
        }

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getLastmod()
    {
        return $this->lastmod;
    }

    /**
     * @param DateTimeImmutable $lastmod
     *
     * @return $this
     */
    public function withLastmod(DateTimeImmutable $lastmod)
    {
        $this->lastmod = $lastmod;

        return $this;
    }

    /**
     * @return UrlSet
     */
    public function getUrlSet(): UrlSet
    {
        return $this->urlSet;
    }

    /**
     * @param UrlSet $urlSet
     *
     * @return $this
     */
    public function withUrlSet(UrlSet $urlSet)
    {
        $this->urlSet = $urlSet;

        return $this;
    }

    /**
     * @param BaseUrl $url
     *
     * @param string $domain
     *
     * @return $this
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    public function addUrl(BaseUrl $url, string $domain = '')
    {
        if ('' != $domain) {
            $url = (clone $url)->withLoc($domain . $url->getLoc());
        }

        $this->getUrlSet()->add($url);

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
