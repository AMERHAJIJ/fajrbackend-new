<?php

return [
    'spreadsheet_id' => '1Nhkvgc4Ggbt7B96B9XUdbKe3ejzzW_7oq3-7eP9GgAg',
    'sheet_name' => 'Sheet1',
    'service_account_path' => storage_path('app/public/google-service-account.json'),
    'scopes' => [
        'https://www.googleapis.com/auth/spreadsheets',
        'https://www.googleapis.com/auth/drive',
    ],
];
