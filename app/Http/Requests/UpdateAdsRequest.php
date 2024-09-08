<?php

namespace App\Http\Requests;

use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAdsRequest extends FormRequest
{
    use GeneralTrait;

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title'=>'sometimes',
            'description'=>'sometimes',
            'price'=>'numeric|gt:0',
            'number_students'=>'integer|min:1',
            'file'=>'file',
            'place'=>'string',
            'start_date'=>'sometimes|date|after:today',
            'end_date'=>'sometimes|date'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));

    }
}
