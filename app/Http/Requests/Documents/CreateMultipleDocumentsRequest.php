<?php

namespace App\Http\Requests\Documents;

use App\Http\Resources\Templates\WithoutDataResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class CreateMultipleDocumentsRequest extends FormRequest
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
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file'],
        ];
    }

    public function messages()
    {
        return [
            'files.required' => 'File yang diunggah tidak boleh kosong.',
            'files.array' => 'Format file harus berupa array.',
            'files.*.required' => 'Setiap file dalam array harus memiliki data.',
            'files.*.file' => 'Setiap item harus berupa file yang valid.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Get Dokumen Gagal', $messages);

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}
