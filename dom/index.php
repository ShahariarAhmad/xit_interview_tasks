<?php
// Set maximum execution time to 50000 seconds
set_time_limit(50000);

// Include the simple_html_dom library
include('simplehtmldom/simple_html_dom.php');

// Function to generate the fetch URL
function generateFetchURL($cat, $page = 1): string
{
    return "https://yourpetpa.com.au/collections/{$cat}?page={$page}&sort_by=title-ascending";
}

// Function to make a cURL request and return the response
function curlResponse($urlData) 
{
    $url = $urlData;
    $cookieFilePath = __DIR__ . '/cookie.txt';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFilePath);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFilePath);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    $response = curl_exec($ch);
    if ($response === false) {
        die('Error fetching data.');
    } else {
        return $response;
    }
}

// Function to create and return a simple_html_dom object from a URL
function domObject($url): object
{
    $html = new simple_html_dom();
    return $html->load(curlResponse($url));
}

// Function to calculate the total number of pages for a given category
function totalPages($p): int
{
    $obj = domObject(generateFetchURL($p, 1));
    $pages = 1;
    foreach ($obj->find('div.pagination span.page') as $e) {
        $pages =  $e->plaintext;
    }
    return $pages;
}

// Function to get the category name from the URI
function getCategoryName($uri): string
{
    $url = $uri;
    $pathParts = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
    $categoryIndex = array_search('collections', $pathParts);
    if ($categoryIndex !== false && isset($pathParts[$categoryIndex + 1])) {
        $category = str_replace('-', ' ', $pathParts[$categoryIndex + 1]);
        return ucwords($category);
    } else {
        return "N/A";
    }
}

// Array of categories to scrape
$categories = [
    'cat',
    'dog',
    'shop-other',
    'prescription-medication-script-required'
];

// Array to store scraped links
$scraped_links = [];

echo "Scraping all product links...\n";

// Loop through each category
foreach ($categories as  $c) {
    $totalPages = totalPages($c);
    echo "\nScraping link of $c category \n";

    // Loop through each page of the category
    for ($j = 1; $j <= $totalPages; $j++) {
        echo "\rProcessed :" . $j . "/" . $totalPages;

        // Get simple_html_dom object for the current page
        $html = domObject(generateFetchURL($c, $j));

        // Loop through each product link on the page
        foreach ($html->find('div.larger-row') as $element) {
            $data = $element->innertext;
            $innerHtmlObject = new simple_html_dom();
            $innerHtmlObject->load($data);
            $product = $innerHtmlObject->find('div.product-block');
            $i = 0;

            // Extract and store each product link
            foreach ($product as $d) {
                $url = 'https://yourpetpa.com.au' . $element->find('a.product-block__title-link', $i)->getAttribute('href');
                $i++;
                array_push($scraped_links, [$url]);
            }

            // Clear innerHtmlObject to free up resources
            $innerHtmlObject->clear();
            unset($innerHtmlObject);
        }
    }
}

echo "\n";

// Clear the main HTML object to free up resources
$html->clear();
unset($html);

// Array to store product information
$products = [];
$a = 1;

// Loop through each scraped link to gather product information
foreach ($scraped_links as $u) {
    $utp = $u[0];
    $hypertext = new simple_html_dom();
    $hypertext->load(curlResponse($utp));
    $hypertext->find('main#MainContent');

    echo "\rCollecting each product's information {$a} /" . count($scraped_links) . "...";

    // Loop through each product on the page
    foreach ($hypertext->find('main#MainContent') as  $e) {
        $imageElement = $e->find('div.rimage-wrapper img', 0);
        if ($imageElement && $imageElement->hasAttribute('data-src')) {
            $imageUrl = 'https:' . $imageElement->getAttribute('data-src');
        } else {
            $imageUrl = 'N/A'; 
        }

        // Create an array with product information and add it to the products array
        $pushToArray = [
            'id' => count($products) + 1,
            'Title' => $e->find('h3', 0)->plaintext,
            'Description' => $e->find('div.product__description_full--width', 0)->plaintext,
            'Category' => getCategoryName($utp), 
            'Price' => $e->find('span.heading-font-4', 0)->plaintext,
            'URL' => $utp, 
            'ImageURL' => $imageUrl, 
        ];
        array_push($products, $pushToArray);
    }

    // Increment the product counter
    $a++;

    // Clear the hypertext object to free up resources
    $hypertext->clear();
    unset($hypertext);
}

echo "\nAll products information scraped successfully...";

// Create a CSV file to store the product information
echo "\nGenerating CSV File...";
$csvFileName = 'products.csv';
$file = fopen($csvFileName, 'w');
fputcsv($file, array_keys($products[0]));
$m = 1;

// Write product information to the CSV file
foreach ($products as $row) {
    fputcsv($file, $row);
    echo "\rProcessed " . $m . '/ ' . count($products);
    $m++;
}

// Close the CSV file
fclose($file);

echo "\nDone, CSV generated...\n";
?>
