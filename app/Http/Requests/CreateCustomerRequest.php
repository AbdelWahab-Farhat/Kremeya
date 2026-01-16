<?php
namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // User Data (اختياري)
            'name'                  => ['nullable', 'string', 'max:255'],

            'email'                 => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_with:password'],

            'password'              => ['nullable', 'string', 'min:8', 'required_with:email', 'confirmed'],
            'password_confirmation' => ['required_with:password'],

            // Customer Data
            'phone'                 => ['required', 'string', 'max:10', Rule::unique('users', 'phone'),
            ],
            'city_id'               => ['nullable', 'integer', 'exists:cities,id'],
            'region_id'             => ['nullable', 'integer', 'exists:regions,id'],

            'gender'                => ['sometimes', Rule::enum(Gender::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'         => 'رقم الهاتف مطلوب.',
            'phone.unique'           => 'رقم الهاتف مستخدم مسبقًا.',
            'phone.max'              => 'رقم الهاتف لا يجب أن يتجاوز :max أحرف.',

            'email.email'            => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'           => 'البريد الإلكتروني مستخدم مسبقًا.',
            'email.required_with'    => 'البريد الإلكتروني مطلوب عند إدخال كلمة المرور.',

            'password.required_with' => 'كلمة المرور مطلوبة عند إدخال البريد الإلكتروني.',
            'password.min'           => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed'     => 'تأكيد كلمة المرور غير مطابق.',

            'city_id.exists'         => 'المدينة المختارة غير موجودة.',
            'region_id.exists'       => 'المنطقة المختارة غير موجودة.',

            'gender.enum'            => 'قيمة الجنس غير صحيحة.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                  => 'الاسم',
            'email'                 => 'البريد الإلكتروني',
            'password'              => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
            'phone'                 => 'رقم الهاتف',
            'city_id'               => 'المدينة',
            'region_id'             => 'المنطقة',
            'gender'                => 'الجنس',
        ];
    }
}
