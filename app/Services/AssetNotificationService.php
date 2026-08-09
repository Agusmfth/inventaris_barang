<?php
namespace App\Services;
use App\Models\{AssetLoan,User};use App\Notifications\ImportantAssetNotification;use Illuminate\Support\Collection;use Illuminate\Support\Facades\Notification;
class AssetNotificationService
{
 public function admins(string $title,string $message,string $type,string $url,string $entityType,int $entityId,?string $key=null):void{$this->send(User::where('role',User::ROLE_ADMIN)->where('is_active',true)->get(),compact('title','message','type','url','entityType','entityId','key'));}
 public function heads(string $title,string $message,string $type,string $url,string $entityType,int $entityId,?string $key=null):void{$this->send(User::where('role',User::ROLE_KEPALA_SEKOLAH)->where('is_active',true)->get(),compact('title','message','type','url','entityType','entityId','key'));}
 public function syncOverdue():void{foreach(AssetLoan::with('asset:id,name')->whereNull('returned_at')->whereDate('expected_return_date','<',today())->get() as $loan){$key='loan_overdue:'.$loan->id;$this->admins('Peminjaman Terlambat',"{$loan->asset->name} belum dikembalikan oleh {$loan->borrower_name}.",'loan_overdue',route('asset-loans.show',$loan),'loan',$loan->id,$key);}}
 private function send(Collection $users,array $data):void{foreach($users as $user){if(($data['key']??null)&&$user->notifications()->where('data','like','%"key":"'.$data['key'].'"%')->exists())continue;$payload=['title'=>$data['title'],'message'=>$data['message'],'type'=>$data['type'],'icon'=>$this->icon($data['type']),'target_url'=>$this->path($data['url']),'entity_type'=>$data['entityType'],'entity_id'=>$data['entityId'],'key'=>$data['key']];$user->notify(new ImportantAssetNotification($payload));}}
 private function icon(string $type):string{return str_starts_with($type,'loan_')?'clock':(str_starts_with($type,'maintenance_')?'wrench':(str_starts_with($type,'disposal_')?'trash-2':'info'));}
 private function path(string $url):string{$path=(string)parse_url($url,PHP_URL_PATH);$query=parse_url($url,PHP_URL_QUERY);return $path.($query?'?'.$query:'');}
}
