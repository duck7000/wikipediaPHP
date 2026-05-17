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
 * Data on wikipedia API
 * @author ed (github user: duck7000)
 */
class MdbBase extends Config
{
    public $version = '1.0.0';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var API
     */
    protected $api;

    /**
     * @param Config $config OPTIONAL override default config
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?: $this;
        $this->api = new Api($this->config);
    }
}
