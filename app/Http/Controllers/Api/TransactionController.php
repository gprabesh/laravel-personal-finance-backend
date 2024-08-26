<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use stdClass;

class TransactionController extends Controller
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
        $transactionDetails = TransactionDetail::with(['transaction', 'account', 'transaction.transactionType'])
            ->whereHas('account', function ($query) use ($request) {
                if (isset($request->accountIds) && count($request->accountIds) > 0) {
                    $query->whereIn('id', $request->accountIds);
                }
                if (isset($request->accountGroupIds) && count($request->accountGroupIds) > 0) {
                    $query->whereIn('account_group_id', $request->accountGroupIds);
                }
            })
            ->whereHas('transaction.transactionType', function ($query) use ($request) {

                if (isset($request->transactionTypeIds) && count($request->transactionTypeIds) > 0) {
                    $query->whereIn('id', $request->transactionTypeIds);
                }
            })
            ->when(isset($request->account_id) && $request->account_id > 0, function ($query) use ($request) {
                return $query->where('account_id', '=', $request->account_id);
            })->whereNotIn('account_id', [auth()->user()->opening_balance_account_id, auth()->user()->transfer_charge_account_id])->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(100);
        return $this->jsonResponse(data: ['transactionDetails' => $transactionDetails]);
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
    public function store(TransactionRequest $transactionRequest)
    {
        DB::beginTransaction();
        try {
            $transactionDate = $transactionRequest->transaction_date ?? now();
            $transaction = new Transaction();
            $transaction->description = $transactionRequest->description;
            $transaction->transaction_type_id = $transactionRequest->transaction_type_id;
            $transaction->transaction_date = $transactionDate;
            $transaction->location_id = $transactionRequest->location_id ?? null;
            $transaction->parent_id = $transactionRequest->parent_id ?? null;
            $transaction->save();
            $transaction->people()->sync($transactionRequest->people ?? []);
            $amount = 0;

            $accountTransactionDetail = new TransactionDetail();
            $accountTransactionDetail->transaction_date = $transactionDate;
            $accountTransactionDetail->account_id = $transactionRequest->account_id;
            $accountTransactionDetail->transaction_id = $transaction->id;
            $accountTransactionDetail->save();
            $this->accountService->getDebitCreditAmounts($accountTransactionDetail, $transactionRequest->amount, $transactionRequest->transaction_type_id);
            $this->accountService->recalculateBalance($accountTransactionDetail);
            $amount += $accountTransactionDetail->debit;

            $chargeTransactionDetail = new TransactionDetail();
            $chargeTransactionDetail->transaction_date = $transactionDate;
            $chargeTransactionDetail->account_id = auth()->user()->transfer_charge_account_id;
            $chargeTransactionDetail->transaction_id = $transaction->id;
            $chargeTransactionDetail->save();
            $this->accountService->getDebitCreditAmounts($chargeTransactionDetail, $transactionRequest->charge, $transactionRequest->transaction_type_id);
            $this->accountService->recalculateBalance($chargeTransactionDetail);
            $amount += $chargeTransactionDetail->debit;

            $walletTransactionDetail = new TransactionDetail();
            $walletTransactionDetail->transaction_date = $transactionDate;
            $walletTransactionDetail->account_id = $transactionRequest->wallet_id;
            $walletTransactionDetail->transaction_id = $transaction->id;
            $walletTransactionDetail->save();
            $this->accountService->getDebitCreditAmounts($walletTransactionDetail, $transactionRequest->amount + $transactionRequest->charge, $transactionRequest->transaction_type_id);
            $this->accountService->recalculateBalance($walletTransactionDetail);
            $amount += $walletTransactionDetail->debit;

            $transaction->amount = $amount;
            $transaction->update();
            DB::commit();
            return $this->jsonResponse(message: 'Transaction created');
        } catch (CustomException $ce) {
            DB::rollBack();
            Log::error($ce);
            return $this->jsonResponse(message: $ce->getMessage(), status: 400);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->jsonResponse(message: 'Failed to save transaction', status: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($transactionId)
    {
        $transaction = Transaction::with('transactionDetails', 'transactionDetails.account')->find($transactionId);
        $transactionDto = new stdClass;
        $transactionDto->description = $transaction->description;
        $transactionDto->transaction_date = $transaction->transaction_date;
        $transactionDto->location_id = $transaction->location_id;
        $transactionDto->people = [];
        $transactionDto->transaction_type_id = $transaction->transaction_type_id;
        $charge = 0;
        $amount = 0;
        foreach ($transaction->transactionDetails as $key => $value) {
            if ($value->account_id == auth()->user()->transfer_charge_account_id) {
                $charge = abs($value->debit - $value->credit);
            } elseif ($value->account->account_group_id == 3) {
                $transactionDto->wallet_id = $value->account_id;
            } else {
                $transactionDto->account_id = $value->account_id;
                $amount = abs($value->debit - $value->credit);
            }
        }
        $transactionDto->amount = $amount;
        $transactionDto->charge = $charge;
        foreach ($transaction->people as $key1 => $value1) {
            $transactionDto->people[] = $value1->id;
        }
        return $this->jsonResponse(message: 'Transaction fetched', data: ['transaction' => $transactionDto]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $transactionRequest, Transaction $transaction)
    {
        DB::beginTransaction();
        try {
            $transactionDate = isset($transactionRequest->transaction_date) ? $transactionRequest->transaction_date : $transaction->transaction_date;
            $transaction->description = $transactionRequest->description;
            $transaction->transaction_date = $transactionDate;
            $transaction->location_id = $transactionRequest->location_id ?? null;
            $transaction->save();
            $transaction->people()->sync($transactionRequest->people);
            $amount = 0;
            foreach ($transactionRequest->transactionDetails as $key => $value) {
                $transactionDetail = TransactionDetail::find($value['id']);
                $transactionDetail->transaction_date = $transactionDate;
                $transactionDetail->debit = $value['debit'];
                $transactionDetail->credit = $value['credit'];
                $transactionDetail->save();
                $this->accountService->recalculateBalance($transactionDetail);
                $amount += $transactionDetail->debit;
            }
            $transaction->amount = $amount;
            $transaction->update();
            DB::commit();
            return $this->jsonResponse(message: 'Transaction updated');
        } catch (CustomException $ce) {
            DB::rollBack();
            Log::error($ce);
            return $this->jsonResponse(message: $ce->getMessage(), status: 400);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->jsonResponse(message: 'Failed to save transaction', status: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
