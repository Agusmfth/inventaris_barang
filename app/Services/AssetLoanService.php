<?php
namespace App\Services;use App\Models\Asset;use App\Models\AssetLoan;
class AssetLoanService{public function borrowedQuantity(Asset $asset):int{return (int)AssetLoan::where('asset_id',$asset->id)->whereNull('returned_at')->sum('quantity');}public function availableQuantity(Asset $asset):int{return max(0,$asset->quantity-$this->borrowedQuantity($asset));}public function syncStatus(Asset $asset):void{if(in_array($asset->status,['perawatan','dihapus'],true))return;$asset->update(['status'=>$this->availableQuantity($asset)>0?'tersedia':'dipinjam']);}}
