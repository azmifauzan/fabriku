<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatternRequest;
use App\Http\Requests\UpdatePatternRequest;
use App\Models\Pattern;
use Inertia\Inertia;

class PatternController extends Controller
{
    public function index()
    {
        $patterns = Pattern::query()
            ->withCount('preparationOrders')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($pattern) => [
                'id' => $pattern->id,
                'code' => $pattern->code,
                'name' => $pattern->name,
                'is_active' => $pattern->is_active,
                'preparation_orders_count' => $pattern->preparation_orders_count,
                'materials' => $pattern->materials,
            ]);

        return Inertia::render('Patterns/Index', [
            'patterns' => $patterns,
            'filters' => request()->only(['search']),
        ]);
    }

    public function show(Pattern $pattern)
    {
        $pattern->load(['preparationOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Patterns/Show', [
            'pattern' => $pattern,
            'recentOrders' => $pattern->preparationOrders,
            'stats' => [
                'total_orders' => $pattern->preparationOrders()->count(),
                'total_produced' => $pattern->preparationOrders()->sum('output_quantity'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Patterns/PatternForm', [
            'isEdit' => false,
        ]);
    }

    public function store(StorePatternRequest $request)
    {
        Pattern::create($request->validated());

        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';

        return redirect('/patterns')
            ->with('success', "{$patternLabel} berhasil ditambahkan.");
    }

    public function edit(Pattern $pattern)
    {
        return Inertia::render('Patterns/PatternForm', [
            'pattern' => $pattern,
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePatternRequest $request, Pattern $pattern)
    {
        $pattern->update($request->validated());

        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';

        return redirect('/patterns')
            ->with('success', "{$patternLabel} berhasil diperbarui.");
    }

    public function destroy(Pattern $pattern)
    {
        $tenant = auth()->user()?->tenant;
        $patternLabel = $tenant?->getTerminology('pattern') ?? 'Pattern';
        $prepOrderLabel = $tenant?->getTerminology('preparation_order') ?? 'Preparation order';

        if ($pattern->preparationOrders()->exists()) {
            return back()->withErrors(['pattern' => "{$patternLabel} tidak bisa dihapus karena sudah digunakan di {$prepOrderLabel}."]);
        }

        $pattern->delete();

        return redirect('/patterns')
            ->with('success', "{$patternLabel} berhasil dihapus.");
    }
}
