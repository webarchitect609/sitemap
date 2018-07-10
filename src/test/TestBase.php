<?php

namespace WebArch\Sitemap\Test;

use DateTimeImmutable;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WebArch\Sitemap\Enum\ChangeFreq;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Url;
use WebArch\Sitemap\Model\UrlSet;
use WebArch\Sitemap\SitemapWriter;

abstract class TestBase extends TestCase
{
    /**
     * @var SitemapWriter
     */
    protected $sitemapWriter;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var UrlSet
     */
    protected $urlSet;

    /**
     * @var string
     */
    protected static $baseDir = '';

    /**
     * @param mixed $object
     */
    private static function isObject($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(
                'Argument `object` expected to be object.'
            );
        }
    }

    /**
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    public function getPropertyValue($object, string $propertyName)
    {
        self::isObject($object);
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @return SitemapWriter
     */
    public function getDummySitemapWriter(): SitemapWriter
    {
        self::$baseDir = sys_get_temp_dir() . '/deployed_sitemap_' . date('Y-m-d_H-i-s');

        if (!is_dir(self::$baseDir)) {
            mkdir(self::$baseDir);
        }

        return new SitemapWriter('http://example.org', self::$baseDir);
    }

    /**
     * @param SitemapWriter $sitemapWriter
     *
     * @return mixed
     */
    public function getSerializer(SitemapWriter $sitemapWriter): SerializerInterface
    {
        return $this->getPropertyValue($sitemapWriter, 'serializer');
    }

    /**
     * @return UrlSet
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     */
    public function getUrlSet(): UrlSet
    {
        $urlSet = new UrlSet();

        $urlSet->add(
            (new Url())->withLoc('/some/url/location/goes/here')
                       ->withChangefreq(ChangeFreq::CHANGE_FREQ_ALWAYS)
                       ->withPriority(0.0)
                       ->withLastmod(
                           DateTimeImmutable::createFromFormat(
                               'Y-m-d H:i:s',
                               '2018-02-28 12:58:36'
                           )
                       )
        );

        $urlSet->add(
            (new Url())->withLoc('/')
                       ->withChangefreq(ChangeFreq::CHANGE_FREQ_NEVER)
                       ->withPriority(0.0)
        );

        $urlSet->add(
            (new Url())->withLoc('/FOO')
                       ->withPriority(0.4)
        );

        $urlSet->add(
            (new Url())->withLoc('/bar?sdf=234')
        );

        return $urlSet;
    }

}
