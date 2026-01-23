<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('BANKING_CACHE_ENABLED', true),
        'ttl' => env('BANKING_CACHE_TTL', 86400), // 24 hours
        'prefix' => 'banking_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bank List Source
    |--------------------------------------------------------------------------
    | Options: 'static', 'api'
    | 'static' uses built-in list
    | 'api' fetches from Central Bank API
    */
    'bank_list_source' => env('BANKING_LIST_SOURCE', 'static'),

    /*
    |--------------------------------------------------------------------------
    | Central Bank API
    |--------------------------------------------------------------------------
    */
    'bacen' => [
        'base_url' => 'https://brasilapi.com.br/api/banks/v1',
        'timeout' => env('BACEN_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | PIX Settings
    |--------------------------------------------------------------------------
    */
    'pix' => [
        'validate_cpf_cnpj' => env('PIX_VALIDATE_CPF_CNPJ', true),
        'validate_email' => env('PIX_VALIDATE_EMAIL', true),
        'validate_phone' => env('PIX_VALIDATE_PHONE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Boleto Settings
    |--------------------------------------------------------------------------
    */
    'boleto' => [
        'validate_checksum' => env('BOLETO_VALIDATE_CHECKSUM', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Major Brazilian Banks (Static List)
    |--------------------------------------------------------------------------
    */
    'banks' => [
        '001' => ['name' => 'Banco do Brasil S.A.', 'short_name' => 'BB', 'ispb' => '00000000'],
        '033' => ['name' => 'Banco Santander (Brasil) S.A.', 'short_name' => 'Santander', 'ispb' => '90400888'],
        '077' => ['name' => 'Banco Inter S.A.', 'short_name' => 'Inter', 'ispb' => '00416968'],
        '104' => ['name' => 'Caixa Econômica Federal', 'short_name' => 'Caixa', 'ispb' => '00360305'],
        '212' => ['name' => 'Banco Original S.A.', 'short_name' => 'Original', 'ispb' => '92894922'],
        '237' => ['name' => 'Banco Bradesco S.A.', 'short_name' => 'Bradesco', 'ispb' => '60746948'],
        '260' => ['name' => 'Nu Pagamentos S.A.', 'short_name' => 'Nubank', 'ispb' => '18236120'],
        '290' => ['name' => 'Pagseguro Internet S.A.', 'short_name' => 'PagSeguro', 'ispb' => '08561701'],
        '323' => ['name' => 'Mercado Pago', 'short_name' => 'MercadoPago', 'ispb' => '10573521'],
        '336' => ['name' => 'Banco C6 S.A.', 'short_name' => 'C6 Bank', 'ispb' => '31872495'],
        '341' => ['name' => 'Itaú Unibanco S.A.', 'short_name' => 'Itaú', 'ispb' => '60701190'],
        '380' => ['name' => 'PicPay Servicos S.A.', 'short_name' => 'PicPay', 'ispb' => '22896431'],
        '389' => ['name' => 'Banco Mercantil do Brasil S.A.', 'short_name' => 'Mercantil', 'ispb' => '17184037'],
        '399' => ['name' => 'Kirton Bank S.A.', 'short_name' => 'Kirton', 'ispb' => '01181521'],
        '422' => ['name' => 'Banco Safra S.A.', 'short_name' => 'Safra', 'ispb' => '58160789'],
        '633' => ['name' => 'Banco Rendimento S.A.', 'short_name' => 'Rendimento', 'ispb' => '68900810'],
        '652' => ['name' => 'Itaú Unibanco Holding S.A.', 'short_name' => 'Itaú Holding', 'ispb' => '60872504'],
        '655' => ['name' => 'Banco Votorantim S.A.', 'short_name' => 'Votorantim', 'ispb' => '59588111'],
        '735' => ['name' => 'Banco Neon S.A.', 'short_name' => 'Neon', 'ispb' => '10130673'],
        '745' => ['name' => 'Banco Citibank S.A.', 'short_name' => 'Citibank', 'ispb' => '33479023'],
        '748' => ['name' => 'Banco Cooperativo Sicredi S.A.', 'short_name' => 'Sicredi', 'ispb' => '01181521'],
        '756' => ['name' => 'Banco Cooperativo do Brasil S.A.', 'short_name' => 'Bancoob', 'ispb' => '02038232'],
    ],
];
