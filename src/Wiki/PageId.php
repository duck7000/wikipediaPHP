<?php

#############################################################################
# wikipediaPHP                                  ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Wiki;

/**
 * methods to get wikipedia pageId from Qid or url
 * @author ed (github user: duck7000)
 */
class PageId extends MdbBase
{

    /**
     * @param Config $config OPTIONAL override default config
     */
    public function __construct(?Config $config = null)
    {
        parent::__construct($config);
    }

    /**
     * Convert Qid to PageId
     * @param string $qid wikidata Qid like Q12345
     * @return string pageId or false
     */
    public function qidToPageId($qid)
    {
        $data = $this->api->qidInfo($qid);
        if (!empty($data) &&
            $data->success == 1 &&
            !empty($data->entities->$qid->sitelinks->enwiki->url)
           )
        {
            $wikiUrl = $data->entities->$qid->sitelinks->enwiki->url;
            $pageId = $this->urlToPageId($wikiUrl);
            if (!empty($pageId)) {
                return $pageId;
            }
        }
        return false;
    }

    /**
     * convert wikipedia Url to pageId
     * @param string $url FQDN e.g. https://en.wikipedia.org/wiki/Peter_Fonda
     * @note $url can be for different languages e.g. en, fr, se etc
     * @return string pageId or false
     */
    public function urlToPageId($url)
    {
        $parseUrlParts = parse_url(rtrim($url, "/"));
        $hostParts = explode('.', $parseUrlParts['host'], 2);
        $siteLanguage = trim($hostParts[0]);
        $urlParts = explode('/', ltrim($parseUrlParts['path'], "/"), 2);
        if (!empty($urlParts[1])) {
            $pageNameInfo = $this->api->pageNameInfo($urlParts[1], $siteLanguage);
            if (!empty($pageNameInfo) && isset($pageNameInfo->query->pages)) {
                $pages = get_object_vars($pageNameInfo->query->pages);
                if (is_array($pages)) {
                    $arrayKey = array_key_first($pages);
                    $pageId = $pageNameInfo->query->pages->{$arrayKey}->pageid;
                    if (!empty($pageId)) {
                        return $pageId;
                    }
                }
            }
        }
        return false;
    }
}
