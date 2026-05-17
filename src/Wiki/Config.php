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
 * Configuration class
 * @author ed (github user: duck7000)
 */
class Config
{

    /**
     * Default userAgent to use in request
     * @var string
     */
    public $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0';

    /**
     * Base wikidata url
     * @var string
     */
    public $baseWikidataUrl = 'https://www.wikidata.org';

    /**
     * Base wikipedia url
     * @var string
     */
    public $baseWikiUrl = 'wikipedia.org';

    /**
     * Throw Exception if something goes wrong with the api call
     * True: throws Exception, false: returns empty object
     * @var boolean
     */
    public $throwHttpExceptions = false;

    /**
     * Set curlopt_timout, this is the time out if curl has a connection problem
     * @var int
     */
    public $curloptTimeout = 30;

}
