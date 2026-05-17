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
 * Search wikipedia api
 * @author ed (github user: duck7000)
 */
class Search extends MdbBase
{

    /**
     * @param Config $config OPTIONAL override default config
     */
    public function __construct(?Config $config = null)
    {
        parent::__construct($config);
    }

    /**
     * Search for wikipedia pages matching input search query
     * @param string $earchString input search query
     * @param string $siteLanguage wikipedia site language
     */
    public function search($searchString, $siteLanguage = 'en')
    {
        $results = array();
        $searchResults = $this->api->searchWikipedia($searchString, $siteLanguage);
        if (!empty($searchResults)) {
            if (isset($searchResults->query->search) &&
                is_array($searchResults->query->search) &&
                count($searchResults->query->search) > 0
               )
            {
                foreach ($searchResults->query->search as $title) {
                    $results[] = array(
                        'title' => isset($title->title) ? $title->title : null,
                        'pageId' => isset($title->pageid) ? $title->pageid : null,
                        'description' => isset($title->snippet) ? $title->snippet : null
                    );
                }
            }
        }
        return $results;
    }
}
