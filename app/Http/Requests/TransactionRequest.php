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
        return [
            "description" => "required|string",
            "amount" => "required|numeric",
            "transaction_type_id" => "required|exists:transaction_types,id",
            "location_id" => "nullable|exists:locations,id",
            "parent_id" => "nullable|exists:transactions,id",
            "transactionDetails" => "required|array|min:3|max:3",
            "transactionDetails.*.account_id" => "exists:accounts,id",
            "transactionDetails.*.debit" => "numeric",
            "transactionDetails.*.credit" => "numeric",
            "people" => "nullable|array",
            "people.*" => "exists:people,id"
        ];
    }
}
