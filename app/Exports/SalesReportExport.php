<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected Collection $orders,
        protected array $summary,
        protected array $filters
    ) {}

    public function view(): View
    {
        return view('exports.sales-report', [
            'orders' => $this->orders,
            'summary' => $this->summary,
            'filters' => $this->filters,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
