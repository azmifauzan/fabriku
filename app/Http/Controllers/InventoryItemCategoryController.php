<?php

namespace App\Http\Controllers;

use App\Models\InventoryItemCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = InventoryItemCategory::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $category = InventoryItemCategory::create($validated);

        return response()->json($category, 201);
    }
}
