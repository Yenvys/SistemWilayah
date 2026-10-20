<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Asumsikan diproteksi via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|string|size:16|unique:residents,nik',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:LAKI_LAKI,PEREMPUAN',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'status' => 'required|in:AKTIF,PINDAH,MENINGGAL',
            'rt_id' => 'nullable|exists:rts,id',
        ];
    }
}
