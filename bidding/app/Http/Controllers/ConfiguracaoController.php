<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\TesteEmail;
use Carbon\Carbon;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $configuracoes = DB::table('configuracoes')->pluck('valor', 'chave')->toArray();

        // Listar backups
        $backups = [];

        if (Storage::disk('local')->exists('backups')) {
            $files = Storage::disk('local')->files('backups');

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip' || pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $backups[] = [
                        'id' => md5($file),
                        'nome' => basename($file),
                        'data' => Storage::disk('local')->lastModified($file),
                        'tamanho' => $this->formatFileSize(Storage::disk('local')->size($file))
                    ];
                }
            }

            // Ordenar por data (mais recente primeiro)
            usort($backups, function($a, $b) {
                return $b['data'] - $a['data'];
            });
        }

        return view('configuracoes.index', [
            'configuracoes' => $configuracoes,
            'backups' => $backups
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_url' => 'required|url',
            'api_timeout' => 'required|integer|min:10|max:300',
            'itens_por_pagina' => 'required|integer|min:5|max:100',
            'email_host' => 'nullable|string',
            'email_port' => 'nullable|integer',
            'email_username' => 'nullable|string',
            'email_password' => 'nullable|string',
            'email_from_address' => 'nullable|email',
            'email_from_name' => 'nullable|string',
            'notificar_novas_licitacoes' => 'boolean',
            'notificar_encerramento' => 'boolean',
            'dias_antecedencia' => 'required|integer|min:1|max:15',
            'emails_notificacao' => 'nullable|string',
            'nome_empresa' => 'required|string',
            'cnpj_empresa' => 'nullable|string',
            'logo_empresa' => 'nullable|image|max:2048',
            'intervalo_sincronizacao' => 'required|integer|min:0|max:24',
            'tema' => 'required|in:light,dark'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Salvar logo se fornecido
        if ($request->hasFile('logo_empresa')) {
            $logo = $request->file('logo_empresa');
            $logoPath = $logo->store('logos', 'public');

            $this->salvarConfiguracao('logo_empresa', $logoPath);
        }

        // Salvar todas as configurações
        foreach ($request->except(['_token', 'logo_empresa']) as $chave => $valor) {
            $this->salvarConfiguracao($chave, $valor);
        }

        return response()->json([
            'success' => true,
            'message' => 'Configurações salvas com sucesso!'
        ]);
    }

    public function testarEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_driver' => 'required|in:smtp,sendmail,mailgun',
            'email_host' => 'nullable|string',
            'email_port' => 'nullable|integer',
            'email_encryption' => 'nullable|in:tls,ssl,none',
            'email_username' => 'nullable|string',
            'email_password' => 'nullable|string',
            'email_from_address' => 'required|email',
            'email_from_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos para teste de email.'
            ], 422);
        }

        // Configurar email temporariamente para o teste
        config([
            'mail.default' => $request->email_driver,
            'mail.mailers.smtp.host' => $request->email_host,
            'mail.mailers.smtp.port' => $request->email_port,
            'mail.mailers.smtp.encryption' => $request->email_encryption === 'none' ? null : $request->email_encryption,
            'mail.mailers.smtp.username' => $request->email_username,
            'mail.mailers.smtp.password' => $request->email_password,
            'mail.from.address' => $request->email_from_address,
            'mail.from.name' => $request->email_from_name
        ]);

        try {
            Mail::to($request->email_username)
                ->send(new TesteEmail());

            return response()->json([
                'success' => true,
                'message' => 'Email de teste enviado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarBackup()
    {
        try {
            // Criar diretório de backups se não existir
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            // Nome do arquivo de backup
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = 'backup_' . $timestamp . '.sql';
            $path = 'backups/' . $filename;

            // Comando para fazer dump do banco de dados
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            $dumpCommand = sprintf(
                'mysqldump -h %s -P %s -u %s --password=%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                storage_path('app/' . $path)
            );

            // Executar comando
            exec($dumpCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("Erro ao executar dump do banco de dados");
            }

            // Atualizar data do último backup
            $this->salvarConfiguracao('ultimo_backup', Carbon::now());

            return response()->json([
                'success' => true,
                'message' => 'Backup gerado com sucesso!',
                'arquivo' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadBackup($id)
    {
        $backups = Storage::disk('local')->files('backups');

        foreach ($backups as $backup) {
            if (md5($backup) === $id) {
                return Storage::disk('local')->download($backup);
            }
        }

        abort(404, 'Backup não encontrado');
    }

    public function restaurarBackup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'arquivo_backup' => 'required|file|mimes:zip,sql|max:50000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo de backup inválido'
            ], 422);
        }

        try {
            $arquivo = $request->file('arquivo_backup');
            $extension = $arquivo->getClientOriginalExtension();

            if ($extension === 'sql') {
                // Salvar arquivo temporariamente
                $path = $arquivo->storeAs('temp', 'backup_temp.sql', 'local');

                // Comando para restaurar banco de dados
                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port');
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');

                $restoreCommand = sprintf(
                    'mysql -h %s -P %s -u %s --password=%s %s < %s',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    storage_path('app/' . $path)
                );

                // Executar comando
                exec($restoreCommand, $output, $returnVar);

                if ($returnVar !== 0) {
                    throw new \Exception("Erro ao restaurar banco de dados");
                }

                // Remover arquivo temporário
                Storage::disk('local')->delete($path);
            } else {
                // TODO: Implementar restauração de backup ZIP
                throw new \Exception("Restauração de arquivos ZIP não implementada");
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup restaurado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function excluirBackup($id)
    {
        $backups = Storage::disk('local')->files('backups');

        foreach ($backups as $backup) {
            if (md5($backup) === $id) {
                Storage::disk('local')->delete($backup);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup excluído com sucesso!'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Backup não encontrado'
        ], 404);
    }

    private function salvarConfiguracao($chave, $valor)
    {
        DB::table('configuracoes')->updateOrInsert(
            ['chave' => $chave],
            ['valor' => $valor, 'updated_at' => now()]
        );
    }

    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $size = max($size, 0);
        $pow = floor(($size ? log($size) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $size /= pow(1024, $pow);

        return round($size, 2) . ' ' . $units[$pow];
    }
}
