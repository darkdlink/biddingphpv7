<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licitacao extends Model
{
    protected $table = 'licitacoes';

    protected $fillable = [
        'numero_controle_pncp', 'orgao_entidade', 'unidade_orgao', 'ano_compra',
        'sequencial_compra', 'numero_compra', 'objeto_compra', 'modalidade_nome',
        'modo_disputa_nome', 'valor_total_estimado', 'situacao_compra_nome',
        'data_inclusao', 'data_publicacao_pncp', 'data_abertura_proposta',
        'data_encerramento_proposta', 'link_sistema_origem', 'is_srp',
        'uf', 'municipio', 'cnpj_orgao', 'analisada', 'interesse'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_publicacao_pncp' => 'datetime',
        'data_abertura_proposta' => 'datetime',
        'data_encerramento_proposta' => 'datetime',
        'is_srp' => 'boolean',
        'analisada' => 'boolean',
        'interesse' => 'boolean',
    ];

    public function propostas()
    {
        return $this->hasMany(Proposta::class);
    }

    public function acompanhamentos()
    {
        return $this->hasMany(Acompanhamento::class);
    }
}
