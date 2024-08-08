<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getAccountGroups()
    {
        $accountGroups = AccountGroup::where('status', 1)->get();
        return $this->jsonResponse(message: __('success.data_fetched'), data: ['accountGroups' => $accountGroups]);
    }
    public function getTransactionTypes()
    {
        $transactionTypes = TransactionType::where('status', 1)->get();
        return $this->jsonResponse(message: __('success.data_fetched'), data: ['transactionTypes' => $transactionTypes]);
    }
}
