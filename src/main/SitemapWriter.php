<?php

namespace WebArch\Sitemap;

use Doctrine\Common\Annotations\AnnotationRegistry;
use InvalidArgumentException;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RuntimeException;
use UnexpectedValueException;
use vipnytt\RobotsTxtParser\TxtClient;
use WebArch\Sitemap\Enum\SerializationContextGroups;
use WebArch\Sitemap\Exception\DeployException;
use WebArch\Sitemap\Exception\UrlCountLimitException;
use WebArch\Sitemap\Exception\XmlSizeLimitException;
use WebArch\Sitemap\Model\Abstraction\BaseUrl;
use WebArch\Sitemap\Model\Sitemap;
use WebArch\Sitemap\Model\SitemapIndex;
use WebArch\Sitemap\Model\Url;

class SitemapWriter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const SERIALIZER_CACHE_DIR = '/../../cache/serializer';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * @var Sitemap
     */
    protected $defaultSitemap;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $baseDir = '';

    /**
     * @var string
     */
    protected $robotsTxt = '/robots.txt';

    /**
     * @var
     */
    protected $robotsTxtParser;

    /**
     * SitemapWriter constructor.
     *
     * @param string $domain Like https://example.org
     * @param string $baseDir Document root, for example
     *
     * @throws UnexpectedValueException
     */
    public function __construct(string $domain, string $baseDir)
    {
        $this->domain = $domain;
        if (!file_exists($baseDir) || !is_dir($baseDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Base dir does not exist: %s',
                    $baseDir
                )
            );
        }
        $this->baseDir = $baseDir;
        $this->sitemapIndex = new SitemapIndex('/sitemap.xml');
        $this->sitemapIndex->addSitemap(new Sitemap('/sitemap_default.xml'), $this->getDomain());
        $this->defaultSitemap = $this->getSitemapIndex()->first();
        $this->initSerializer();
        $this->setLogger(new NullLogger());
    }

    protected function initSerializer()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $this->serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy())
        )
                                             ->setCacheDir(__DIR__ . self::SERIALIZER_CACHE_DIR)
                                             ->build();
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @param Sitemap $sitemap
     *
     * @return $this
     * @throws UnexpectedValueException
     */
    public function addSitemap(Sitemap $sitemap)
    {
        $this->sitemapIndex->addSitemap($sitemap, $this->getDomain());

        return $this;
    }

    /**
     * @return SitemapIndex
     */
    public function getSitemapIndex(): SitemapIndex
    {
        return $this->sitemapIndex;
    }

    /**
     * Добавляет URL в карту сайта, автоматически добавляя впереди domain
     *
     * @param Url $url
     *
     * @return SitemapWriter
     * @throws UrlCountLimitException
     * @throws XmlSizeLimitException
     *
     * TODO Возможно, надо сделать метод, который ловит исключение по ограничению кол-ва ссылок и заводит новый Sitemap?
     */
    public function addUrl(BaseUrl $url)
    {
        $this->defaultSitemap->addUrl($url, $this->getDomain());

        return $this;
    }

    /**
     * @throws DeployException
     * @throws RuntimeException
     */
    public function write()
    {
        $this->initRobotsTxtParser();
        $this->writeAllSitemaps();
        $this->writeSitemapIndex();
        $this->deployAll();
    }

    /**
     * @throws RuntimeException
     */
    private function writeAllSitemaps()
    {
        /** @var Sitemap $oldSitemap */
        foreach ($this->getSitemapIndex() as $index => $oldSitemap) {

            $sitemap = $this->respectRobotsTxt($oldSitemap);
            //Заменить, т.к. было клонирование
            $this->getSitemapIndex()->removeElement($oldSitemap);
            $this->getSitemapIndex()->add($sitemap);

            $sitemap->withTmpFilename($this->generateTmpFilename())
                    ->refreshLastmod();

            $fileWriteResult = file_put_contents(
                $sitemap->getTmpFilename(),
                $this->serializer->serialize(
                    $sitemap,
                    'xml',
                    SerializationContext::create()->setGroups([SerializationContextGroups::SITEMAP])
                )
            );

            if (false === $fileWriteResult) {
                throw new RuntimeException(
                    sprintf(
                        'Error writing sitemap with loc `%s` to temp file %s',
                        $sitemap->getLoc(),
                        $sitemap->getTmpFilename()
                    )
                );
            }

        }
    }

    /**
     * @throws RuntimeException
     */
    private function writeSitemapIndex()
    {
        $this->getSitemapIndex()->withTmpFilename($this->generateTmpFilename());

        $fileWriteResult = file_put_contents(
            $this->getSitemapIndex()->getTmpFilename(),
            $this->serializer->serialize(
                $this->getSitemapIndex(),
                'xml',
                SerializationContext::create()->setGroups([SerializationContextGroups::SITEMAP_INDEX])
            )
        );

        if (false === $fileWriteResult) {
            throw new RuntimeException(
                sprintf(
                    'Error writing sitemap %s to temp file %s',
                    $this->getSitemapIndex()->getFilename(),
                    $this->getSitemapIndex()->getTmpFilename()
                )
            );
        }
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    private function generateTmpFilename()
    {
        $tempnam = tempnam(sys_get_temp_dir(), uniqid('sitemapWriter_', true));

        if (false === $tempnam) {
            throw new RuntimeException(
                'Unable to create temp file.'
            );
        }

        return $tempnam;
    }

    /**
     * @throws DeployException
     */
    private function deployAll()
    {
        clearstatcache();

        /** @var Sitemap $sitemap */
        foreach ($this->getSitemapIndex() as $sitemap) {
            $this->deployFile(
                $sitemap->getTmpFilename(),
                $this->getBaseDir() . $sitemap->getFilename()
            );
        }

        $this->deployFile(
            $this->getSitemapIndex()->getTmpFilename(),
            $this->getBaseDir() . $this->getSitemapIndex()->getFilename()
        );
    }

    /**
     * @param string $fromFilename
     * @param string $toFilename
     *
     * @throws DeployException
     */
    private function deployFile(string $fromFilename, string $toFilename)
    {
        if (!is_file($fromFilename) || !file_exists($fromFilename)) {
            throw new DeployException(
                sprintf(
                    'File %s not found.',
                    $fromFilename
                )
            );
        }

        if (false == rename($fromFilename, $toFilename)) {
            throw new DeployException(
                sprintf(
                    'Error moving %s -> %s',
                    $fromFilename,
                    $toFilename
                )
            );
        }

        /**
         * Сделать файл доступным всем на чтение,
         * не меняя остальные права
         */
        if (false == chmod($toFilename, fileperms($toFilename) & 0777 | 0444)) {
            $this->logger->warning(
                sprintf(
                    'Error setting reading permissions for %s',
                    $toFilename
                )
            );
        }

    }

    private function initRobotsTxtParser()
    {
        $robotsTxtFilename = $this->getBaseDir() . $this->getRobotsTxt();
        if ('' == $this->getRobotsTxt()) {
            $this->logger->warning('Using robots.txt is disabled.');

            return;
        }

        if (!is_file($robotsTxtFilename) || !file_exists($robotsTxtFilename)) {
            $this->logger->warning(
                sprintf(
                    'Robots.txt not found in %s',
                    $robotsTxtFilename
                )
            );

            return;
        }

        $this->logger->info('Using robots.txt is enabled.');

        $this->robotsTxtParser = new TxtClient($this->getDomain(), 200, file_get_contents($robotsTxtFilename));
    }

    /**
     * @return string
     */
    public function getRobotsTxt(): string
    {
        return $this->robotsTxt;
    }

    /**
     * @param string $robotsTxt
     *
     * @return $this
     */
    public function withRobotsTxt(string $robotsTxt)
    {
        $this->robotsTxt = $robotsTxt;

        return $this;
    }

    /**
     * @param Sitemap $sitemap
     *
     * @return Sitemap
     */
    protected function respectRobotsTxt(Sitemap $sitemap): Sitemap
    {
        $sitemap = clone $sitemap;

        if (!($this->robotsTxtParser instanceof TxtClient)) {
            return $sitemap;
        }

        /** @var Url $url */
        foreach ($sitemap->getUrlSet() as $index => $url) {
            if ($this->robotsTxtParser->userAgent()->isDisallowed($url->getLoc())) {
                $sitemap->getUrlSet()->remove($index);
            }
        }

        return $sitemap;
    }

}
