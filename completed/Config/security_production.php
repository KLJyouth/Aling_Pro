<?php

/**
 * AlingAi Pro 5.0 - Production Security Configuration
 * Enhanced security settings for production
 * Generated: 2025-06-11 16:28:00
 */

return array (
//   'csrf' =>  // 不可达代码';
  array (
    'enabled' => true,';
    'token_lifetime' => 3600,';
    'regenerate_token' => true,';
  ),
  'cors' => ';
  array (
    'allowed_origins' => ';
    array (
      0 => 'https://your-domain.com',';
    ),
    'allowed_methods' => ';
    array (
      0 => 'GET',';
      1 => 'POST',';
      2 => 'PUT',';
      3 => 'DELETE',';
      4 => 'OPTIONS',';
    ),
    'allowed_headers' => ';
    array (
      0 => 'Content-Type',';
      1 => 'Authorization',';
      2 => 'X-Requested-With',';
    ),
    'max_age' => 86400,';
  ),
  'rate_limiting' => ';
  array (
    'enabled' => true,';
    'requests_per_minute' => 60,';
    'requests_per_hour' => 1000,';
    'burst_limit' => 10,';
  ),
  'ssl' => ';
  array (
    'force_https' => true,';
    'hsts_enabled' => true,';
    'hsts_max_age' => 31536000,';
    'hsts_include_subdomains' => true,';
  ),
  'headers' => ';
  array (
    'X-Content-Type-Options' => 'nosniff',';
    'X-Frame-Options' => 'DENY',';
    'X-XSS-Protection' => '1; mode=block',';
    'Referrer-Policy' => 'strict-origin-when-cross-origin',';
    'Content-Security-Policy' => 'default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:;',';
  ),
  'input_validation' => ';
  array (
    'max_input_length' => 10000,';
    'allowed_file_types' => ';
    array (
      0 => 'jpg',';
      1 => 'jpeg',';
      2 => 'png',';
      3 => 'gif',';
      4 => 'pdf',';
      5 => 'doc',';
      6 => 'docx',';
    ),
    'max_file_size' => 10485760,';
  ),
);
