<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Pousada'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'pt_BR'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],


    'pagination' => 'bootstrap-4', // This is the default pagination template that comes with Laravel


    'enums' => [
        'tipos_usuario' => [
            'administrador' => 'Administrador',
            'cliente' => 'Cliente',
        ],
        'permissoes_plano' => [
            // Reservas
            'visualizar_reservas' => 'Visualizar Reservas',
            'gerenciar_reservas' => 'Gerenciar Reservas',
            // Quartos
            'visualizar_quartos' => 'Visualizar Quartos',
            'gerenciar_quartos' => 'Gerenciar Quartos',
            // Clientes
            'visualizar_clientes' => 'Visualizar Clientes',
            'gerenciar_clientes' => 'Gerenciar Clientes',
            // Usuários
            'visualizar_usuarios' => 'Visualizar Usuários',
            'gerenciar_usuarios' => 'Gerenciar Usuários',
            // Grupos de Usuários
            'gerenciar_grupos' => 'Gerenciar Grupos de Usuários',
            // Produtos
            'visualizar_produtos' => 'Visualizar Produtos',
            'gerenciar_produtos' => 'Gerenciar Produtos',
            // Estoque
            'visualizar_estoque' => 'Visualizar Estoque',
            'gerenciar_estoque' => 'Gerenciar Estoque',
            // Despesas
            'visualizar_despesas' => 'Visualizar Despesas',
            'gerenciar_despesas' => 'Gerenciar Despesas',
            // Relatórios
            'visualizar_relatorios' => 'Visualizar Relatórios',
            // Impressoras
            'gerenciar_impressoras' => 'Gerenciar Impressoras',
            // Fornecedores
            'gerenciar_fornecedores' => 'Gerenciar Fornecedores',
            // Bar
            'visualizar_bar' => 'Visualizar Bar',
            'gerenciar_bar' => 'Gerenciar Bar',
            // Financeiro
            'visualizar_financeiro' => 'Visualizar Financeiro',
            'gerenciar_financeiro' => 'Gerenciar Financeiro',
        ],
    ],



    'messages' => [
        'logged_in' => 'Login efetuado com sucesso.',
        'no_rows' => 'Nenhum registro encontrado.',
        'insert' => 'Dados inseridos com sucesso!',
        'update' => 'Dados atualizados com sucesso!',
        'delete' => 'Dados excluídos com sucesso!',
        'send_new_password' => 'Nova senha enviada com sucesso!',
    ],
];
