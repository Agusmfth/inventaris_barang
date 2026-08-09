<?php

namespace Tests\Feature;

use App\Models\{Asset, AssetCategory, AssetLoan, AssetLocation, FundingSource, SchoolSetting, User};
use App\Services\AssetReportQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AssetReportTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role=User::ROLE_ADMIN): User { return User::factory()->create(['role'=>$role]); }
    private function asset(array $overrides=[]): Asset
    {
        return Asset::create(array_merge(['asset_code'=>'RPT-'.uniqid(), 'name'=>'Aset Laporan Uji',
            'category_id'=>AssetCategory::firstOrFail()->id, 'location_id'=>AssetLocation::firstOrFail()->id,
            'funding_source_id'=>FundingSource::first()?->id, 'acquisition_year'=>2026, 'acquisition_date'=>'2026-01-15',
            'acquisition_price'=>125000, 'quantity'=>2, 'condition'=>'baik', 'status'=>'tersedia'], $overrides));
    }

    public function test_default_report_excludes_deleted_and_calculates_filtered_totals(): void
    {
        $visible=$this->asset(['asset_code'=>'RPT-VISIBLE','name'=>'Khusus Rekap','quantity'=>3,'acquisition_price'=>200000]);
        $this->asset(['asset_code'=>'RPT-DELETED','name'=>'Khusus Rekap Dihapus','status'=>'dihapus']);
        $this->actingAs($this->user())->get(route('asset-reports.index',['search'=>'Khusus Rekap']))
            ->assertOk()->assertSee($visible->asset_code)->assertDontSee('RPT-DELETED')->assertSee('Rp 600.000');
    }

    public function test_combined_filters_search_sort_and_deleted_history_work(): void
    {
        $category=AssetCategory::firstOrFail(); $location=AssetLocation::firstOrFail(); $funding=FundingSource::firstOrFail();
        $asset=$this->asset(['asset_code'=>'RPT-FILTER-1','name'=>'Target Kombinasi','category_id'=>$category->id,'location_id'=>$location->id,'funding_source_id'=>$funding->id,'condition'=>'rusak_berat','status'=>'dihapus']);
        $query=['year'=>2026,'category'=>$category->id,'location'=>$location->id,'funding_source'=>$funding->id,'condition'=>'rusak_berat','status'=>'dihapus','date_from'=>'2026-01-01','date_to'=>'2026-12-31','search'=>'Target Kombinasi','sort'=>'value_desc'];
        $this->actingAs($this->user())->get(route('asset-reports.index',$query))->assertOk()->assertSee($asset->asset_code)->assertSee('Aset Dihapus');
    }

    public function test_report_counts_active_partial_loans_as_borrowed_units(): void
    {
        $asset=$this->asset(['asset_code'=>'RPT-PARTIAL-LOAN','quantity'=>10,'status'=>'tersedia']);$admin=$this->user();
        AssetLoan::create(['asset_id'=>$asset->id,'borrower_name'=>'Anggota Koperasi','quantity'=>3,'loan_date'=>'2026-08-09','status'=>'dipinjam','created_by'=>$admin->id]);
        $summary=app(AssetReportQuery::class)->summary(['search'=>'RPT-PARTIAL-LOAN']);
        $this->assertSame(3.0,$summary['borrowed']);
    }

    public function test_print_pdf_and_excel_follow_filters_and_include_identity(): void
    {
        $included=$this->asset(['asset_code'=>'RPT-EXPORT-IN','name'=>'Ekspor Pilihan','acquisition_year'=>2027]);
        $this->asset(['asset_code'=>'RPT-EXPORT-OUT','name'=>'Bukan Pilihan','acquisition_year'=>2025]);
        $this->actingAs($this->user());
        $this->get(route('asset-reports.print',['year'=>2027]))->assertOk()->assertSee($included->asset_code)->assertDontSee('RPT-EXPORT-OUT')->assertSee(app(SchoolSetting::class)->display_name)->assertDontSee('sidebar');
        $this->get(route('asset-reports.pdf',['year'=>2027]))->assertOk()->assertHeader('content-type','application/pdf');

        $response=$this->get(route('asset-reports.excel',['year'=>2027]));
        $response->assertOk()->assertDownload('laporan-inventaris-2027.xlsx');
        $file=$response->baseResponse->getFile()->getPathname();
        $sheet=IOFactory::load($file)->getActiveSheet(); $values=$sheet->toArray();
        $flat=implode('|',array_map(fn($row)=>implode('|',$row),$values));
        $this->assertStringContainsString('RPT-EXPORT-IN',$flat); $this->assertStringNotContainsString('RPT-EXPORT-OUT',$flat);
        $this->assertIsNumeric($sheet->getCell('K2')->getValue());
    }

    public function test_report_authorization_pagination_and_validation(): void
    {
        $this->get(route('asset-reports.index'))->assertRedirect(route('login'));
        $this->actingAs($this->user(User::ROLE_KEPALA_SEKOLAH))->get(route('asset-reports.index'))->assertOk();
        $this->get(route('asset-reports.print'))->assertOk(); $this->get(route('asset-reports.pdf'))->assertOk();
        $this->get(route('asset-reports.index',['date_from'=>'2026-12-31','date_to'=>'2026-01-01']))->assertSessionHasErrors('date_to');
        foreach(range(1,26) as $i)$this->asset(['asset_code'=>'RPT-PAGE-'.str_pad($i,2,'0',STR_PAD_LEFT),'name'=>'Paging Laporan']);
        $this->get(route('asset-reports.index',['search'=>'Paging Laporan']))->assertOk()->assertSee('page=2',false);
    }
}
