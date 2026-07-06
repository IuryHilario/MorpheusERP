<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RehashSenhasUsuarios extends Command
{
    protected $signature = 'usuarios:rehash-senhas {--dry-run : Apenas lista quem seria alterado, sem gravar nada}';

    protected $description = 'Rehasheia com bcrypt qualquer senha de usuário que ainda esteja em texto puro';

    public function handle(): int
    {
        $usuarios = DB::table('usuarios')->select('id_Usuario', 'nome_Usuario', 'senha')->get();

        $pendentes = $usuarios->filter(fn ($usuario) => substr((string) $usuario->senha, 0, 4) !== '$2y$');

        if ($pendentes->isEmpty()) {
            $this->info('Nenhuma senha em texto puro encontrada. Nada a fazer.');
            return self::SUCCESS;
        }

        $this->warn("{$pendentes->count()} usuário(s) com senha em texto puro encontrados:");
        foreach ($pendentes as $usuario) {
            $this->line("  - #{$usuario->id_Usuario} {$usuario->nome_Usuario}");
        }

        if ($this->option('dry-run')) {
            $this->info('Modo --dry-run: nenhuma alteração foi feita.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Confirma o rehash dessas senhas? Faça um backup do banco antes de continuar.')) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        foreach ($pendentes as $usuario) {
            DB::table('usuarios')
                ->where('id_Usuario', $usuario->id_Usuario)
                ->update([
                    'senha' => Hash::make($usuario->senha),
                    'updated_at' => now(),
                ]);
        }

        $this->info("{$pendentes->count()} senha(s) rehasheada(s) com sucesso.");
        return self::SUCCESS;
    }
}
