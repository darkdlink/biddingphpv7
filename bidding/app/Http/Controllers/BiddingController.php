<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\Entity;
use App\Services\BiddingApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BiddingController extends Controller
{
    protected $apiService;

    public function __construct(BiddingApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        $query = Bidding::with('entity');

        // Aplicar filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $orderBy = $request->order_by ?? 'created_at';
        $orderDir = $request->order_dir ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        // Paginação
        $biddings = $query->paginate($request->per_page ?? 15);

        return response()->json($biddings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'reference_number' => 'required|string|max:100',
            'entity_id' => 'required|exists:entities,id',
            'status' => 'required|string|max:50',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:opening_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bidding = Bidding::create($request->all());

        return response()->json($bidding, 201);
    }

    public function show($id)
    {
        $bidding = Bidding::with(['entity', 'proposals', 'documents'])->findOrFail($id);
        return response()->json($bidding);
    }

    public function update(Request $request, $id)
    {
        $bidding = Bidding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'reference_number' => 'sometimes|required|string|max:100',
            'entity_id' => 'sometimes|required|exists:entities,id',
            'status' => 'sometimes|required|string|max:50',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:opening_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bidding->update($request->all());

        return response()->json($bidding);
    }

    public function destroy($id)
    {
        $bidding = Bidding::findOrFail($id);
        $bidding->delete();

        return response()->json(null, 204);
    }

    public function fetchFromApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:1|max:90',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $filters = [
            'published_after' => now()->subDays($request->days ?? 7)->format('Y-m-d'),
            'status' => $request->status ?? 'open',
        ];

        $apiData = $this->apiService->fetchBiddings($filters);

        if (isset($apiData['error'])) {
            return response()->json(['error' => $apiData['error']], 500);
        }

        $result = $this->apiService->saveBiddingsFromApi($apiData);

        return response()->json($result);
    }
}
