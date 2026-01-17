<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDarbAssabilShipmentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'city'    => ['required', 'string', 'max:100'],
            'area'    => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'city'    => 'المدينة',
            'area'    => 'المنطقة',
            'address' => 'العنوان',
        ];
    }

    public function messages(): array
    {
        return [
            'city.required' => 'حقل :attribute مطلوب.',
            'area.required' => 'حقل :attribute مطلوب.',
        ];
    }
}
