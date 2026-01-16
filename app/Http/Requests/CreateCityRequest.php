<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:cities,name'],
            'is_required' => ['boolean'],
            'is_active'   => ['boolean'],
        ];
    }
}
