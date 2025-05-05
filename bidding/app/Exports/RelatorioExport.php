<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RelatorioExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $dados;
    protected $cabecalho;
    protected $titulo;

    /**
     * @param array $dados
     * @param array $cabecalho
     * @param string $titulo
     */
    public function __construct($dados, $cabecalho, $titulo)
    {
        $this->dados = $dados;
        $this->cabecalho = $cabecalho;
        $this->titulo = $titulo;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->dados);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->cabecalho;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->titulo;
    }
}
