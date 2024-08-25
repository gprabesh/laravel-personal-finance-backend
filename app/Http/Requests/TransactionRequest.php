<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        $validationArray = [
            "description" => "required|string",
            "transaction_date" => "nullable|date",
            "amount" => "required|numeric",
            "charge" => "required|numeric",
            "account_id" => "required|numeric|exists:accounts,id",
            "wallet_id" => "required|numeric|exists:accounts,id",
            "transaction_type_id" => "required|exists:transaction_types,id",
            "location_id" => "nullable|exists:locations,id",
            "parent_id" => "nullable|exists:transactions,id",
            "people" => "nullable|array",
            "people.*" => "exists:people,id"
        ];
        return $validationArray;
    }
}
