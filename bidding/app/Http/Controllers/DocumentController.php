<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Bidding;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('documentable', 'uploader');

        // Filtrar por tipo de entidade relacionada
        if ($request->has('documentable_type') && $request->has('documentable_id')) {
            $query->where('documentable_type', $request->documentable_type)
                  ->where('documentable_id', $request->documentable_id);
        }

        // Paginação
        $documents = $query->paginate($request->per_page ?? 15);

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'documentable_type' => 'required|in:App\\Models\\Bidding,App\\Models\\Proposal',
            'documentable_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se a entidade relacionada existe
        $model = null;
        if ($request->documentable_type === 'App\\Models\\Bidding') {
            $model = Bidding::find($request->documentable_id);
        } elseif ($request->documentable_type === 'App\\Models\\Proposal') {
            $model = Proposal::find($request->documentable_id);
        }

        if (!$model) {
            return response()->json(['error' => 'Related entity not found'], 404);
        }

        // Armazenar o arquivo
        $file = $request->file('file');
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        // Criar documento
        $document = new Document([
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        $model->documents()->save($document);

        return response()->json($document, 201);
    }

    public function show($id)
    {
        $document = Document::with('documentable', 'uploader')->findOrFail($id);
        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Atualizar informações básicas
        if ($request->has('name')) {
            $document->name = $request->name;
        }

        if ($request->has('description')) {
            $document->description = $request->description;
        }

        // Se um novo arquivo foi enviado
        if ($request->hasFile('file')) {
            // Remover arquivo antigo
            Storage::disk('public')->delete($document->file_path);

            // Armazenar novo arquivo
            $file = $request->file('file');
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            $document->file_path = $filePath;
            $document->file_type = $file->getClientMimeType();
            $document->file_size = $file->getSize();
        }

        $document->save();

        return response()->json($document);
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Remover arquivo físico
        Storage::disk('public')->delete($document->file_path);

        // Remover registro
        $document->delete();

        return response()->json(null, 204);
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);

        $path = Storage::disk('public')->path($document->file_path);

        return response()->download($path, $document->name . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }
}
