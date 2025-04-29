<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        $query = Entity::query();

        // Aplicar filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Paginação
        $entities = $query->paginate($request->per_page ?? 15);

        return response()->json($entities);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:20',
            'type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entity = Entity::create($request->all());

        return response()->json($entity, 201);
    }

    public function show($id)
    {
        $entity = Entity::with('biddings')->findOrFail($id);
        return response()->json($entity);
    }

    public function update(Request $request, $id)
    {
        $entity = Entity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'document' => 'nullable|string|max:20',
            'type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entity->update($request->all());

        return response()->json($entity);
    }

    public function destroy($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->delete();

        return response()->json(null, 204);
    }
}
