<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$usuarios = \App\Models\Usuario::all();

foreach ($usuarios as $u) {
    $u->notificacoes()->createMany([
        [
            'titulo' => 'Boas vindas',
            'mensagem' => 'Bem-vindo ao novo painel! Agora suas notificações são em tempo real.',
            'tipo' => 'success',
            'created_at' => now()->subMinutes(5)
        ],
        [
            'titulo' => 'Alerta de Segurança',
            'mensagem' => 'Nova tentativa de login detectada em um novo dispositivo.',
            'tipo' => 'warning',
            'created_at' => now()->subHours(2)
        ],
        [
            'titulo' => 'Atualização do Sistema',
            'mensagem' => 'Novas funcionalidades foram liberadas no Módulo Comercial.',
            'tipo' => 'info',
            'created_at' => now()->subDays(1)
        ]
    ]);
}

echo "Notificacoes criadas com sucesso!\n";
