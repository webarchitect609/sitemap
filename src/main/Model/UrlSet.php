<?php

namespace WebArch\Sitemap\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use WebArch\Sitemap\Enum\XmlEstimateSize;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Traits\ArrayCollectionDecorator;

/**
 * Class UrlSet
 * @package WebArch\Sitemap\Model
 *
 * @XmlRoot(name="urlset",namespace="http://www.sitemaps.org/schemas/sitemap/0.9")
 */
class UrlSet extends ArrayCollection
{
    use ArrayCollectionDecorator;

    /**
     * Max url count <= 50000
     */
    const DEFAULT_MAX_COUNT_LIMIT = 50000;

    /**
     * Max size of XML <= 50MB
     */
    const DEFAULT_MAX_XML_SIZE_BYTES_LIMIT = 52428800;

    /**
     * @var int
     * @Exclude
     */
    protected $maxUrlCount = self::DEFAULT_MAX_COUNT_LIMIT;

    /**
     * @var int
     * @Exclude
     */
    protected $maxXmlSizeBytes = 0;

    /**
     * @var ArrayCollection
     * @Type("ArrayCollection<WebArch\Sitemap\Model\Url>")
     * @XmlList(inline=true,entry="url")
     * @Groups({"sitemap"})
     */
    protected $collection;

    public function __clone()
    {
        $this->collection = clone $this->collection;
    }

    /**
     * @param $element
     *
     * @return bool
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    public function add($element)
    {
        $this->checkLimits();

        return $this->collection->add($element);
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    public function set($key, $value)
    {
        /**
         * Если таким образом добавляется новый,
         * проверить лимиты.
         */
        if (!$this->offsetExists($key)) {
            $this->checkLimits();
        }

        return $this->collection->set($key, $value);
    }

    /**
     * @return int
     */
    public function getMaxUrlCount(): int
    {
        return $this->maxUrlCount;
    }

    /**
     * @param int $maxUrlCount
     *
     * @return $this
     */
    public function withMaxUrlCount(int $maxUrlCount)
    {
        $this->maxUrlCount = $maxUrlCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxXmlSizeBytes(): int
    {
        return $this->maxXmlSizeBytes;
    }

    /**
     * Устанавливает лимит размера результирующего XML файла.
     *
     * \important Т.к. проверка выполняется при каждом add(), установка лимита размера файла будет занимать
     *     значительное время! Рекомендуется использовать getXmlEstimatedSize() отдельно.
     *
     * @param int $maxXmlSizeBytes
     *
     * @return $this
     */
    public function withMaxXmlSizeBytes(int $maxXmlSizeBytes)
    {
        $this->maxXmlSizeBytes = $maxXmlSizeBytes;

        return $this;
    }

    /**
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    public function checkLimits()
    {
        if (
            $this->getMaxUrlCount() > 0
            && $this->collection->count() >= $this->getMaxUrlCount()
        ) {
            throw new UrlCountLimitException(
                sprintf(
                    'Limit of %s urls is reached.',
                    $this->getMaxUrlCount()
                )
            );
        }

        if (
            $this->getMaxXmlSizeBytes() > 0
            && $this->getXmlEstimatedSize() >= $this->getMaxXmlSizeBytes()
        ) {
            throw new XmlSizeLimitException(
                sprintf(
                    'Estimated xml size of %4.2fMB is reached.',
                    ($this->getMaxXmlSizeBytes() / 1048576)
                )
            );
        }
    }

    /**
     * @return int
     */
    public function getXmlEstimatedSize(): int
    {
        $dataLen = XmlEstimateSize::XML_DOCTYPE + XmlEstimateSize::TAG_SIZE_URLSET_WITH_NAMESPACE;

        /** @var Url $url */
        foreach ($this as $url) {

            $dataLen += mb_strlen($url->getLoc()) + XmlEstimateSize::TAG_SIZE_LOC + XmlEstimateSize::TAG_SIZE_URL;

            $priority = $url->getPriority();
            if (!is_null($priority)) {
                $dataLen += mb_strlen($priority) + XmlEstimateSize::TAG_SIZE_PRIORITY;
            }

            $changefreq = $url->getChangefreq();
            if ('' != $changefreq) {
                $dataLen += mb_strlen($changefreq) + XmlEstimateSize::TAG_SIZE_CHANGE_FREQ;
            }

            $lastmod = $url->getLastmod();
            if ($lastmod instanceof DateTimeInterface) {
                /**
                 * Используется такой же формат, как для сериализации
                 */
                $dataLen += mb_strlen($lastmod->format('Y-m-d\TH:i:sP')) + XmlEstimateSize::TAG_SIZE_LASTMOD;
            }
        }

        return $dataLen;
    }

}
