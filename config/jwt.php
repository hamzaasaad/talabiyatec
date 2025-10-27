<?php

return [


   
    'secret' => env('JWT_SECRET'),

    
    'access_ttl' => (int) env('ACCESS_TOKEN_TTL', 15),


    'refresh_ttl' => (int) env('REFRESH_TOKEN_TTL', 30),

   
    'issuer' => env('APP_URL', 'http://localhost'),

    
    'audience' => env('APP_URL', 'http://localhost'),

    
    'algo' => 'HS256',

  
    'leeway' => 10, 

];
