<?php

namespace WebArch\Sitemap\Enum;

class XmlEstimateSize
{
    /**
     * All includes `\n` and leading spaces with respect to tag level and using jms_serializer
     */

    /**
     * <?xml version="1.0" encoding="UTF-8"?> + 1 \n
     */
    const XML_DOCTYPE = 39;

    /**
     * <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset> + 2 \n
     */
    const TAG_SIZE_URLSET_WITH_NAMESPACE = 71;

    /**
     * <url></url> + 4 \s + 2 \n
     */
    const TAG_SIZE_URL = 17;

    /**
     * <loc></loc> + 4 \s + 1 \n
     */
    const TAG_SIZE_LOC = 16;

    /**
     * <lastmod></lastmod> + 4 \s + 1 \n
     */
    const TAG_SIZE_LASTMOD = 24;

    /**
     * <changefreq></changefreq> + 4 \s + 1 \n
     */
    const TAG_SIZE_CHANGE_FREQ = 30;

    /**
     * <priority></priority> + 4 \s + 1 \n
     */
    const TAG_SIZE_PRIORITY = 26;
}
