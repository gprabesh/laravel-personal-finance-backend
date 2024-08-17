<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TransactionDetail;

class AccountService
{

    public function recalculateBalance(TransactionDetail $transactionDetail)
    {
        $balance = 0;
        $previous_transaction = TransactionDetail::where('transaction_date', '<', $transactionDetail->transaction_date)->where('account_id', $transactionDetail->account_id)->latest('transaction_date')->first();
        $after_transactions = TransactionDetail::where('transaction_date', '>', $transactionDetail->transaction_date)->where('account_id', $transactionDetail->account_id)->get();
        if (!$previous_transaction) {
            $balance = $transactionDetail->debit - $transactionDetail->credit;
            $transactionDetail->account_balance = abs($balance);
            $transactionDetail->account_balance_type = $balance < 0 ? 'CR' : 'DR';
        } else {
            $balance = $previous_transaction->account_balance_type == 'CR' ? 0 - $previous_transaction->account_balance : $previous_transaction->account_balance;
            $balance = $balance + $transactionDetail->debit - $transactionDetail->credit;
            $transactionDetail->account_balance = abs($balance);
            $transactionDetail->account_balance_type = $balance < 0 ? 'CR' : 'DR';
        }
        if (count($after_transactions) > 0) {
            foreach ($after_transactions as $key => $value) {
                $balance = $balance + $value->debit - $value->credit;
                $value->account_balance_type = $balance < 0 ? 'CR' : 'DR';
                $value->account_balance = abs($balance);
                $value->update();
            }
        }
        $transactionDetail->update();
        Account::withoutGlobalScopes()->where('id', $transactionDetail->account_id)->update([
            'current_balance' => abs($balance),
            'current_balance_type' => $balance < 0 ? 'CR' : 'DR',
            'needs_balance_recalculation' => 0,
        ]);
    }
}
