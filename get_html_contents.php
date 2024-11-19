<?php
$url = "https://rezortricks.com/bingo-bash-free-chips";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL

// Get HTML content
$htmlContent = file_get_contents($url);

// Check if content was fetched
if ($htmlContent === FALSE) {
    echo "Failed to fetch the URL.";
} else {
    echo "HTML Content:\n";
    echo htmlspecialchars($htmlContent); // Escape for better readability
}
?>
