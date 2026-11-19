<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class StoreMobilRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->status === 'aktif'
            && $user->roleKey() !== 'owner'
            && ($user->roleName() === 'admin' || $user->hasPermission('inventory.manage'));
    }
    protected function prepareForValidation(): void
    {
        $this->merge(['nomor_rangka' => strtoupper(trim((string) $this->input('nomor_rangka'))),
            'nomor_mesin' => strtoupper(trim((string) $this->input('nomor_mesin'))), 'warna' => trim((string) $this->input('warna'))]);
    }
    public function rules(): array
    {
        return self::baseRules() + ['catalog_identity' => ['nullable', 'string', 'max:120'], 'kode_mobil' => ['required', 'string', 'max:30', Rule::unique('mobil', 'kode_mobil')], 'nomor_rangka' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_rangka')], 'nomor_mesin' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_mesin')]];
    }
    public static function baseRules(?int $ignoreMobilId = null): array
    {
        return ['merk' => ['required', 'string', 'max:80'], 'model' => ['required', 'string', 'max:100'], 'tipe' => ['nullable', 'string', 'max:100'], 'tahun' => ['nullable', 'integer', 'between:1886,'.(now()->year + 1)], 'warna' => ['required', 'string', 'max:50'], 'warna_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'], 'stok_awal' => ['nullable', 'integer', 'min:1', 'max:50'], 'tampil_di' => ['nullable', Rule::in(['keduanya', 'landing_only', 'member_only'])], 'transmisi' => ['nullable', Rule::in(['manual','automatic','cvt','dct','lainnya'])], 'bahan_bakar' => ['nullable', Rule::in(['bensin','diesel','hybrid','listrik','lainnya'])], 'kilometer' => ['required', 'integer', 'min:0'], 'harga_beli' => ['required', 'numeric', 'min:0'], 'harga_jual' => ['required', 'numeric', 'min:0'], 'kondisi' => ['required', Rule::in(['baru','bekas'])], 'status' => ['required', Rule::in(['tersedia','dibooking','dalam_pemeriksaan','terjual','nonaktif'])], 'showcase_slug' => ['nullable', 'string', 'max:80', Rule::unique('mobil', 'showcase_slug')->ignore($ignoreMobilId, 'id_mobil')], 'is_limited_edition' => ['sometimes', 'boolean'], 'deskripsi' => ['nullable', 'string'], 'tanggal_masuk' => ['nullable', 'date_format:Y-m-d'], 'foto' => ['nullable', 'array'], 'foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'foto_sudut' => ['nullable', 'array'], 'foto_sudut.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }

    private static function routeMobilId(self $request): ?int
    {
        $mobil = $request->route('mobil');
        return $mobil instanceof \App\Models\Mobil ? (int) $mobil->id_mobil : null;
    }
}
