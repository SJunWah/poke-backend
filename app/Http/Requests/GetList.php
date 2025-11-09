<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\HttpResponse;

class GetList extends FormRequest
{
    protected $stopOnFirstFailure = true;
    use HttpResponse;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'string'],
            'page' => ['sometimes', 'integer'],
            'limit' => ['sometimes', 'integer','max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['name', 'height', 'weight', 'created_at'])],
            'order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.string' => 'The name must be a string.',
            'page.integer' => 'The page must be an integer.',
            'limit.integer' => 'The limit must be an integer.',
            'limit.max' => 'The limit may not be greater than 100.',
            'sort.string' => 'The sort must be a string.',
            'sort.in' => 'The sort must be one of the following: name, height, weight, created_at.',
            'order.string' => 'The order must be a string.',
            'order.in' => 'The order must be one of the following: asc, desc.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(
            $this->error(null, "Validation failed: " . implode(" ", $errors), 422)
        );
    }

    /**
     * Validator hook (after validated)
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            //
        });
    }
}
