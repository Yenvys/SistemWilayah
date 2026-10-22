<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterWargaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'digits:16', 'exists:residents,nik', 'unique:users,username'],
            'kk_number' => ['required', 'string', 'digits:16', 'exists:families,kk_number'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.exists' => 'NIK tidak ditemukan di data kependudukan. Pastikan RT/RW Anda sudah mendaftarkan NIK Anda.',
            'nik.unique' => 'NIK ini sudah terdaftar sebagai akun. Silakan login.',
            'kk_number.required' => 'No. Kartu Keluarga (KK) wajib diisi.',
            'kk_number.digits' => 'No. KK harus 16 digit angka.',
            'kk_number.exists' => 'No. KK tidak ditemukan di data kependudukan. Pastikan data keluarga Anda sudah terdaftar.',
            'phone_number.max' => 'Nomor WhatsApp / HP maksimal 20 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
