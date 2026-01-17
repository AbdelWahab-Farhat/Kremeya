<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'selling_price' => ['required', 'numeric', 'gte:0', 'lte:999999.99'],
            'buying_price'  => ['required', 'numeric', 'gte:0', 'lte:999999.99'],
            'is_active'     => ['sometimes', 'boolean'],
            'stock'         => ['required', 'integer', 'min:0'],
            'images'        => ['required', 'array', 'min:1', 'max:10'],
            'images.*'      => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'          => 'اسم المنتج',
            'description'   => 'وصف المنتج',
            'selling_price' => 'سعر البيع',
            'buying_price'  => 'سعر الشراء',
            'is_active'     => 'حالة التفعيل',
            'stock'         => 'المخزون',
            'images'        => 'صور المنتج',
            'images.*'      => 'صورة المنتج',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'حقل :attribute مطلوب.',
            'name.string'            => 'حقل :attribute يجب أن يكون نصًا.',
            'name.max'               => 'حقل :attribute يجب ألا يتجاوز :max حرفًا.',

            'description.string'     => 'حقل :attribute يجب أن يكون نصًا.',

            'selling_price.required' => 'حقل :attribute مطلوب.',
            'selling_price.numeric'  => 'حقل :attribute يجب أن يكون رقمًا.',
            'selling_price.gte'      => 'حقل :attribute يجب أن يكون أكبر من أو يساوي :value.',
            'selling_price.lte'      => 'حقل :attribute يجب ألا يتجاوز :value.',

            'buying_price.numeric'   => 'حقل :attribute يجب أن يكون رقمًا.',
            'buying_price.gte'       => 'حقل :attribute يجب أن يكون أكبر من أو يساوي :value.',
            'buying_price.lte'       => 'حقل :attribute يجب ألا يتجاوز :value.',
            'buying_price.required'  => 'حقل :attribute مطلوب.',
            'is_active.boolean'      => 'حقل :attribute يجب أن يكون نعم/لا (true/false) أو 1/0.',

            'stock.required'         => 'حقل :attribute مطلوب.',
            'stock.integer'          => 'حقل :attribute يجب أن يكون رقمًا صحيحًا.',
            'stock.min'              => 'حقل :attribute يجب أن يكون :min أو أكثر.',

            'images.required'        => 'حقل :attribute مطلوب.',
            'images.array'           => 'حقل :attribute يجب أن يكون مصفوفة',
            'images.min'             => 'يجب تحميل على الأقل صورة واحدة في حقل :attribute.',
            'images.max'             => 'لا يمكن تحميل أكثر من :max صور في حقل :attribute.',
            'images.*.image'         => 'كل عنصر في حقل :attribute يجب أن يكون صورة صالحة.',
            'images.*.mimes'         => 'كل صورة في حقل :attribute يجب أن تكون من نوع :values.',
            'images.*.max'           => 'كل صورة في حقل :attribute يجب ألا تتجاوز :max كيلوبايت.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
        ]);
    }
}
