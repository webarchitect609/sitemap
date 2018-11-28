<?php

namespace WebArch\Sitemap\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SkipWhenEmpty;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlRoot;
use WebArch\Sitemap\Model\Abstraction\BaseUrl;

/**
 * Class Url
 * @package WebArch\Sitemap\Model
 *
 * TODO Добавить в setters валидацию входных данных.
 * @XmlRoot(name="url")
 */
class Url extends BaseUrl
{
    const LOC_MAX_LEN = 2048;

    const PRIORITY_MIN = 0.0;

    const PRIORITY_MAX = 1.0;

    /**
     * @var string
     * @XmlElement(cdata=false)
     * @Type("string")
     * @Groups({"sitemap"})
     */
    protected $loc = '';

    /**
     * @var DateTimeImmutable
     * @XmlElement(cdata=false)
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:sP'>")
     * @Groups({"sitemap"})
     */
    protected $lastmod;

    /**
     * @var string
     * @Type("string")
     * @XmlElement(cdata=false)
     * @Groups({"sitemap"})
     */
    protected $changefreq = null;

    /**
     * @var string 0.0 - 1.0
     * @Type("string")
     * @SkipWhenEmpty
     * @XmlElement(cdata=false)
     * @Groups({"sitemap"})
     */
    protected $priority = null;

    /**
     * Url constructor.
     *
     * @param string $loc
     */
    public function __construct($loc = '')
    {
        parent::__construct($loc);
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
     * @return string
     */
    public function getChangefreq(): string
    {
        return (string)$this->changefreq;
    }

    /**
     * @param string $changefreq
     *
     * @return $this
     */
    public function withChangefreq(string $changefreq)
    {
        $this->changefreq = $changefreq;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPriority()
    {
        if (is_null($this->priority)) {
            return null;
        }

        return number_format($this->priority, 1, '.', '');
    }

    /**
     * @param string|float $priority 0.0-1.0
     *
     * @return $this
     */
    public function withPriority($priority)
    {
        if ($priority < self::PRIORITY_MIN || $priority > self::PRIORITY_MAX) {
            throw new InvalidArgumentException(
                sprintf(
                    'Priority must be in range [ %s; %s ]',
                    self::PRIORITY_MIN,
                    self::PRIORITY_MAX
                )
            );
        }

        $this->priority = number_format($priority, 1, '.', '');

        return $this;
    }

    public function resetPriority()
    {
        $this->priority = null;

        return $this;
    }
}
