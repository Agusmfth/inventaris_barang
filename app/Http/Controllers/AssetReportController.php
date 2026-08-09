<?php

namespace App\Http\Controllers;

use App\Exports\AssetReportExport;
use App\Models\{Asset, AssetCategory, AssetLocation, FundingSource, SchoolSetting};
use App\Services\AssetReportQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AssetReportController extends Controller
{
    public function __construct(private readonly AssetReportQuery $reports) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $assets = $this->reports->build($filters)->paginate(25)->withQueryString();
        return view('asset-reports.index', array_merge($this->viewData($filters), compact('assets')));
    }

    public function print(Request $request)
    {
        $filters = $this->filters($request);
        $assets = $this->reports->build($filters)->get();
        return view('asset-reports.print', array_merge($this->documentData($filters), compact('assets')));
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $assets = $this->reports->build($filters)->get();
        return Pdf::loadView('asset-reports.pdf', array_merge($this->documentData($filters), compact('assets')))
            ->setPaper('a4', 'landscape')->download($this->fileName($filters, 'pdf'));
    }

    public function excel(Request $request)
    {
        $filters = $this->filters($request);
        $summary = $this->reports->summary($filters);
        return Excel::download(new AssetReportExport($this->reports, $filters, (int) $summary['total_assets'], (float) $summary['total_value']), $this->fileName($filters, 'xlsx'));
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'year' => ['nullable','integer','min:1900','max:'.(now()->year + 1)],
            'category' => ['nullable','integer','exists:asset_categories,id'],
            'location' => ['nullable','integer','exists:asset_locations,id'],
            'condition' => ['nullable', Rule::in(Asset::CONDITIONS)],
            'status' => ['nullable', Rule::in(['active','all',...Asset::STATUSES])],
            'funding_source' => ['nullable','integer','exists:funding_sources,id'],
            'date_from' => ['nullable','date'], 'date_to' => ['nullable','date','after_or_equal:date_from'],
            'search' => ['nullable','string','max:100'],
            'sort' => ['nullable', Rule::in(['code','name','year_desc','year_asc','value_desc','value_asc'])],
        ], ['date_to.after_or_equal' => 'Tanggal sampai harus sama dengan atau setelah tanggal dari.', '*.exists' => 'Pilihan filter tidak valid.', '*.in' => 'Pilihan filter tidak valid.']);
    }

    private function viewData(array $filters): array
    {
        return ['filters'=>$filters, 'summary'=>$this->reports->summary($filters),
            'categories'=>AssetCategory::orderBy('name')->get(['id','name']), 'locations'=>AssetLocation::orderBy('name')->get(['id','name']),
            'fundingSources'=>FundingSource::orderBy('name')->get(['id','name']), 'years'=>Asset::query()->distinct()->orderByDesc('acquisition_year')->pluck('acquisition_year')];
    }

    private function documentData(array $filters): array
    {
        $school = app(SchoolSetting::class); $logoData = null;
        if ($school->logo && Storage::disk('public')->exists($school->logo)) {
            $mime = Storage::disk('public')->mimeType($school->logo) ?: 'image/png';
            $logoData = 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($school->logo));
        }
        return ['filters'=>$filters, 'filterLabels'=>$this->filterLabels($filters), 'summary'=>$this->reports->summary($filters), 'school'=>$school, 'logoData'=>$logoData, 'printedAt'=>now()];
    }

    private function filterLabels(array $filters): array
    {
        $conditions=['baik'=>'Baik','rusak_ringan'=>'Rusak Ringan','rusak_berat'=>'Rusak Berat'];
        $statuses=['active'=>'Aktif','all'=>'Semua','tersedia'=>'Tersedia','dipinjam'=>'Dipinjam','perawatan'=>'Perawatan','dihapus'=>'Dihapus'];
        return ['Tahun'=>$filters['year']??'Semua', 'Kategori'=>isset($filters['category'])?AssetCategory::find($filters['category'])?->name:'Semua',
            'Lokasi'=>isset($filters['location'])?AssetLocation::find($filters['location'])?->name:'Semua', 'Kondisi'=>$conditions[$filters['condition']??'']??'Semua',
            'Status'=>$statuses[$filters['status']??'active'], 'Sumber Dana'=>isset($filters['funding_source'])?FundingSource::find($filters['funding_source'])?->name:'Semua',
            'Tanggal Pengadaan'=>($filters['date_from']??null)||($filters['date_to']??null)?($filters['date_from']??'Awal').' s.d. '.($filters['date_to']??'Sekarang'):'Semua'];
    }

    private function fileName(array $filters, string $extension): string
    { $suffix=isset($filters['year'])?$filters['year']:now()->format('Y-m-d'); return "laporan-inventaris-{$suffix}.{$extension}"; }
}
