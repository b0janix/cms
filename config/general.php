<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination Limit
    |--------------------------------------------------------------------------
    |
    | This value gives the maximum number of records a request can return
    |
    */
    'limit' => env('PAGINATION_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | Page Number
    |--------------------------------------------------------------------------
    |
    | This value gives the page number
    |
    */
    'page' => env('DEFAULT_PAGE', 1),

    /*
    |--------------------------------------------------------------------------
    | Random Words
    |--------------------------------------------------------------------------
    |
    | Define random words for content insertion
    |
    */
    'random_words' => "Cool,Strange,Funny,Laughing,Nice,Awesome,Great,Horrible,Beautiful,PHP,Vegeta,Italy,Joost",

];
