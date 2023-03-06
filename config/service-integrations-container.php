<?php

return [


    /**
     * The service integrations model
     */
    'model' => \BlackSpot\ServiceIntegrationsContainer\ServiceIntegration::class,

    /**
     * The credentials of the integrated service
     */
    'payload_column' => 'payload',


    /**
     * The default services
     */
    'services' => [

        /**
         * The stripe settings
         */
        'stripe' => [
            'selectable_button' => [
              'text' => 'Stripe',
              'icon' => 'https://cdn.brandfolder.io/KGT2DTA4/at/x5bgtt3ktwsn5hxmck79g8xf/Stripe_wordmark_-_blurple_large.png?width=100&height=48',
              'background-color' => '#fff'
            ],
            'model' => [
              'name' => 'Stripe',
              'short_name' => 'str',
              'documentation_link' => '/stripe.com',
              'active' => true,
              'payload' => [
                'stripe_key' => null,
                'stripe_secret' => null,
                'stripe_webhook_secret' => null,
              ],
              'payload_labels' => [],
            ]
        ],

        /**
         * The system charges settings
         */
        'system_charges' => [ 
            'selectable_button' => [
                'text'             => 'System_Charges',
                'background-color' => '#fff'
                'icon'             => null,
            ],
            'model' => [
                'name' => 'System_Charges',
                'short_name' => 'sys_ch',
                'documentation_link' => config('app.url').'/docs/payments/system_charges',
                'active' => true,
                'payload' => [
                  'bank_name' => null,
                  'bank_account' => null,
                  'bank_card' => null,
                  'interbank_code' => null,
                ],
                'payload_labels' => [
                    'bank_name' => 'Nombre de banco',
                    'bank_account' => 'Cuenta bancaria (10 dígitos)',
                    'bank_card' => 'Tarjeta bancaria (16 dígitos)',
                    'interbank_code' => 'Código intervancario (18 dígitos)',
                ],
                //'payload_rules' => [], // not working yet
            ]
        ]
    ],

];