<?php

namespace Modules\PaymentGateway\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\OrderRepository;
use Modules\Wallet\Repositories\WalletRepository;
use Brian2694\Toastr\Facades\Toastr;
use Modules\Account\Repositories\TransactionRepository;
use Modules\Account\Entities\Transaction;
use Modules\FrontendCMS\Entities\SubsciptionPaymentInfo;
use App\Traits\Accounts;
use Carbon\Carbon;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Support\Str;
require 'Crypto.php';

class InstamojoController extends Controller
{
    use Accounts;

    public function __construct()
    {
        $this->middleware('maintenance_mode');
    }

    public function paymentProcess($data)
    {
        $merchant_data='2';
    	$working_key='B6315C3A913D775AD9B57694F1799AA5';//Shared by CCAVENUES
    	$access_code='AVPJ90KG75BS59JPSB';//Shared by CCAVENUES
    	
    	
    	$merchant_data = [
    	    'tid' => rand(10000000, 99999999),
            'merchant_id' => '2680086',
            'order_id' => rand(10000000, 99999999),
            'amount' => $data['amount'],
            'currency' => 'INR',
            'redirect_url' => 'https://fightorsports.com',
            'cancel_url' => 'https://fightorsports.com',
            'language' => 'EN',
            'billing_name' => $data['name'],
            'billing_address' => null,
            'billing_city' => null,
            'billing_state' => null,
            'billing_zip' => null,
            'billing_country' => null,
            'billing_tel' => $data['mobile'],
            'billing_email' => $data['email'],
            'delivery_name' => $data['name'],
            'delivery_address' => null,
            'delivery_city' => null,
            'delivery_state' => null,
            'delivery_zip' => null,
            'delivery_country' => null,
            'delivery_tel' => $data['mobile'],
            'merchant_param1' => 'additional Info.',
            'merchant_param2' => 'additional Info.',
            'merchant_param3' => 'additional Info.',
            'merchant_param4' => 'additional Info.',
            'merchant_param5' => 'additional Info.',
            'promo_code' => 'null',
            'customer_identifier' => 'null'
    	];
    	
    	$merchant_data_string = http_build_query($merchant_data);
    	
    // 	echo $merchant_data_string;

	    $encrypted_data=encryptforcc($merchant_data_string,$working_key); // Method for encrypting the data.
	    
	   //echo $encrypted_data;
	   
	   // try {
    //         $client = new \GuzzleHttp\Client();
            
    //         $CC_BASE_URL = "https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction";

    //         $request = Http::post($CC_BASE_URL,[
    //              'encRequest' => $encrypted_data,
    //              'access_code' => $access_code
    //         ]);
    //     } catch (\Exception $e) {

    //         LogActivity::errorLog($e->getMessage());
            
    //     }
	    
        // try {
        //     $credential = $this->getCredential();
        //     $ch = curl_init();
        //     curl_setopt($ch, CURLOPT_URL, 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction');
        //     curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        //     $payload = array(
        //         'encRequest' => $encrypted_data,
        //         'access_code' => $access_code
        //     );
        //     curl_setopt($ch, CURLOPT_POST, true);
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        //     $response = curl_exec($ch);
        //     curl_close($ch);
        //     echo $response;
            
        // } catch (\Exception $e) {
        //     LogActivity::errorLog($e->getMessage());
        //     Toastr::error(__('common.operation_failed'));
        //     return redirect()->back();
        // }
        
        // $form = Form::post('https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction');

        // $form->hidden('encRequest', $encrypted_data);
        // $form->hidden('access_code', $access_code);
        
        // $form->submit();
    }
    
