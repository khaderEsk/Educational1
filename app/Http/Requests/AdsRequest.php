<?php

namespace App\Http\Requests;

use App\Traits\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdsRequest extends FormRequest
{
    use GeneralTrait;

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title'=>'required',
            'description'=>'required',
            'price'=>'required|numeric|gt:0',
            'number_students'=>'required|integer|min:1',
            'file'=>'required|file',
            'place'=>'required|string',
            'start_date'=>'required|date|after:today',
            'end_date'=>'required|date|after:start_date'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));

    }
}
