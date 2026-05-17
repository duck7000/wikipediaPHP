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
 * Get content from wikipedia page
 * @author ed (github user: duck7000)
 */
class Wiki extends MdbBase
{
    protected $pageId;
    protected $wikiContent = array();

    /**
     * @param Config $config OPTIONAL override default config
     */
    public function __construct($id, ?Config $config = null)
    {
        parent::__construct($config);
        $this->pageId = $id;
    }

    /**
     * Get wikipedia content
     * @param string $siteLanguage wikipedia site language
     * @return array()
     */
    public function getWikiContent($siteLanguage = 'en')
    {
        $sectionsContent = $this->getSectionContent($siteLanguage);
        $this->wikiContent['title'] = $sectionsContent[0]['title'];
        $this->wikiContent['pageId'] = $sectionsContent[0]['pageid'];
        foreach ($sectionsContent as $key => $content) {
            if ($key == 0) {
                $this->firstSectionSummary($content['text']);
            }
            $this->procesWikipediaText($content['text']);
        }
        return $this->wikiContent;
    }

    /**
     * Get sections numbers
     * @param string $siteLanguage wikipedia site language
     * @note excluded sections:
     *      External_links
     *      Track_listing
     *      Charts
     *      Certifications
     *      Notes
     *      References
     *      Bibliography
     *      See_also
     *      Release_history
     *      Awards
     *      Further_reading
     * @return array()
     */
    protected function getSections($siteLanguage)
    {
        $sections = array();
        $pageTocData = $this->api->pageIdInfo($this->pageId, $siteLanguage);
        if (!empty($pageTocData)) {
            if (isset($pageTocData->parse->tocdata->sections) &&
                is_array($pageTocData->parse->tocdata->sections)
               )
            {
                $sections[0] = "0";
                foreach ($pageTocData->parse->tocdata->sections as $section) {
                    if (!isset($section->number) ||
                        (int) $section->number != $section->number ||
                        stripos($section->anchor, "External_links") !== false ||
                        stripos($section->anchor, "Track_listing") !== false ||
                        stripos($section->anchor, "Charts") !== false ||
                        stripos($section->anchor, "Certifications") !== false ||
                        stripos($section->anchor, "Notes") !== false ||
                        stripos($section->anchor, "References") !== false ||
                        stripos($section->anchor, "Bibliography") !== false ||
                        stripos($section->anchor, "See_also") !== false ||
                        stripos($section->anchor, "Release_history") !== false ||
                        stripos($section->anchor, "Further_reading") !== false
                       )
                    {
                        continue;
                    }
                    $sections[] = isset($section->index) ? $section->index : null;
                }
                return $sections;
            }
        }
        return false;
    }

    /**
     * Get wikipedia content from pageId
     * @return array()
     */
    protected function getSectionContent($siteLanguage)
    {
        $results = array();
        $sectionNumbers = $this->getSections($siteLanguage);
        if (!empty($sectionNumbers)) {
            foreach ($sectionNumbers as $sectionNumber) {
                $returnData = $this->api->sectionText($sectionNumber, $siteLanguage, $this->pageId);
                if (!empty($returnData)) {
                    $results[] = array(
                        'title' => isset($returnData->parse->title) ? $returnData->parse->title : null,
                        'pageid' => isset($returnData->parse->pageid) ? $returnData->parse->pageid : null,
                        'text' => isset($returnData->parse->text->{'*'}) ? $returnData->parse->text->{'*'} : null
                    );
                }
            }
            return $results;
        }
        return $results;
    }

