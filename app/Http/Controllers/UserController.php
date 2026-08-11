<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search'=>['nullable','string','max:100'],
            'role'=>['nullable',Rule::in([User::ROLE_ADMIN,User::ROLE_KEPALA_SEKOLAH])],
            'status'=>['nullable',Rule::in(['active','inactive'])],
            'per_page'=>['nullable','integer',Rule::in([10,20,50,100])]
        ]);
        $perPage = $request->integer('per_page', 10);
        $users = User::query()->when($filters['search'] ?? null,fn($q,$v)=>$q->where(fn($n)=>$n->where('name','like',"%{$v}%")->orWhere('username','like',"%{$v}%")))
            ->when($filters['role'] ?? null,fn($q,$v)=>$q->where('role',$v))->when(($filters['status']??null)==='active',fn($q)=>$q->where('is_active',true))->when(($filters['status']??null)==='inactive',fn($q)=>$q->where('is_active',false))
            ->orderByDesc('is_active')->orderBy('name')->paginate($perPage)->withQueryString();
        return view('users.index',['users'=>$users,'totalUsers'=>User::count(),'activeUsers'=>User::where('is_active',true)->count(),'adminUsers'=>User::where('role',User::ROLE_ADMIN)->count(),'headUsers'=>User::where('role',User::ROLE_KEPALA_SEKOLAH)->count()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate($this->rules(messages:true),$this->messages());
        User::create($data);
        return back()->with('success','Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data=$request->validate($this->rules($user),$this->messages());
        if ($request->user()->is($user) && ((!$request->boolean('is_active')) || ($data['role']??null)!==User::ROLE_ADMIN)) return back()->withErrors(['account'=>'Anda tidak dapat menonaktifkan atau mengubah role akun yang sedang digunakan.']);
        $user->update($data);
        return back()->with('success','Data pengguna berhasil diperbarui.');
    }

    public function password(Request $request, User $user): RedirectResponse
    {
        $data=$request->validate(['password'=>['required','string','min:8','confirmed']],$this->messages());
        $user->update(['password'=>$data['password']]);
        return back()->with('success','Password berhasil diperbarui.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) return back()->withErrors(['account'=>'Anda tidak dapat menonaktifkan akun yang sedang digunakan.']);
        $user->update(['is_active'=>!$user->is_active]);
        return back()->with('success',$user->is_active?'Pengguna berhasil diaktifkan.':'Pengguna berhasil dinonaktifkan.');
    }

    private function rules(?User $user=null, bool $messages=false): array
    {
        $rules=['name'=>['required','string','max:255'],'username'=>['required','string','max:100',Rule::unique('users')->ignore($user?->id)],'email'=>['nullable','email','max:255',Rule::unique('users')->ignore($user?->id)],'role'=>['required',Rule::in([User::ROLE_ADMIN,User::ROLE_KEPALA_SEKOLAH])],'is_active'=>['required','boolean']];
        if (!$user) $rules+=['password'=>['required','string','min:8','confirmed']];
        return $rules;
    }
    private function messages(): array { return ['name.required'=>'Nama lengkap wajib diisi.','username.required'=>'Username wajib diisi.','username.unique'=>'Username sudah digunakan.','email.email'=>'Alamat email tidak valid.','email.unique'=>'Email sudah digunakan.','role.required'=>'Role wajib dipilih.','role.in'=>'Role tidak valid.','password.required'=>'Password wajib diisi.','password.min'=>'Password minimal 8 karakter.','password.confirmed'=>'Konfirmasi password tidak sama.']; }
}
