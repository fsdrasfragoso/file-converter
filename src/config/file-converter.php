<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | LibreOffice Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração do LibreOffice para conversão de documentos
    |
    */
    'libreoffice' => [
        /*
        | Modo de operação:
        | - 'external': Usa LibreOffice externo (sidecar container)
        | - 'internal': Usa LibreOffice instalado localmente
        */
        'mode' => env('LIBREOFFICE_MODE', 'external'),
        
        /*
        | Configurações para modo external
        */
        'host' => env('LIBREOFFICE_HOST', '127.0.0.1'),
        'port' => env('LIBREOFFICE_PORT', 8100),
        'timeout' => env('LIBREOFFICE_TIMEOUT', 60),
        
        /*
        | Configurações para modo internal
        */
        'binary_path' => env('LIBREOFFICE_BINARY_PATH'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração de fallback quando o LibreOffice falha
    |
    */
    'fallback' => [
        'enabled' => env('LIBREOFFICE_FALLBACK_ENABLED', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Temporary Directory
    |--------------------------------------------------------------------------
    |
    | Diretório para arquivos temporários
    |
    */
    'temp_dir' => env('FILE_CONVERTER_TEMP_DIR', sys_get_temp_dir()),
];