    /**
     * Process first section of wikipedia content (summary only)
     * @param string $firstContent content of first section
     * @return array()
     */
    protected function firstSectionSummary($firstContent)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $firstContent);
        if (($pElements = $dom->getElementsByTagName('p'))) {
            foreach($pElements as $node) {
                $html = $dom->saveHTML($node);
                if (stripos($node->getAttribute('class'), "mw-empty-elt") !== false) {
                    continue;
                }
                if (stripos($html, "mw-ext-cite-error") !== false) {
                    continue;
                }
                $this->wikiContent['summary'][] = $this->cleanHtml($html);
            }
        }
        $this->firstSectionInfobox($dom);
    }

    /**
     * Process first section infobox of wikipedia content
     * @param string $firstContent content of first section
     * @param string $dom new instance of DOMDocument from firstSectionSummary()
     * @return array()
     */
    protected function firstSectionInfobox($dom)
    {
        $infoboxResults = array();
        if ($tableElements = $dom->getElementsByTagName('table')) {
            foreach ($tableElements as $tableKey => $tableValue) {
                if (stripos($tableValue->getAttribute('class'), "infobox") !== false) {
                    if ($rows = $tableValue->getElementsByTagName('tr')) {
                        foreach ($rows as $row) {
                            $thData = '';
                            $tdData = array();
                            if ($th = $row->getElementsByTagName('th')) {
                                if (!empty($th->item(0))) {
                                    $thTemp = $dom->saveHTML($th->item(0));
                                    $thData =  str_replace(' ', '_', $this->cleanHtml($thTemp));
                                } else {
                                    continue;
                                }
                            }
                            if ($td = $row->getElementsByTagName('td')) {
                                if (!empty($td->item(0))) {
                                    $tdTemp =  $dom->saveHTML($td->item(0));
                                    if (stripos($tdTemp, "<li>") !== false) {
                                        $liParts = explode('<li>', $tdTemp);
                                        foreach ($liParts as $li) {
                                            $liTemp = $this->cleanHtml($li);
                                            if (empty($liTemp)) {
                                                continue;
                                            }
                                            $tdData[]= $liTemp;
                                        }
                                    } else {
                                        $parts = explode('<br>', $tdTemp);
                                        foreach ($parts as $part) {
                                            $brTemp = $this->cleanHtml($part);
                                            if (empty($brTemp)) {
                                                continue;
                                            }
                                            $tdData[]= $brTemp;
                                        }
                                    }
                                } else {
                                    continue;
                                }
                            }
                            $infoboxResults[$thData] = $tdData;
                        }
                        $this->wikiContent['infobox'] = $infoboxResults;
                    }
                }
            }
        }
    }

    /**
     * proces wikipedia html, clean and add to array
     * @param string $textData input wikipedia html
     * @return array()
     */
    protected function procesWikipediaText($textData)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $textData);
        if (($divElements = $dom->getElementsByTagName('div'))) {
            foreach($divElements as $node) {
                if (($element = $node->getAttribute('class')) !== false &&
                    stripos($element, "mw-heading mw-heading2") === false
                   )
                {
                    continue;
                }
                if (($elementId = $node->getElementsByTagName('h2')->item(0)->getAttribute('id')) === false) {
                    continue;
                }
                $text = array();
                while(($node = $node->nextSibling)) {
                    if ($node->nodeName === 'div') {
                        if (($elementSibling = $node->getAttribute('class'))) {
                            if (stripos($elementSibling, "mw-heading mw-heading2") !== false) {
                                break;
                            }
                        }
                    }
                    // personel , Credits
                    if (stripos($elementId, "Personnel") !== false ||
                        stripos($elementId, "Credits") !== false ||
                        stripos($elementId, "Tours") !== false ||
                        stripos($elementId, "Band_members") !== false ||
                        stripos($elementId, "Discography") !== false
                       )
                    {
                        if (!empty(trim(strip_tags($node->nodeValue))) &&
                            ($node->nodeName === 'ul' || $node->nodeName === 'div')
                           )
                        {
                            if (stripos($node->getAttribute('class'), "mw-heading mw-heading3") === false) {
                                if (!empty($node->getElementsByTagName('li'))) {
                                    foreach($node->getElementsByTagName('li') as $listItem) {
                                        if (($liId = $listItem->getAttribute('id')) !== false &&
                                            stripos($liId , "cite") !== false
                                           )
                                        {
                                            continue;
                                        }
                                        if (!empty(trim(strip_tags($listItem->nodeValue)))) {
                                            if (stripos($listItem->nodeValue, "\n") !== false) {
                                                $parts = explode("\n", $listItem->nodeValue, 2);
                                                $value = $parts[0];
                                            } else {
                                                $value = $listItem->nodeValue;
                                            }
                                            $text[] = $this->cleanHtml($value);
                                        }
                                    }
                                    $this->wikiContent[$elementId] = $text;
                                }
                            }
                        }
                    } else {
                        // All other sections
                        if (!empty(trim(strip_tags($node->nodeValue))) &&
                            ($node->nodeName === 'p' || $node->nodeName === 'blockquote')
                           )
                        {
                            $text[] = $this->cleanHtml($dom->saveHTML($node));
                            $this->wikiContent[$elementId] = $text;
                        }
                    }
                }
            }
        }
    }

    /**
     * Clean up html
     * @param string $html input html
     * @return string
     */
    protected function cleanHtml($html)
    {
        $html = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', '', $html);
        $cleanTags =  trim(strip_tags($html));
        //$pattern = array('/(\[\D+\])/', '/(\[\d+\])/'); first: matches [..], second [digits]
        $pattern = array('/(\[\d+\])/');
        $cleanHtml = preg_replace($pattern, '', $cleanTags);
        return trim($cleanHtml);
    }
}
