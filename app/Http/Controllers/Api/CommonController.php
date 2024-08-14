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
        $accountGroups = AccountGroup::where('status', 1)->where('code', '<>', 'SYS')->get();
        return $this->jsonResponse(data: ['accountGroups' => $accountGroups]);
    }
    public function getTransactionTypes()
    {
        $transactionTypes = TransactionType::where('status', 1)->where('code', '<>', 'OB')->get();
        return $this->jsonResponse(data: ['transactionTypes' => $transactionTypes]);
    }
}
