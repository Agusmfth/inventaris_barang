<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolSettingTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User { return User::factory()->create(['role'=>User::ROLE_ADMIN]); }
    private function data(array $overrides=[]): array { return array_merge(['school_name'=>'SD Negeri Contoh 01','npsn'=>'12345678','principal_name'=>'Kepala Contoh','address'=>'Jl. Sekolah','village'=>'Sayar','district'=>'Taktakan','city'=>'Kota Serang','province'=>'Banten','postal_code'=>'42162','phone'=>'0254-123456','email'=>'sekolah@example.test','website'=>'https://example.test','inventory_label_title'=>'ASET SEKOLAH','inventory_label_mark'=>'KOPERASI DESA','inventory_label_footer'=>'RAWAT DAN JAGA ASET'], $overrides); }

    public function test_admin_can_open_and_update_school_identity_globally(): void
    {
        $admin=$this->admin();
        $this->actingAs($admin)->get(route('school-settings.edit'))->assertOk()->assertSee('Identitas Sekolah');
        $this->put(route('school-settings.update'),$this->data())->assertRedirect()->assertSessionHas('success','Identitas sekolah berhasil diperbarui.');
        $this->assertDatabaseHas('school_settings',['school_name'=>'SD Negeri Contoh 01','inventory_label_title'=>'ASET SEKOLAH','inventory_label_mark'=>'KOPERASI DESA']);
        $this->get(route('dashboard'))->assertSee('SD Negeri Contoh 01');
        $this->post(route('logout'));
        $this->get(route('login'))->assertSee('SD Negeri Contoh 01');
        $asset=Asset::firstOrFail();
        $this->get(route('assets.public-info',['asset'=>$asset->asset_code]))->assertSee('SD Negeri Contoh 01');
    }

    public function test_logo_can_be_added_preserved_and_replaced(): void
    {
        Storage::fake('public'); $this->actingAs($this->admin());
        $this->put(route('school-settings.update'),$this->data(['logo'=>UploadedFile::fake()->image('logo.png')]))->assertRedirect();
        $setting=SchoolSetting::first(); $first=$setting->logo; Storage::disk('public')->assertExists($first);
        $this->put(route('school-settings.update'),$this->data())->assertRedirect();
        $this->assertSame($first,$setting->fresh()->logo); Storage::disk('public')->assertExists($first);
        $this->put(route('school-settings.update'),$this->data(['logo'=>UploadedFile::fake()->image('baru.webp')]))->assertRedirect();
        $setting->refresh(); Storage::disk('public')->assertMissing($first); Storage::disk('public')->assertExists($setting->logo);
        $this->get(route('media.show',['path'=>$setting->logo]))->assertOk()->assertHeader('content-disposition','inline');
        $this->get(route('school-settings.logo'))->assertOk()->assertHeader('content-disposition','inline');
        $asset=Asset::firstOrFail(); $this->get(route('asset-labels.single',$asset))->assertSee(route('school-settings.logo'),false)->assertSee('SD Negeri Contoh 01')->assertSee('ASET SEKOLAH')->assertSee('KOPERASI DESA')->assertSee('RAWAT DAN JAGA ASET');
    }

    public function test_validation_and_authorization_are_enforced(): void
    {
        $head=User::factory()->create(['role'=>User::ROLE_KEPALA_SEKOLAH]);
        $this->actingAs($head)->get(route('school-settings.edit'))->assertForbidden();
        $this->put(route('school-settings.update'),$this->data())->assertForbidden();
        $this->actingAs($this->admin())->put(route('school-settings.update'),$this->data(['school_name'=>'','email'=>'bukan-email','website'=>'bukan-url','logo'=>UploadedFile::fake()->create('logo.pdf',10)]))->assertSessionHasErrors(['school_name','email','website','logo']);
    }
}
