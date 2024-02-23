<?php

if (isset($_GET['p'])) {
    
    $sitemapurl = $_GET['p'];
    
    //echo "URL-ul sitemapului este: " . $sitemapurl;
  
} else {
   
    echo "Parametrul 'p' lipsește din URL.";
}

$xml = simplexml_load_file($sitemapurl . "/page-sitemap.xml");


if ($xml === false) {
    die('Error loading XML');
}

$urls = [];

foreach ($xml->url as $url) {
    $urls[] = (string)$url->loc;
}

// Funcție pentru a extrage meta title și meta description pentru un URL dat
function getMetaInfo($url)
{
    // Inițializează un context stream pentru a gestiona solicitarea HTTP
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    
    // Realizează solicitarea HTTP și obține conținutul paginii
    $pageContent = file_get_contents($url, false, $context);

    if ($pageContent === false) {
        // Tratează eroarea în cazul în care nu se poate obține conținutul paginii
        return [
            'url' => $url,
            'meta_title' => 'Error: Unable to fetch page content',
            'meta_description' => '',
        ];
    }

    // Extrage meta title
    preg_match('/<title>(.*?)<\/title>/', $pageContent, $matches);
    $metaTitle = isset($matches[1]) ? $matches[1] : '';

    // Extrage meta description
    preg_match('/<meta\s+name="twitter:description"\s+content="(.*?)"\s*\/?>/', $pageContent, $matches);
    $metaDescription = isset($matches[1]) ? $matches[1] : '';

    return [
        'url' => $url,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
    ];
}

// Iterează prin fiecare URL și afișează meta title și meta description
foreach ($urls as $url) {
    $metaInfo = getMetaInfo($url);

    echo "<b>URL: <a href=". $metaInfo['url'] ." target='_blank'>" . $metaInfo['url'] . "</a></b><br>";
    echo "Meta Title: " . $metaInfo['meta_title'] . "<br>";
	
    echo "Meta Description: " . $metaInfo['meta_description'] . "<br>";
    echo "<br>";


	// Încărcați conținutul HTML al paginii web
	$html = file_get_contents($url);

	// Extrageți tagurile h1, h2 și h3
	$result = extractHeadings($html);

	// Afișați rezultatele
	foreach ($result as $tag => $headings) {
		echo strtoupper($tag) . "tags:<br>";
		foreach ($headings as $heading) {
			echo " - $heading<br>";
		}
			echo "<br>";
	}	
		
	
}


function extractHeadings($html) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $headings = ['h1' => [], 'h2' => [], 'h3' => []];

    foreach (['h1', 'h2', 'h3'] as $tag) {
        $headingNodes = $doc->getElementsByTagName($tag);

        foreach ($headingNodes as $headingNode) {
            $headings[$tag][] = $headingNode->nodeValue;
        }
    }

    return $headings;
}



?>