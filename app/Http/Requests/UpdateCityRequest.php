<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255', 'unique:cities,name,' . $this->city->id],
            'is_required' => ['sometimes', 'boolean'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
