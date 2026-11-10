<?php

namespace App\Http\Requests;

use App\Enums\BalanceGroupCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubstationMeterStatRequest extends FormRequest
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
            'balance_group' => ['required', Rule::enum(BalanceGroupCode::class)],
            'year' => ['required', 'integer', 'min:2000', 'max:'.now()->year],
            'month' => ['required', 'integer', 'between:1,12'],
            'meters_total' => ['required', 'integer', 'min:0'],
            'meters_installed' => ['required', 'integer', 'min:0', 'lte:meters_total'],
        ];
    }

    /** @return array<int, \Closure> */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['year', 'month'])) {
                    return;
                }

                $now = now();

                if ($this->integer('year') === $now->year && $this->integer('month') > $now->month) {
                    $validator->errors()->add('month', 'Нельзя внести данные за месяц, который ещё не наступил.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'meters_installed.lte' => 'Установлено не может быть больше общего количества.',
            'meters_total.min' => 'Количество не может быть отрицательным.',
        ];
    }
}
