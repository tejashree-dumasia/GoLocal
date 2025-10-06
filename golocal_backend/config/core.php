<?php
// Set your default timezone to avoid potential issues
date_default_timezone_set('Asia/Kolkata');

// --- YOUR JWT SECRET KEY ---
// Generate a long, random string for this.
$secret_key = "k#8wP@2zV!t$5sF&gB*qY^7hN(3jM)Lp";


// --- JWT SETTINGS ---
$issuer_claim = "http://localhost/golocal"; // The issuer of the token
$audience_claim = "http://localhost";       // The audience of the token
$issuedat_claim = time();                   // Issued at timestamp
$notbefore_claim = $issuedat_claim;         // Token is valid from this timestamp
$expire_claim = $issuedat_claim + (3600 * 12);     // Token expires in 1 hour (3600 seconds)

?>