    function decryptforcc($encryptedText,$key)
	{
		$key = hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText = hextobin($encryptedText);
		$decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
		return $decryptedText;
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	 function hextobin($hexString) 
   	 { 
        	$length = strlen($hexString); 
        	$binString="";   
        	$count=0; 
        	while($count<$length) 
        	{       
        	    $subString =substr($hexString,$count,2);           
        	    $packedString = pack("H*",$subString); 
        	    if ($count==0)
		    {
				$binString=$packedString;
		    } 
        	    
		    else 
		    {
				$binString.=$packedString;
		    } 
        	    
		    $count+=2; 
        	} 
  	        return $binString; 
    }

    public function paymentSuccess(Request $request)
    {
        $input = $request->all();
        
        $workingKey = 'B6315C3A913D775AD9B57694F1799AA5';
        $encResponse = $_POST["encResp"];
        
        $rcvdString = decryptforcc($encResponse, $workingKey);
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            $key = urldecode($information[0]);
            $value = urldecode($information[1]);
        
            // Store the order ID, amount, and order status if the keys match
            if ($key === 'order_id') {
                $order_id = $value;
            } elseif ($key === 'amount') {
                $amount = $value;
            } elseif ($key === 'order_status') {
                $order_status = $value;
            }
        }
    	
    	if($order_status==="Success")
    	{
    // 		if (session()->has('wallet_recharge')) {
    //                  $response = $order_id;
    //                  $walletService = new WalletRepository;
    //                  session()->forget('wallet_recharge');
    //                  return $walletService->walletRecharge($amount, "8", $response);
    //              }
    //              if (session()->has('order_payment')) {
                     $response = $order_id;
                     $orderPaymentService = new OrderRepository;
                     $order_payment = $orderPaymentService->orderPaymentDone($amount, "8", $response, (auth()->check()) ? auth()->user() : null);
                     if($order_payment == 'failed'){
                         Toastr::error('Invalid Payment');
                         return redirect(url('/checkout'));
                     }
                     $payment_id = $order_payment->id;
                     Session()->forget('order_payment');
                     $datas['payment_id'] = encrypt($payment_id);
                     $datas['gateway_id'] = encrypt(8);
                     $datas['step'] = 'complete_order';
                     LogActivity::successLog('Order payment successful.');
                     return redirect()->route('frontend.checkout', $datas);
                //  }
                //  if (session()->has('subscription_payment')) {
                //      $response = $order_id;
                //      $defaultIncomeAccount = $this->defaultIncomeAccount();
                //      $seller_subscription = getParentSeller()->SellerSubscriptions;
                //      $transactionRepo = new TransactionRepository(new Transaction);
                //      $transaction = $transactionRepo->makeTransaction(getParentSeller()->first_name . " - Subsriction Payment", "in", "InstaMojo", "subscription_payment", $defaultIncomeAccount, "Subscription Payment", $seller_subscription, $amount, Carbon::now()->format('Y-m-d'), getParentSellerId(), null, null);
                //      $seller_subscription->update(['last_payment_date' => Carbon::now()->format('Y-m-d')]);
                //      SubsciptionPaymentInfo::create([
                //          'transaction_id' => $transaction->id,
                //          'txn_id' => $response,
                //          'seller_id' => getParentSellerId(),
                //         'subscription_type' => getParentSeller()->sellerAccount->subscription_type,
                //          'commission_type' => @$seller_subscription->pricing->name
                //      ]);
                //      session()->forget('subscription_payment');
                //      Toastr::success(__('common.paymeny_successfully'), __('common.success'));
                //     LogActivity::successLog('Subscription payment successful.');
                //      return redirect()->route('seller.dashboard');
                //  } else {
                //      echo "no session";
                //  }
    		
    	}
    	else if($order_status==="Aborted")
    	{
    		Toastr::error(__('common.payment_failed'));
             return redirect()->route('frontend.welcome');
    	
    	}
    	else if($order_status==="Failure")
    	{
    	   	Toastr::error(__('common.payment_failed'));
             return redirect()->route('frontend.welcome');
    	}
    	else
    	{
	        Toastr::error(__('common.payment_failed'));
             return redirect()->route('frontend.welcome');
    	
    	}

        // $credential = $this->getCredential();
        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL,@$credential->perameter_3 .'payments/' . $request->get('payment_id'));
        // curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        // curl_setopt(
        //     $ch,
        //     CURLOPT_HTTPHEADER,
        //     array(
        //         "X-Api-Key:" . @$credential->perameter_1,
        //         "X-Auth-Token:" . @$credential->perameter_2
        //     )
        // );

