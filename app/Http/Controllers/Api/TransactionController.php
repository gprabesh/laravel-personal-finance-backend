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
use Illuminate\Support\Facades\Auth;

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
        $transactions = TransactionDetail::with('transaction', 'account', 'transaction.transactionType')->when(isset($request->account_id) && $request->account_id > 0, function ($query) use ($request) {
            return $query->where('account_id', '=', $request->account_id);
        })->where('account_id', '<>', auth()->user()->opening_balance_account_id)->paginate(100);
        return $this->jsonResponse(data: ['transactions' => $transactions]);
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
            $transaction = new Transaction();
            $transaction->description = $transactionRequest->description;
            $transaction->transaction_type_id = $transactionRequest->transaction_type_id;
            $transaction->location_id = $transactionRequest->location_id ?? null;
            $transaction->parent_id = $transactionRequest->parent_id ?? null;
            $transaction->save();
            $transaction->people()->sync($transactionRequest->people);
            $amount = 0;

            foreach ($transactionRequest->transactionDetails as $key => $value) {
                $transactionDetail = new TransactionDetail();
                $transactionDetail->account_id = $value['account_id'];
                $transactionDetail->debit = $value['debit'];
                $transactionDetail->credit = $value['credit'];
                $transactionDetail->transaction_id = $transaction->id;
                $transactionDetail->save();
                $this->accountService->recalculateBalance($transactionDetail);
                $amount += $transactionDetail->debit;
            }
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
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $transactionRequest, Transaction $transaction)
    {
        DB::beginTransaction();
        try {
            $transaction->description = $transactionRequest->description;
            $transaction->location_id = $transactionRequest->location_id ?? null;
            $transaction->save();
            $transaction->people()->sync($transactionRequest->people);
            $amount = 0;
            foreach ($transactionRequest->transactionDetails as $key => $value) {
                $transactionDetail = TransactionDetail::find($value['id']);
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
