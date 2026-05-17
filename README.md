wikipediaPHP
=======

PHP library for retrieving data from wikipedia API.<br>
This library is primarily meant to get wikipedia content for Music albums<br>
but can also be used for artist/persons or bands<br>
@Note: Sections not included:<br>
>     External_links
>     Track_listing
>     Charts
>     Certifications
>     Notes
>     References
>     Bibliography
>     See_also
>     Release_history
>     Awards
>     Further_reading


Quick Start
===========

* Clone this repo or download the latest [release zip]
* Include `bootstrap.php`.
* Get some data
@note: wikipedia siteLanguage is always default: en (parameter is only needed for different language)

For wikipedia search:
```php
$wiki = new \Wiki\Search();
$results = $wiki->search("peter fonda", $siteLanguage = "en");
```

For Wikipedia page content (with pageId):
```php
$wiki = new \Wiki\WikiContent("2158677");
$results = $wiki->getWikiContent($siteLanguage = "en");
```

Convert wikimedia Qid to pageId:<br>
@parameter string $qid wikidata id
```php
$wiki = new \Wiki\PageId();
$results = $wiki->qidToPageId($qid);
```

Convert wikipedia page url to pageId:<br>
@param string $url FQDN e.g. https://en.wikipedia.org/wiki/Peter_Fonda
```php
$wiki = new \Wiki\PageId();
$results = $wiki->urlToPageId($url);
```


Installation
============

Download the latest version or latest git version and extract it to your webserver. Use one of the above methods to get some results

Get the files with one of:
* Git clone. Checkout the latest release tag
* [Zip/Tar download]

### Requirements
* PHP >= works from 8.0 - 8.5
* PHP cURL extension
* PHP json extension

