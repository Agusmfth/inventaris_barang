<?php

namespace App\Exports;

use App\Services\AssetReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    private int $number=0;
    public function __construct(private readonly AssetReportQuery $reports, private readonly array $filters, private readonly int $rowCount, private readonly float $totalValue) {}
    public function query(): Builder { return $this->reports->build($this->filters); }
    public function headings(): array { return ['No','Kode Aset','Nama Aset','Kategori','Lokasi','Sumber Dana','Tahun','Jumlah','Kondisi','Status','Harga Per Unit','Total Nilai']; }
    public function map($asset): array { return [++$this->number,$asset->asset_code,$asset->name,$asset->category->name,$asset->location->name,$asset->fundingSource?->name??'-',$asset->acquisition_year,$asset->quantity,$asset->condition_label,$asset->status_label,(float)$asset->acquisition_price,(float)$asset->acquisition_price*$asset->quantity]; }
    public function styles(Worksheet $sheet): array { return [1=>['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0B6B53']]]]; }
    public function columnFormats(): array { return ['K'=>'#,##0','L'=>'#,##0']; }
    public function registerEvents(): array { return [AfterSheet::class=>function(AfterSheet $event){$row=$this->rowCount+2;$event->sheet->setCellValue("K{$row}",'TOTAL NILAI');$event->sheet->setCellValue("L{$row}",$this->totalValue);$event->sheet->getStyle("K{$row}:L{$row}")->getFont()->setBold(true);$event->sheet->freezePane('A2')->setAutoFilter('A1:L1');}]; }
}
