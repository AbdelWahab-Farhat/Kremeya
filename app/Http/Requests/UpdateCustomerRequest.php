<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
{
    $this->merge([
        'region_id' => $this->input('region_id') === 'null' ? null : $this->input('region_id'),
        'city_id'   => $this->input('city_id') === 'null' ? null : $this->input('city_id'),
    ]);
}

    public function rules(): array
    {
        $userId     = $this->customer ? $this->customer->user_id : null;
        $customerId = $this->customer ? $this->customer->id : null;


        return [
            // User Data
            'name' => ['nullable', 'string', 'max:255'],
            // 'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            // 'password' => ['nullable', 'string', 'min:8', 'confirmed'],

            // Customer Data
            'phone'     => ['sometimes', 'string', 'max:20', Rule::unique('customers')->ignore($customerId)],
            'city_id'   => ['nullable', 'integer', 'exists:cities,id'],
            'region_id' => ['sometimes', 'nullable', 'integer', 'exists:regions,id'],
            'gender'    => ['sometimes', Rule::enum(Gender::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'رقم الهاتف مستخدم مسبقًا.',
            'phone.max'    => 'رقم الهاتف لا يجب أن يتجاوز :max أحرف.',

            'city_id.exists' => 'المدينة المختارة غير موجودة.',
            'region_id.exists' => 'المنطقة المختارة غير موجودة.',

            'gender.enum' => 'قيمة الجنس غير صحيحة.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'city_id' => 'المدينة',
            'region_id' => 'المنطقة',
            'gender' => 'الجنس',
        ];
    }
}
