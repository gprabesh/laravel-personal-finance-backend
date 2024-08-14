<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AccountRequest;
use App\Services\AccountService;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{

    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accounts = Account::query();
        if (isset($request->account_group_id) && count($request->account_group_id) > 0) {
            $accounts = $accounts->whereIn('account_group_id', $request->account_group_id);
        }
        $accounts = $accounts->get();

        return $this->jsonResponse(data: ['accounts' => $accounts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $account = new Account();
            $account->name = $request->name;
            $account->account_group_id = $request->account_group_id;
            $account->current_balance = abs($request->opening_balance) ?? 0;
            $account->save();
            if ($account->account_group_id == 3) { //Assets/Wallets
                if ($request->opening_balance < 0) {
                    $account->current_balance_type = 'CR';
                    $account->update();
                }
                $transaction = new Transaction();
                $transaction->description = 'Opening Balance';
                $transaction->amount = abs($request->opening_balance) ?? 0;
                $transaction->transaction_type_id = 8; //Opening Balance
                $transaction->save();
                $debitTransaction = new TransactionDetail();
                $debitTransaction->debit = $transaction->amount;
                $creditTransaction = new TransactionDetail();
                $creditTransaction->credit = $transaction->amount;
                $debitTransaction->transaction_id = $transaction->id;
                $creditTransaction->transaction_id = $transaction->id;
                if ($request->opening_balance >= 0) {
                    $debitTransaction->account_id = $account->id;
                    $creditTransaction->account_id = $user->opening_balance_account_id;
                } else {
                    $debitTransaction->account_id = $user->opening_balance_account_id;
                    $creditTransaction->account_id = $account->id;
                }
                $debitTransaction->save();
                $creditTransaction->save();
                $this->accountService->recalculateBalance($debitTransaction);
                $this->accountService->recalculateBalance($creditTransaction);
            }
            DB::commit();
            return $this->jsonResponse(message: 'Account created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->jsonResponse(message: 'Failed to create account');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request, Account $account)
    {
        DB::beginTransaction();
        try {
            if (($account->account_group_id != $request->account_group_id) && $account->account_group_id == 3) {
                throw new CustomException('Cannot change account group');
            }
            if (($account->account_group_id != $request->account_group_id) && $account->account_group_id != 3 && $request->account_group_id == 3) {
                throw new CustomException('Cannot change account group');
            }
            $account->name = $request->name;
            $account->account_group_id = $request->account_group_id;
            if ($account->account_group_id == 3) { //Assets/Wallets
                $transactionDetail = DB::table('transaction_details as td')->select('t.id')->join('transactions as t', 't.id', '=', 'td.transaction_id')->where('account_id', $account->id)->where('t.transaction_type_id', 8)->first();
                $openingBalanceTransaction = Transaction::with('transactionDetails')->find($transactionDetail->id);
                foreach ($openingBalanceTransaction->transactionDetails as $key => $value) {
                    if (abs($request->opening_balance) == 0) {
                        $value->debit = 0;
                        $value->credit = 0;
                    } else {
                        if ($value->account_id == $account->id) {
                            if ($request->opening_balance > 0) {
                                $value->debit = abs($request->opening_balance);
                                $value->credit = 0;
                            } else {
                                $value->credit = abs($request->opening_balance);
                                $value->debit = 0;
                            }
                        } else {
                            if ($request->opening_balance > 0) {
                                $value->credit = abs($request->opening_balance);
                                $value->debit = 0;
                            } else {
                                $value->debit = abs($request->opening_balance);
                                $value->credit = 0;
                            }
                        }
                    }
                    if ($value->isDirty()) {
                        $account->needs_balance_recalculation = 1;
                        $account->update();
                        $value->update();
                        $this->accountService->recalculateBalance($value);
                    }
                }
            }
            $account->update();
            DB::commit();
            return $this->jsonResponse(message: 'Account updated');
        } catch (CustomException $ce) {
            DB::rollBack();
            Log::error($ce);
            return $this->jsonResponse(message: $ce->getMessage(), status: 400);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->jsonResponse(message: 'Failed to update account', status: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }
}
