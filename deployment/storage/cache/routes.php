<?php return array (
  'GET' => 
  array (
    '/' => 'HomeController@index',
    '/api/system/status' => 'SystemController@status',
    '/api/user/info' => 'UserController@info',
  ),
  'POST' => 
  array (
    '/api/chat/send' => 'ChatController@send',
    '/api/upload' => 'FileController@upload',
  ),
);