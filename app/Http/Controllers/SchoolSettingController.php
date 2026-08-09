<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSchoolSettingRequest;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SchoolSettingController extends Controller
{
    public function logo()
    {
        $setting = SchoolSetting::first();
        abort_unless($setting?->logo && Storage::disk('public')->exists($setting->logo), 404);

        return Storage::disk('public')->response($setting->logo, null, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function edit(): View
    {
        return view('school-settings.edit', ['setting'=>SchoolSetting::firstOrCreate([], ['school_name'=>'Nama Sekolah','inventory_label_title'=>'BARANG INVENTARIS','inventory_label_footer'=>'JAGA & GUNAKAN DENGAN BAIK'])]);
    }

    public function update(UpdateSchoolSettingRequest $request): RedirectResponse
    {
        $setting = SchoolSetting::firstOrCreate([], ['school_name'=>'Nama Sekolah']);
        $data = $request->validated();
        $newLogo = $request->file('logo')?->store('school','public');
        unset($data['logo']);
        if ($newLogo) $data['logo'] = $newLogo;
        $oldLogo = $setting->logo;
        try { DB::transaction(fn () => $setting->update($data)); }
        catch (Throwable $exception) { if ($newLogo) Storage::disk('public')->delete($newLogo); throw $exception; }
        if ($newLogo && $oldLogo) Storage::disk('public')->delete($oldLogo);
        app()->instance(SchoolSetting::class, $setting->fresh());
        return back()->with('success','Identitas sekolah berhasil diperbarui.');
    }
}
