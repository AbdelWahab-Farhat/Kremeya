<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'value'       => ['required', 'numeric', 'min:0'],
            'type'        => ['required', Rule::in(['fixed', 'percent'])],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code'        => 'كود الكوبون',
            'value'       => 'قيمة الخصم',
            'type'        => 'نوع الخصم',
            'expiry_date' => 'تاريخ الانتهاء',
            'usage_limit' => 'حد الاستخدام',
            'is_active'   => 'حالة التفعيل',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'       => 'حقل :attribute مطلوب.',
            'code.string'         => 'حقل :attribute يجب أن يكون نصًا.',
            'code.max'            => 'حقل :attribute يجب ألا يتجاوز :max حرفًا.',
            'code.unique'         => 'كود الكوبون مستخدم مسبقًا.',

            'value.required'      => 'حقل :attribute مطلوب.',
            'value.numeric'       => 'حقل :attribute يجب أن يكون رقمًا.',
            'value.min'           => 'حقل :attribute يجب أن يكون :min أو أكثر.',

            'type.required'       => 'حقل :attribute مطلوب.',
            'type.in'             => 'حقل :attribute يجب أن يكون fixed أو percent.',

            'expiry_date.date'    => 'حقل :attribute يجب أن يكون تاريخًا صالحًا.',
            'expiry_date.after'   => 'حقل :attribute يجب أن يكون بعد تاريخ اليوم.',

            'usage_limit.integer' => 'حقل :attribute يجب أن يكون رقمًا صحيحًا.',
            'usage_limit.min'     => 'حقل :attribute يجب أن يكون :min أو أكثر.',

            'is_active.boolean'   => 'حقل :attribute يجب أن يكون نعم/لا.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }
    }
}
