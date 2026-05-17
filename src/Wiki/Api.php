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
 * Accessing data through API
 * @author Ed (duck7000)
 */
class Api
{

    /**
     * @var Config
     */
    private $config;

    /**
     * API constructor.
     * @param Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get qid info
     * @param string $qid wikidata Qid
     * @return json object or false
     */
    public function qidInfo($qid)
    {
        $url = $this->config->baseWikidataUrl;
        $url .= '/';
        $url .= 'w';
        $url .= '/';
        $url .= 'api.php';
        $url .= '?';
        $url .= 'action=wbgetentities';
        $url .= '&';
        $url .= 'props=sitelinks/urls';
        $url .= '&';
        $url .= 'ids=' . $qid;
        $url .= '&';
        $url .= 'sitefilter=enwiki';
        $url .= '&';
        $url .= 'format=json';
        return $this->execRequest($url);
    }

    /**
     * Get page name info
     * @param string $pageName wikipedia page name
     * @param string $siteLanguage wikipedia site language
     * @return json object or false
     */
    public function pageNameInfo($pageName, $siteLanguage)
    {
        $url = 'https://';
        $url .= $siteLanguage;
        $url .= '.';
        $url .= $this->config->baseWikiUrl;
        $url .= '/';
        $url .= 'w';
        $url .= '/';
        $url .= 'api.php';
        $url .= '?';
        $url .= 'action=query';
        $url .= '&';
        $url .= 'prop=info';
        $url .= '&';
        $url .= 'titles=' . $pageName;
        $url .= '&';
        $url .= 'format=json';
        return $this->execRequest($url);
    }

    /**
     * Search wikipedia api
     * @param string $searchString search query
     * @param string $siteLanguage wikipedia site language
     * @return json object or false
     */
    public function searchWikipedia($searchString, $siteLanguage)
    {
        $url = 'https://';
        $url .= $siteLanguage;
        $url .= '.';
        $url .= $this->config->baseWikiUrl;
        $url .= '/';
        $url .= 'w';
        $url .= '/';
        $url .= 'api.php';
        $url .= '?';
        $url .= 'action=query';
        $url .= '&';
        $url .= 'list=search';
        $url .= '&';
        $url .= 'srsearch=' . rawurlencode($searchString);
        $url .= '&';
        $url .= 'format=json';
        return $this->execRequest($url);
    }

    /**
     * Get sections list from wikipedia page
     * @param string $pageId wikipedia page id
     * @param string $siteLanguage wikipedia site language
     * @return json object or false
     */
    public function pageIdInfo($pageId, $siteLanguage)
    {
        $url = 'https://';
        $url .= $siteLanguage;
        $url .= '.';
        $url .= $this->config->baseWikiUrl;
        $url .= '/';
        $url .= 'w';
        $url .= '/';
        $url .= 'api.php';
        $url .= '?';
        $url .= 'action=parse';
        $url .= '&';
        $url .= 'prop=tocdata';
        $url .= '&';
        $url .= 'pageid=' . $pageId;
        $url .= '&';
        $url .= 'format=json';
        return $this->execRequest($url);
    }

    /**
     * Get sections list from wikipedia page
     * @param string $section section number
     * @param string $siteLanguage wikipedia site language
     * @param string $pageId wikipedia page id
     * @return json object or false
     */
    public function sectionText($section, $siteLanguage, $pageId)
    {
        $url = 'https://';
        $url .= $siteLanguage;
        $url .= '.';
        $url .= $this->config->baseWikiUrl;
        $url .= '/';
        $url .= 'w';
        $url .= '/';
        $url .= 'api.php';
        $url .= '?';
        $url .= 'action=parse';
        $url .= '&';
        $url .= 'section=' . $section;
        $url .= '&';
        $url .= 'prop=text';
        $url .= '&';
        $url .= 'pageid=' . $pageId;
        $url .= '&';
        $url .= 'format=json';
        return $this->execRequest($url);
    }

    /**
     * Execute request
     * @param string $url
     * @return \stdClass, false or exception
     */
    public function execRequest($url)
    {
        $request = new Request($url, $this->config);
        $request->sendRequest();
        if (200 == $request->getStatus() || 307 == $request->getStatus()) {
            return json_decode($request->getResponseBody());
        } elseif (404 == $request->getStatus()) {
            return false;
        } else {
            if ($this->config->throwHttpExceptions) {
                throw new \Exception("Failed to retrieve query");
            } else {
                return new \StdClass();
            }
        }
    }
}
