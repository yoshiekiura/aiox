<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions;
use App\Wallet;
use Session;
use Redirect;
use Auth;

class TransactionController extends Controller
{

    public function showUserTransactions($curr)
    {
        //function to show user transactions
        $transactions = Transactions::where('currency',$curr)->where('from_user',Auth::user()->id)->orWhere('to_user',Auth::user()->id)->paginate(100);
        $data['curr'] = $curr;
        $data['transactions'] = $transactions;
        if ($curr=="IDR"){
            return view("rupiah",$data);
        }
        else{
            return view("crypto", $data);
        }
        
    }

    public function openTransactionPage(){
        //function to open transaction page
        $wallets = Wallet::where('user_id', Auth::user()->id)->get();
        return view('transpg',$wallets);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawMoney(Request $request)
    {
        //function to decrease user's wallet and log transaction

        $wallet = Wallet::where('user_id', Auth::user()->id)->where('currency',$request->curr)->get();
        if($request->value > $wallet->balance){
            Session::flash('message','Balance uang tidak cukup!');
            return Redirect::to('/transpg');
        }

        $transaction = new Transactions;
        $transaction->from_user = Auth::user()->id;
        $transaction->to_user = Auth::user()->id;
        $transaction->currency = $request->curr;
        $transaction->type = 'WITHDRAW';
        $transaction->value = floatval($request->value);
        $transaction->save();

        $wallet = Wallet::where("user_id", Auth::user()->id)->where("currency", $request->curr)->first();
        $wallet->balance -= floatval($request->value);
        $wallet->save();

        Session::flash('message','Penarikan uang berhasil!');
        return Redirect::to('/balance');    
    }

    public function depositMoney(Request $request)
    {
        //function to increase user's wallet and log transaction
        $transaction = new Transactions;
        $transaction->from_user = Auth::user()->id;
        $transaction->to_user = Auth::user()->id;
        $transaction->currency = $request->curr;
        $transaction->type = 'DEPOSIT';
        $transaction->value = floatval($request->value);
        $transaction->save();
        // dd($transaction);

        $wallet = Wallet::where("user_id", Auth::user()->id)->where("currency", $request->curr)->first();
        $wallet->balance += floatval($request->value);
        $wallet->save();

        return Redirect::to('/balance');

    }

    public function withdrawCrypto(Request $request)
    {
        //function to decrease user's wallet and log transaction
        $wallet = Wallet::where('user_id', Auth::user()->id)->where('currency',$request->curr)->get();
        if($request->value > $wallet->balance){
            Session::flash('message','Balance uang tidak cukup!');
            return Redirect::to('/transpg');
        }

        $transaction = new Transactions;
        $transaction->from_user = Auth::user()->id;
        $transaction->to_user = Auth::user()->id;
        $transaction->currency = $request->curr;
        $transaction->type = 'WITHDRAW';
        $transaction->value = $request->value;
        $transaction->save();

        $wallet->balance -= $request->value;
        $wallet->save();

        Session::flash('message','Penarikan uang berhasil!');
        return Redirect::to('/transpg');
    }

    public function depositCrypto(Request $request)
    {
        //

    }

}
