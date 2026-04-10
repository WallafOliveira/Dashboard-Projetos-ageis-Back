<?php

namespace App\Services;

use App\Models\Relatorio;

class RelatoriosService
{
    public function buscaTodosRelatorios()
    {
        return Relatorio::all();
    }

    public function criarRelatorio(array $dados)
    {
        return Relatorio::create($dados);
    }

    public function atualizarRelatorio(Relatorio $relatorio, array $dados)
    {
        $relatorio->update($dados);
        return $relatorio;
    }

    public function deletarRelatorio(Relatorio $relatorio)
    {
        $relatorio->delete();
    }
}
