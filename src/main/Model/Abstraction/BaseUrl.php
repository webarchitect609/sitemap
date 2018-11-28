<?php

namespace WebArch\Sitemap\Model\Abstraction;

abstract class BaseUrl
{
    private $loc;

    /**
     * BaseUrl constructor.
     *
     * @param string $loc
     */
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