<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge([
            'clock_in' => ['required', 'date_format:H:i', 'before:clock_out'],
            'clock_out' => ['required', 'date_format:H:i'],
            'note' => ['required'],
            ],
            $this->_dynamic_rules ?? []);
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '退勤時間を入力してください',
            '*.date_format' => '休憩時間が不適切な値です',
            '*.after' => '休憩時間が不適切な値です',
            '*.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }

    // 動的に追加された休憩行にルール適用
    public function validator($factory)
    {
        $validator = $factory->make(
            $this->validationData(),
            $this->rules(),
            $this->messages()
        );

        foreach ($this->all() as $key => $value) {
            if (preg_match('/^break_start_\d+$/', $key)) {
                $num = (int) str_replace('break_start_', '', $key);

                $validator->addRules([
                    "break_start_{$num}" => ['nullable', 'date_format:H:i', 'after:clock_in', 'before:clock_out'],
                    "break_end_{$num}"   => ['nullable', 'date_format:H:i', 'after:break_start_' . $num, 'before:clock_out'],
                ]);
            }
        }

        return $validator;
    }
}
