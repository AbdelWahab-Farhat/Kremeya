<?php
namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletTransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'        => ['required', Rule::enum(TransactionType::class)],
            'amount'      => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type'        => 'نوع العملية',
            'amount'      => 'المبلغ',
            'description' => 'الملاحظة',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'حقل :attribute مطلوب.',
            'type.enum'            => 'حقل :attribute يجب أن يكون credit أو debit.',
            'amount.required'      => 'حقل :attribute مطلوب.',
            'amount.numeric'       => 'حقل :attribute يجب أن يكون رقمًا.',
            'amount.gt'            => 'حقل :attribute يجب أن يكون أكبر من صفر.',
            'description.required' => 'حقل :attribute مطلوب.',
            'description.string'   => 'حقل :attribute يجب أن يكون نصًا.',
            'description.max'      => 'حقل :attribute يجب ألا يتجاوز :max حرفًا.',
        ];
    }
}
