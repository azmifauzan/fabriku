<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialReceipt;
use Illuminate\Http\Request;

class MaterialReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'supplier_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'batch_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $material = Material::findOrFail($validated['material_id']);

        // Generate receipt number
        $year = now()->year;
        $count = MaterialReceipt::whereYear('created_at', $year)->count() + 1;
        $receiptNumber = sprintf('REC-%d-%04d', $year, $count);

        \Illuminate\Support\Facades\Log::info('Creating receipt', ['validated' => $validated]);

        $receipt = MaterialReceipt::create([
            'tenant_id' => $request->user()->tenant_id,
            'material_id' => $material->id,
            'receipt_number' => $receiptNumber,
            'supplier_name' => $validated['supplier_name'],
            'quantity' => $validated['quantity'],
            'unit' => $material->unit, // Assume same unit as material
            'price_per_unit' => $validated['unit_price'],
            'total_cost' => $validated['quantity'] * $validated['unit_price'],
            'receipt_date' => $validated['receipt_date'],
            'notes' => $validated['notes'] ?? null,
            'batch_number' => $validated['batch_number'] ?? null,
            'received_by' => $request->user()->id,
        ]);

        \Illuminate\Support\Facades\Log::info('Receipt created', ['id' => $receipt->id]);

        if ($request->hasFile('image')) {
            $tenantId = $request->user()->tenant_id;
            $path = $request->file('image')->store("tenants/{$tenantId}/receipts", 'fabriku_s3');
            $receipt->update(['image_path' => $path]);
        }

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan.');
    }
}
