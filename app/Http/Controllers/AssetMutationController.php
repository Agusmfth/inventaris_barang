<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAssetMutationRequest;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class AssetMutationController extends Controller {
    public function index(Request $request): View {
        $filters=$request->validate([
            'search'=>['nullable','string','max:100'],
            'from'=>['nullable','integer','exists:asset_locations,id'],
            'to'=>['nullable','integer','exists:asset_locations,id'],
            'month'=>['nullable','date_format:Y-m'],
            'per_page'=>['nullable','integer','in:10,20,50,100']
        ]);
        $perPage=$request->integer('per_page',10);
        $mutations=AssetMutation::with(['asset:id,asset_code,name','fromLocation:id,name','toLocation:id,name','creator:id,name'])
            ->when($filters['search']??null,fn($q,$s)=>$q->whereHas('asset',fn($a)=>$a->where('asset_code','like',"%{$s}%")->orWhere('name','like',"%{$s}%")))
            ->when($filters['from']??null,fn($q,$id)=>$q->where('from_location_id',$id))->when($filters['to']??null,fn($q,$id)=>$q->where('to_location_id',$id))
            ->when($filters['month']??null,fn($q,$month)=>$q->whereYear('mutation_date',substr($month,0,4))->whereMonth('mutation_date',substr($month,5,2)))
            ->latest('mutation_date')->latest('id')->paginate($perPage)->withQueryString();
        return view('asset-mutations.index',['mutations'=>$mutations,'totalMutations'=>AssetMutation::count(),'thisMonth'=>AssetMutation::whereYear('mutation_date',now()->year)->whereMonth('mutation_date',now()->month)->count(),'mutatedAssets'=>AssetMutation::distinct('asset_id')->count('asset_id'),'assets'=>Asset::with('location:id,name')->whereNotIn('status',['perawatan','dihapus'])->whereDoesntHave('loans',fn($q)=>$q->whereNull('returned_at'))->orderBy('name')->get(['id','asset_code','name','location_id']),'locations'=>AssetLocation::orderBy('name')->get(['id','name','is_active']),'activeLocations'=>AssetLocation::where('is_active',true)->orderBy('name')->get(['id','name'])]);
    }
    public function store(StoreAssetMutationRequest $request): RedirectResponse {
        $data=$request->validated();
        $mutation=DB::transaction(function()use($data,$request){$asset=Asset::lockForUpdate()->findOrFail($data['asset_id']);if($asset->status==='dihapus')throw ValidationException::withMessages(['asset_id'=>'Aset yang sudah dihapus tidak dapat dimutasi.']);if($asset->loans()->whereNull('returned_at')->exists()||in_array($asset->status,['dipinjam','perawatan'],true))throw ValidationException::withMessages(['asset_id'=>'Aset sedang digunakan dalam proses operasional dan tidak dapat dimutasi.']);if((int)$asset->location_id===(int)$data['to_location_id'])throw ValidationException::withMessages(['to_location_id'=>'Lokasi tujuan tidak boleh sama dengan lokasi saat ini.']);$mutation=AssetMutation::create([...$data,'from_location_id'=>$asset->location_id,'created_by'=>$request->user()->id]);$asset->update(['location_id'=>$data['to_location_id']]);return $mutation;});
        return redirect()->route('asset-mutations.show',$mutation)->with('success','Mutasi aset berhasil disimpan.');
    }
    public function show(AssetMutation $mutation): View{return view('asset-mutations.show',['mutation'=>$mutation->load(['asset','fromLocation','toLocation','creator'])]);}
}
