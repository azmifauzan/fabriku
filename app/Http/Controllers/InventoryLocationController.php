<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryLocationRequest;
use App\Http\Requests\UpdateInventoryLocationRequest;
use App\Models\InventoryLocation;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryLocation::query()
            ->withCount('inventoryItems');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $locations = $query->latest()->paginate(self::DEFAULT_PER_PAGE);

        // Map is_active boolean to status string for frontend
        $locations->getCollection()->transform(function ($location) {
            $location->status = $location->is_active ? 'active' : 'inactive';

            return $location;
        });

        return Inertia::render('Inventory/Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function show(InventoryLocation $location)
    {
        $location->load('inventoryItems');

        $location->status = $location->is_active ? 'active' : 'inactive';

        $items = $location->inventoryItems->map(fn ($item) => [
            'id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->product_name,
            'current_stock' => $item->current_quantity,
            'quality_grade' => $item->quality_grade,
            'status' => $item->status,
            'image_url' => $item->image_url,
        ]);

        return Inertia::render('Inventory/Locations/Show', [
            'location' => array_merge($location->toArray(), [
                'items' => $items,
                'current_capacity' => $location->used_capacity,
                'available_capacity' => $location->available_capacity,
            ]),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Inventory/Locations/Create');
    }

    public function store(StoreInventoryLocationRequest $request)
    {
        $location = InventoryLocation::create($request->validated());

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil dibuat.');
    }

    public function edit(InventoryLocation $location)
    {
        return Inertia::render('Inventory/Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $location)
    {
        $location->update($request->validated());

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil diupdate.');
    }

    public function printQrCode(InventoryLocation $location)
    {
        return Inertia::render('Inventory/Locations/PrintQrCode', [
            'location' => $location,
        ]);
    }

    public function generateQrCode(InventoryLocation $location)
    {
        $url = route('inventory.locations.show', $location);

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($url);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function destroy(InventoryLocation $location)
    {
        // Check if location has any items
        if ($location->inventoryItems()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Tidak dapat menghapus lokasi yang masih berisi item.',
            ]);
        }

        $location->delete();

        return redirect()
            ->route('inventory.locations.index')
            ->with('success', 'Location berhasil dihapus.');
    }
}
