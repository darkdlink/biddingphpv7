<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\BiddingStatus;
use Illuminate\Http\Request;

class BiddingController extends Controller
{
    public function index()
    {
        $biddings = Bidding::with('status')->orderBy('closing_date', 'asc')->paginate(15);

        ob_start();
        include resource_path('views/bidding/index.php');
        $content = ob_get_clean();

        include resource_path('views/layouts/main.php');

        return $content;
    }

    public function create()
    {
        $statuses = BiddingStatus::all();

        ob_start();
        include resource_path('views/bidding/create.php');
        $content = ob_get_clean();

        include resource_path('views/layouts/main.php');

        return $content;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'notice_number' => 'required|string|max:100',
            'status_id' => 'required|exists:bidding_statuses,id',
            'entity' => 'required|string|max:255',
            'estimated_value' => 'nullable|numeric',
            'publication_date' => 'required|date',
            'opening_date' => 'required|date',
            'closing_date' => 'required|date|after:opening_date',
            'source_url' => 'nullable|url|max:255',
        ]);

        $bidding = Bidding::create($validated);

        return redirect()->route('biddings.show', $bidding->id);
    }

    public function show($id)
    {
        $bidding = Bidding::with(['status', 'proposals.company', 'documents'])->findOrFail($id);

        ob_start();
        include resource_path('views/bidding/show.php');
        $content = ob_get_clean();

        include resource_path('views/layouts/main.php');

        return $content;
    }

    public function edit($id)
    {
        $bidding = Bidding::findOrFail($id);
        $statuses = BiddingStatus::all();

        ob_start();
        include resource_path('views/bidding/edit.php');
        $content = ob_get_clean();

        include resource_path('views/layouts/main.php');

        return $content;
    }

    public function update(Request $request, $id)
    {
        $bidding = Bidding::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'notice_number' => 'required|string|max:100',
            'status_id' => 'required|exists:bidding_statuses,id',
            'entity' => 'required|string|max:255',
            'estimated_value' => 'nullable|numeric',
            'publication_date' => 'required|date',
            'opening_date' => 'required|date',
            'closing_date' => 'required|date|after:opening_date',
            'source_url' => 'nullable|url|max:255',
        ]);

        $bidding->update($validated);

        return redirect()->route('biddings.show', $bidding->id);
    }

    public function destroy($id)
    {
        $bidding = Bidding::findOrFail($id);
        $bidding->delete();

        return redirect()->route('biddings.index');
    }
}
