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
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i', 'after:clock_in', 'before:clock_out'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start', 'before:clock_out'],
            'note' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start.after' => '休憩時間が不適切な値です',
            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.after' => '休憩時間が不適切な値です',
            'break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください'
        ];
    }
}
