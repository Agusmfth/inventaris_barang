<?php
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class ProfileController extends Controller
{
    public function edit(Request $request):View{return view('profile.edit',['user'=>$request->user()]);}
    public function update(Request $request):RedirectResponse{$user=$request->user();$data=$request->validate(['name'=>['required','string','max:255'],'email'=>['nullable','email','max:255',Rule::unique('users')->ignore($user->id)]],['name.required'=>'Nama lengkap wajib diisi.','email.email'=>'Alamat email tidak valid.','email.unique'=>'Email sudah digunakan.']);$user->update($data);return back()->with('success','Profil berhasil diperbarui.');}
    public function password(Request $request):RedirectResponse{$data=$request->validate(['password'=>['required','string','min:8','confirmed']],['password.required'=>'Password baru wajib diisi.','password.min'=>'Password minimal 8 karakter.','password.confirmed'=>'Konfirmasi password tidak sama.']);$request->user()->update(['password'=>$data['password']]);return back()->with('success','Password berhasil diperbarui.');}
}
