<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdfDisbursement;
use Illuminate\Http\Request;

class CdfDisbursementController extends Controller
{
    /**
     * Display a listing of CDF disbursements.
     */
    public function index(Request $request)
    {
        $query = CdfDisbursement::with('recordedBy')
            ->latest()
            ->when($request->filled('constituency'), function ($q) use ($request) {
                $q->where('constituency', 'like', '%' . $request->constituency . '%');
            });

        $disbursements = $query->paginate(20)->withQueryString();

        return view('admin.cdf-disbursements.index', compact('disbursements'));
    }

    /**
     * Show the form for creating a new CDF disbursement.
     */
    public function create(Request $request)
    {
        $preselectedConstituency = $request->get('constituency', '');

        return view('admin.cdf-disbursements.create', compact('preselectedConstituency'));
    }

    /**
     * Store a newly created CDF disbursement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'constituency' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'received_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        CdfDisbursement::create([
            'constituency' => $validated['constituency'],
            'amount' => $validated['amount'],
            'received_date' => $validated['received_date'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.cdf-disbursements.index')
            ->with('success', 'CDF disbursement recorded successfully.');
    }

    /**
     * Remove the specified CDF disbursement.
     */
    public function destroy(CdfDisbursement $cdfDisbursement)
    {
        $cdfDisbursement->delete();

        return back()->with('success', 'CDF disbursement deleted successfully.');
    }
}
