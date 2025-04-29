<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with('bidding.entity');

        // Filtrar por licitação específica
        if ($request->has('bidding_id')) {
            $query->where('bidding_id', $request->bidding_id);
        }

        // Filtrar por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Ordenação
        $orderBy = $request->order_by ?? 'created_at';
        $orderDir = $request->order_dir ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        // Paginação
        $proposals = $query->paginate($request->per_page ?? 15);

        return response()->json($proposals);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bidding_id' => 'required|exists:biddings,id',
            'status' => 'required|string|max:50',
            'proposed_value' => 'required|numeric|min:0',
            'cost_estimate' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se a licitação está aberta para propostas
        $bidding = Bidding::findOrFail($request->bidding_id);
        if ($bidding->status !== 'open' && $bidding->status !== 'active') {
            return response()->json(['error' => 'This bidding is no longer accepting proposals'], 422);
        }

        $proposal = Proposal::create($request->all());

        return response()->json($proposal, 201);
    }

    public function show($id)
    {
        $proposal = Proposal::with(['bidding.entity', 'documents'])->findOrFail($id);
        return response()->json($proposal);
    }

    public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:50',
            'proposed_value' => 'sometimes|required|numeric|min:0',
            'cost_estimate' => 'nullable|numeric|min:0',
            'profit_margin' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar se a proposta pode ser editada
        if ($proposal->status === 'submitted' && $request->has('proposed_value')) {
            $bidding = $proposal->bidding;
            if ($bidding && ($bidding->status !== 'open' && $bidding->status !== 'active')) {
                return response()->json(['error' => 'Cannot edit the value of a submitted proposal for a closed bidding'], 422);
            }
        }

        $proposal->update($request->all());

        return response()->json($proposal);
    }

    public function destroy($id)
    {
        $proposal = Proposal::findOrFail($id);

        // Verificar se a proposta pode ser excluída
        if ($proposal->status === 'submitted' || $proposal->status === 'winner') {
            return response()->json(['error' => 'Cannot delete submitted or winning proposals'], 422);
        }

        $proposal->delete();

        return response()->json(null, 204);
    }

    public function calculateProfit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'proposed_value' => 'required|numeric|min:0',
            'cost_estimate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $proposedValue = $request->proposed_value;
        $costEstimate = $request->cost_estimate;

        $profit = $proposedValue - $costEstimate;
        $profitMargin = ($proposedValue > 0) ? ($profit / $proposedValue) * 100 : 0;

        return response()->json([
            'profit' => $profit,
            'profit_margin' => $profitMargin,
        ]);
    }
}
