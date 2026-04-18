<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ConsultaGraficoRequest extends FormRequest
{
    private const CHAVES_FILTRO_DOMINIO_POR_GRAFICO = [
        'financeRevenueChart' => ['cenario', 'centroCustoIds', 'contaContabilIds', 'tipoMovimento', 'clienteIds', 'fornecedorIds', 'faixaAtraso'],
        'commercialPipelineChart' => ['etapaFunil', 'canal', 'vendedorIds', 'equipeIds', 'segmento', 'statusOportunidade', 'origemLead'],
        'operationsEfficiencyChart' => ['plantaIds', 'linhaIds', 'turno', 'familiaProdutoIds', 'causaPerdaIds', 'ordemProducao'],
        'logisticsDeliveryChart' => ['cdIds', 'rota', 'transportadoraIds', 'classeSku', 'statusEntrega', 'clienteRegiao'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periodo' => 'required|array',
            'periodo.de' => 'required|date',
            'periodo.ate' => 'required|date|after_or_equal:periodo.de',
            'periodo.granularidade' => 'required|in:dia,semana,mes',

            'filtrosGlobais' => 'required|array',
            'filtrosGlobais.empresaId' => 'required|string|max:100',
            'filtrosGlobais.unidadeIds' => 'required|array|min:1',
            'filtrosGlobais.unidadeIds.*' => 'required|string|max:100',
            'filtrosGlobais.timezone' => 'required|timezone',
            'filtrosGlobais.comparacao' => 'nullable|in:vs_periodo_anterior,vs_meta,vs_ano_anterior',

            'filtrosDominio' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'periodo.required' => 'O bloco periodo e obrigatorio.',
            'periodo.de.required' => 'O campo periodo.de e obrigatorio.',
            'periodo.ate.required' => 'O campo periodo.ate e obrigatorio.',
            'periodo.ate.after_or_equal' => 'O campo periodo.ate deve ser maior ou igual a periodo.de.',
            'periodo.granularidade.in' => 'A granularidade aceita apenas dia, semana ou mes.',
            'filtrosGlobais.required' => 'O bloco filtrosGlobais e obrigatorio.',
            'filtrosGlobais.empresaId.required' => 'O campo filtrosGlobais.empresaId e obrigatorio.',
            'filtrosGlobais.unidadeIds.required' => 'O campo filtrosGlobais.unidadeIds e obrigatorio.',
            'filtrosGlobais.unidadeIds.min' => 'O campo filtrosGlobais.unidadeIds deve conter ao menos uma unidade.',
            'filtrosGlobais.timezone.required' => 'O campo filtrosGlobais.timezone e obrigatorio.',
            'filtrosGlobais.timezone.timezone' => 'O campo filtrosGlobais.timezone deve ser um timezone valido.',
            'filtrosGlobais.comparacao.in' => 'A comparacao aceita apenas vs_periodo_anterior, vs_meta ou vs_ano_anterior.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $chaveGrafico = (string) ($this->route('chaveGrafico') ?? $this->route('chartKey'));
            $chavesPermitidas = self::CHAVES_FILTRO_DOMINIO_POR_GRAFICO[$chaveGrafico] ?? [];
            $filtrosDominio = (array) $this->input('filtrosDominio', []);

            foreach ($filtrosDominio as $chave => $valor) {
                if (! in_array($chave, $chavesPermitidas, true)) {
                    $validator->errors()->add(
                        'filtrosDominio.'.$chave,
                        'Filtro de dominio invalido para o chart informado.'
                    );
                }
            }
        });
    }
}
