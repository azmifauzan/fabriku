<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\MaterialType;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::query()
            ->with(['materialAttributes', 'materialType'])
            ->withCount('receipts')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when(request('material_type_id'), fn ($query, $typeId) => $query->where('material_type_id', $typeId))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($material) => [
                ...$material->toArray(),
                'materialType' => $material->materialType?->only(['id', 'name', 'code']),
            ]);

        $types = MaterialType::query()
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'types' => $types,
            'filters' => request()->only(['search', 'material_type_id']),
            'stats' => [
                'total_materials' => Material::count(),
                'low_stock_materials' => Material::whereColumn('stock_quantity', '<=', 'min_stock')->where('stock_quantity', '>', 0)->count(),
                'out_of_stock_materials' => Material::where('stock_quantity', '<=', 0)->count(),
                'total_asset_value' => Material::sum(DB::raw('stock_quantity * price_per_unit')),
            ],
        ]);
    }

    public function create()
    {
        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Materials/Form', [
            'materialTypes' => $materialTypes,
        ]);
    }

    public function store(StoreMaterialRequest $request)
    {
        $data = $request->safe()->except(['attributes', 'stock_quantity', 'image']);

        // Don't set stock_quantity here - it will be set by MaterialReceipt
        // We'll create initial stock to 0, then create a receipt if stock_quantity is provided
        $data['stock_quantity'] = 0;

        $initialStock = $request->stock_quantity ?? 0;

        if ($request->hasFile('image')) {
            $tenantId = auth()->user()->tenant_id;
            $path = $request->file('image')->store("tenants/{$tenantId}/materials", 'fabriku_s3');
            $data['image_path'] = $path;
        }

        $material = Material::create($data);

        // Save attributes if provided
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attr) {
                if (! empty($attr['name']) && ! empty($attr['value'])) {
                    $material->materialAttributes()->create([
                        'attribute_key' => $attr['name'],
                        'attribute_value' => $attr['value'],
                    ]);
                }
            }
        }

        // Create initial receipt/batch if stock quantity is provided
        if ($initialStock > 0) {
            $year = now()->year;
            $count = \App\Models\MaterialReceipt::whereYear('created_at', $year)->count() + 1;
            $receiptNumber = sprintf('REC-%d-%04d', $year, $count);

            $receiptData = [
                'receipt_number' => $receiptNumber,
                'supplier_name' => $material->supplier_name ?? 'Initial Stock',
                'quantity' => $initialStock,
                'remaining_quantity' => $initialStock,
                'unit' => $material->unit,
                'price_per_unit' => $material->price_per_unit ?? 0,
                'total_cost' => $initialStock * ($material->price_per_unit ?? 0),
                'receipt_date' => now(),
                'batch_number' => 'BATCH-'.strtoupper($material->code).'-001',
                'status' => 'available',
                'notes' => 'Stok awal saat pembuatan material',
            ];

            // Include image path if material has image
            if (! empty($data['image_path'])) {
                $receiptData['image_path'] = $data['image_path'];
            }

            $material->receipts()->create($receiptData);
        }

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function show(Material $material)
    {
        $material->load(['materialAttributes', 'materialType',
            'receipts' => fn ($query) => $query->latest()->with('usages.preparationOrder'),
        ]);

        return Inertia::render('Materials/Show', [
            'material' => [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'material_type_id' => $material->material_type_id,
                'unit' => $material->unit,
                'price_per_unit' => (string) $material->price_per_unit,
                'stock_quantity' => (string) $material->stock_quantity,
                'min_stock' => (string) $material->min_stock,
                'supplier_name' => $material->supplier_name,
                'description' => $material->description,
                'materialType' => $material->materialType?->only(['id', 'name', 'code']),
                'attributes' => $material->materialAttributes->map(fn ($attr) => [
                    'attribute_name' => $attr->attribute_key,
                    'attribute_value' => $attr->attribute_value,
                ])->toArray(),
                'receipts' => $material->receipts->map(fn ($receipt) => [
                    'id' => $receipt->id,
                    'receipt_number' => $receipt->receipt_number,
                    'batch_number' => $receipt->batch_number, // or barcode
                    'barcode' => $receipt->barcode,
                    'quantity' => (string) $receipt->quantity,
                    'remaining_quantity' => (string) $receipt->remaining_quantity,
                    'status' => $receipt->status,
                    'receipt_date' => $receipt->receipt_date->format('Y-m-d'),
                    'supplier_name' => $receipt->supplier_name,
                    'price_per_unit' => (string) $receipt->price_per_unit,
                    'image_url' => $receipt->image_url,
                    'usages' => $receipt->usages->map(fn ($usage) => [
                        'id' => $usage->id,
                        'preparation_order_number' => $usage->preparationOrder->order_number,
                        'quantity' => (string) $usage->quantity,
                        'date' => $usage->created_at->format('Y-m-d H:i'),
                    ]),
                ]),
            ],
        ]);
    }

    public function edit(Material $material)
    {
        $material->load(['materialAttributes', 'materialType']);

        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Materials/Form', [
            'material' => [
                'id' => $material->id,
                'code' => $material->code,
                'name' => $material->name,
                'material_type_id' => $material->material_type_id,
                'unit' => $material->unit,
                'price_per_unit' => (string) $material->price_per_unit,
                'stock_quantity' => (string) $material->stock_quantity,
                'min_stock' => (string) $material->min_stock,
                'supplier_name' => $material->supplier_name,
                'description' => $material->description,
                'attributes' => $material->materialAttributes->map(fn ($attr) => [
                    'attribute_name' => $attr->attribute_key,
                    'attribute_value' => $attr->attribute_value,
                ])->toArray(),
            ],
            'materialTypes' => $materialTypes,
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->safe()->except(['attributes', 'stock_quantity', 'image']);

        // Map stock_quantity to actual field name if needed
        if ($request->has('stock_quantity')) {
            $data['stock_quantity'] = $request->stock_quantity;
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($material->image_path) {
                \Illuminate\Support\Facades\Storage::disk('fabriku_s3')->delete($material->image_path);
            }

            $tenantId = auth()->user()->tenant_id;
            $path = $request->file('image')->store("tenants/{$tenantId}/materials", 'fabriku_s3');
            $data['image_path'] = $path;
        }

        $material->update($data);

        // Sync attributes
        if ($request->has('attributes')) {
            // Delete old attributes
            $material->materialAttributes()->delete();

            // Create new attributes
            if (is_array($request->attributes)) {
                foreach ($request->attributes as $attr) {
                    if (! empty($attr['name']) && ! empty($attr['value'])) {
                        $material->materialAttributes()->create([
                            'attribute_key' => $attr['name'],
                            'attribute_value' => $attr['value'],
                        ]);
                    }
                }
            }
        }

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil diupdate.');
    }

    public function destroy(Material $material)
    {
        if ($material->receipts()->exists()) {
            return back()->with('error', 'Material tidak bisa dihapus karena sudah memiliki penerimaan.');
        }

        if ($material->image_path) {
            \Illuminate\Support\Facades\Storage::disk('fabriku_s3')->delete($material->image_path);
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }
}
