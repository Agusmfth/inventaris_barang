<?php
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\Notifications\DatabaseNotification;use Illuminate\View\View;
class NotificationController extends Controller
{
 public function index(Request $request):View{$f=$request->validate(['status'=>['nullable','in:unread,read'],'type'=>['nullable','in:loan,maintenance,disposal,mutation,system'],'per_page'=>['nullable','integer','in:15,30,50,100']]);$perPage=$request->integer('per_page',15);$q=$request->user()->notifications()->when(($f['status']??null)==='unread',fn($q)=>$q->whereNull('read_at'))->when(($f['status']??null)==='read',fn($q)=>$q->whereNotNull('read_at'))->when($f['type']??null,fn($q,$v)=>$q->where('data','like','%\"type\":\"'.$v.'_%'));return view('notifications.index',['notifications'=>$q->latest()->paginate($perPage)->withQueryString()]);}
 public function open(Request $request,string $notification):RedirectResponse
 {
  $item=$request->user()->notifications()->findOrFail($notification);
  $item->markAsRead();

  $entityId=filter_var($item->data['entity_id']??null,FILTER_VALIDATE_INT);
  $route=match($item->data['entity_type']??null){
   'loan'=>'asset-loans.show',
   'maintenance'=>'asset-maintenances.show',
   'disposal'=>'asset-disposals.show',
   'mutation'=>'asset-mutations.show',
   default=>null,
  };

  if($route&&$entityId)return redirect()->route($route,$entityId);

  $target=$item->data['target_url']??'/';
  if(!is_string($target)||!str_starts_with($target,'/')||str_starts_with($target,'//'))return redirect()->route('notifications.index');
  if($target==='/')return redirect()->route('dashboard');

  $basePath=rtrim($request->getBaseUrl(),'/');
  while($basePath!==''&&($target===$basePath||str_starts_with($target,$basePath.'/')))$target=substr($target,strlen($basePath))?:'/';

  return redirect()->to($request->getSchemeAndHttpHost().$basePath.'/'.ltrim($target,'/'));
 }
 public function read(Request $request,string $notification):RedirectResponse{$request->user()->notifications()->findOrFail($notification)->markAsRead();return back()->with('success','Notifikasi ditandai sudah dibaca.');}
 public function readAll(Request $request):RedirectResponse{$request->user()->unreadNotifications()->update(['read_at'=>now()]);return back()->with('success','Semua notifikasi ditandai sudah dibaca.');}
 public function destroy(Request $request,string $notification):RedirectResponse{$request->user()->notifications()->findOrFail($notification)->delete();return back()->with('success','Notifikasi berhasil dihapus.');}
}
