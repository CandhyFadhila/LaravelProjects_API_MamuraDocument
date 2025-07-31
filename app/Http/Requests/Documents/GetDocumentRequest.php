<?php

namespace App\Http\Requests\Documents;

use App\Http\Resources\Templates\WithoutDataResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class GetDocumentRequest extends FormRequest
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
            'file_id' => ['required', 'array'],
            'file_id.*' => ['required', 'uuid'],
        ];
    }

    public function messages()
    {
        return [
            'file_id.required' => 'ID file tidak boleh kosong.',
            'file_id.array' => 'ID file harus berupa array.',
            'file_id.*.uuid' => 'Setiap ID file harus berupa UUID.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Get Dokumen Gagal', $messages);

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}
