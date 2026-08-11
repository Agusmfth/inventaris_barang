<?php
namespace App\Http\Controllers;
use App\Models\{Asset,AssetCategory,AssetLocation};
use App\Services\AssetQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class AssetQrCodeController extends Controller
{
    public function __construct(private readonly AssetQrCodeService $qr){}
    public function index(Request $request):View
    {
        $f=$request->validate([
            'search'=>['nullable','string','max:100'],
            'category'=>['nullable','integer','exists:asset_categories,id'],
            'location'=>['nullable','integer','exists:asset_locations,id'],
            'condition'=>['nullable',Rule::in(Asset::CONDITIONS)],
            'status'=>['nullable',Rule::in(Asset::STATUSES)]
        ]);
        $assets=Asset::with(['category:id,name','location:id,name'])->when($f['search']??null,fn($q,$v)=>$q->where(fn($n)=>$n->where('asset_code','like',"%{$v}%")->orWhere('name','like',"%{$v}%")))->when($f['category']??null,fn($q,$v)=>$q->where('category_id',$v))->when($f['location']??null,fn($q,$v)=>$q->where('location_id',$v))->when($f['condition']??null,fn($q,$v)=>$q->where('condition',$v))->when($f['status']??null,fn($q,$v)=>$q->where('status',$v))->latest()->get();
        $assets->each(fn($asset)=>$asset->setAttribute('qr_data_uri',$this->qr->svgDataUri($asset)));
        return view('asset-qr-codes.index',['assets'=>$assets,'categories'=>AssetCategory::orderBy('name')->get(['id','name']),'locations'=>AssetLocation::orderBy('name')->get(['id','name']),'summary'=>['total'=>Asset::count(),'qr'=>Asset::count(),'active'=>Asset::where('status','!=','dihapus')->count()]]);
    }
    public function download(Asset $asset){return response($this->qr->png($asset),200,['Content-Type'=>'image/png','Content-Disposition'=>'attachment; filename="QR-'.$asset->asset_code.'.png"','Cache-Control'=>'private, no-store']);}
    public function print(Asset $asset):View{$asset->load(['category','location']);$qrDataUri=$this->qr->svgDataUri($asset,520);return view('asset-qr-codes.print',compact('asset','qrDataUri'));}
    public function printSelected(Request $request):View
    {
        $data=$request->validate(['asset_ids'=>['required','array','min:1','max:100'],'asset_ids.*'=>['integer','distinct','exists:assets,id']],['asset_ids.required'=>'Pilih minimal satu aset untuk dicetak.','asset_ids.min'=>'Pilih minimal satu aset untuk dicetak.','asset_ids.max'=>'Maksimal 100 QR dalam sekali cetak.']);
        $assets=Asset::with(['category','location'])->whereIn('id',$data['asset_ids'])->orderBy('asset_code')->get()->each(fn($asset)=>$asset->setAttribute('qr_data_uri',$this->qr->svgDataUri($asset,360)));
        return view('asset-qr-codes.print-selected',compact('assets'));
    }
}
