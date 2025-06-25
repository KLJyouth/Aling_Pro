<?php

/**
 * AlingAi Pro 5.0 - Production Security Configuration
 * Enhanced security settings for production
 * Generated: 2025-06-11 16:28:00
 */

return [
//   'csrf' =>  // 不可达代�?;
  [
    'enabled' => true,';
    'token_lifetime' => 3600,';
    'regenerate_token' => true,';
  ],
  'cors' => ';
  [
    'allowed_origins' => ';
    [
      0 => 'https://your-domain.com',';
    ],
    'allowed_methods' => ';
    [
      0 => 'GET',';
      1 => 'POST',';
      2 => 'PUT',';
      3 => 'DELETE',';
      4 => 'OPTIONS',';
    ],
    'allowed_headers' => ';
    [
      0 => 'Content-Type',';
      1 => 'Authorization',';
      2 => 'X-Requested-With',';
    ],
    'max_age' => 86400,';
  ],
  'rate_limiting' => ';
  [
    'enabled' => true,';
    'requests_per_minute' => 60,';
    'requests_per_hour' => 1000,';
    'burst_limit' => 10,';
  ],
  'ssl' => ';
  [
    'force_https' => true,';
    'hsts_enabled' => true,';
    'hsts_max_age' => 31536000,';
    'hsts_include_subdomains' => true,';
  ],
  'headers' => ';
  [
    'X-Content-Type-Options' => 'nosniff',';
    'X-Frame-Options' => 'DENY',';
    'X-XSS-Protection' => '1; mode=block',';
    'Referrer-Policy' => 'strict-origin-when-cross-origin',';
    'Content-Security-Policy' => 'default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:;',';
  ],
  'input_validation' => ';
  [
    'max_input_length' => 10000,';
    'allowed_file_types' => ';
    [
      0 => 'jpg',';
      1 => 'jpeg',';
      2 => 'png',';
      3 => 'gif',';
      4 => 'pdf',';
      5 => 'doc',';
      6 => 'docx',';
    ],
    'max_file_size' => 10485760,';
  ],
];
