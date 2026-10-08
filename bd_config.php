<?php
/**
 * Central configuration for biteanddiet.in.
 *
 * This file MUST live OUTSIDE the web root, otherwise the API keys,
 * database password and admin password can be downloaded by anyone.
 *
 * Local XAMPP : C:\xampp\htdocs\bd_config.php
 * Production  : /home/biteandd/bd_config.php
 *
 * Read it through the loader, never directly:
 *     require_once __DIR__ . '/app_config.php';
 *     cfg('google_api_key', '');
 *
 * Never commit this file to a public repository.
 */

return [
    'google_api_key' => 'AIzaSyADQiWEajX298hN1C21P8xgjpCyNi5v6As',
    'blogger_blog_id' => '7894093648055121041',
    'youtube_channel_id' => 'UCwl1Lbkl0PhwYm8j3j8fo1A',
    'youtube_upload_playlist_id' => 'UUwl1Lbkl0PhwYm8j3j8fo1A',
    'db_host' => 'localhost',
    'db_name' => 'biteandd_u299993858_Data',
    'db_user' => 'biteandd_u299993858_Shashi',
    'db_password' => 'Shashi@742744',
    'admin_username' => 'yourbitemydiet@gmail.com',
    'admin_password' => 'BiteAndDiet@2319',
    'instagram_access_token' => 'IGQWRNQU85ZAkJJVXdYTXBLUjFoSXI2ZAlZAJbGRTU2NOMmdOcmVJSFJhclJkbnpwX1dFZAkdHRzJUNHprdjhyYTRzVlprckRpVkc5MDRxeGphLWpON19hNG5PY29Qc3ZAFbXpnMXR0emZAXWEd2eUprMENMWXRUcjV2MEEZD',
    'instagram_user_id' => '8544913635530921',
    'contact_phone_display' => '+91 88265 49878',
    'contact_phone_e164' => '+918826549878',
    'whatsapp_number' => '918826549878',
    'business_latitude' => '28.4898737190245',
    'business_longitude' => '77.09474899925662',
    'form_secret' => '21eb5a641d5c5fe53e5c46c6a37b33784e08e7a94cc79a295cdd1607fbad32b8',
    'sitemap_cron_token' => '2abcd1bbe4773168bda1829c3c4d8c5486147f696be027a6',
];