        // $response = curl_exec($ch);
        // $err = curl_error($ch);
        // curl_close($ch);

        // if ($err) {
        //     Toastr::error(__('common.operation_failed'));
        //     return redirect()->back();
        // } else {
        //     $data = json_decode($response);
        // }

        // if ($data->success == true) {
        //     if ($data->payment->status == "Credit") {
        //         if (session()->has('wallet_recharge')) {
        //             $amount =  $data->payment->amount;
        //             $response = $data->payment->payment_id;
        //             $walletService = new WalletRepository;
        //             session()->forget('wallet_recharge');
        //             return $walletService->walletRecharge($amount, "8", $response);
        //         }
        //         if (session()->has('order_payment')) {
        //             $amount =  $data->payment->amount;
        //             $response = $data->payment->payment_id;
        //             $orderPaymentService = new OrderRepository;
        //             $order_payment = $orderPaymentService->orderPaymentDone($amount, "8", $response, (auth()->check()) ? auth()->user() : null);
        //             if($order_payment == 'failed'){
        //                 Toastr::error('Invalid Payment');
        //                 return redirect(url('/checkout'));
        //             }
        //             $payment_id = $order_payment->id;
        //             Session()->forget('order_payment');
        //             $datas['payment_id'] = encrypt($payment_id);
        //             $datas['gateway_id'] = encrypt(8);
        //             $datas['step'] = 'complete_order';
        //             LogActivity::successLog('Order payment successful.');
        //             return redirect()->route('frontend.checkout', $datas);
        //         }
        //         if (session()->has('subscription_payment')) {
        //             $amount =  $data->payment->amount;
        //             $response = $data->payment->payment_id;
        //             $defaultIncomeAccount = $this->defaultIncomeAccount();
        //             $seller_subscription = getParentSeller()->SellerSubscriptions;
        //             $transactionRepo = new TransactionRepository(new Transaction);
        //             $transaction = $transactionRepo->makeTransaction(getParentSeller()->first_name . " - Subsriction Payment", "in", "InstaMojo", "subscription_payment", $defaultIncomeAccount, "Subscription Payment", $seller_subscription, $amount, Carbon::now()->format('Y-m-d'), getParentSellerId(), null, null);
        //             $seller_subscription->update(['last_payment_date' => Carbon::now()->format('Y-m-d')]);
        //             SubsciptionPaymentInfo::create([
        //                 'transaction_id' => $transaction->id,
        //                 'txn_id' => $response,
        //                 'seller_id' => getParentSellerId(),
        //                 'subscription_type' => getParentSeller()->sellerAccount->subscription_type,
        //                 'commission_type' => @$seller_subscription->pricing->name
        //             ]);
        //             session()->forget('subscription_payment');
        //             Toastr::success(__('common.paymeny_successfully'), __('common.success'));
        //             LogActivity::successLog('Subscription payment successful.');
        //             return redirect()->route('seller.dashboard');
        //         }
        //     }
        // } else {
        //     if (session()->has('subscription_payment')) {
        //         session()->forget('subscription_payment');
        //         Toastr::error(__('common.operation_failed'));
        //         return redirect()->route('seller.dashboard');
        //     }
        //     Toastr::error(__('common.operation_failed'));
        //     return redirect()->route('frontend.welcome');
        // }
    }

    private function getCredential(){
        $url = explode('?',url()->previous());
        if(isset($url[0]) && $url[0] == url('/checkout')){
            $is_checkout = true;
        }else{
            $is_checkout = false;
        }
        if(session()->has('order_payment') && app('general_setting')->seller_wise_payment && session()->has('seller_for_checkout') && $is_checkout){
            $credential = getPaymentInfoViaSellerId(session()->get('seller_for_checkout'), 8);
        }else{
            $credential = getPaymentInfoViaSellerId(1, 8);
        }
        return $credential;
    }
}
