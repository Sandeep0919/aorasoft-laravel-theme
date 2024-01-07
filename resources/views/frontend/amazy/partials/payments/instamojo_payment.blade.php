@php


        $merchant_data='';
//    	$working_key='B6315C3A913D775AD9B57694F1799AA5';//Shared by CCAVENUES
//    	$access_code='AVPJ90KG75BS59JPSB';//Shared by CCAVENUES

    	$working_key='0D5372417064DF2CF08950FC1E5A161C';//Shared by CCAVENUES
    	$access_code='AVWA46LA66AK94AWKA';//Shared by CCAVENUES

    	$route = route('instamojo.payment_success');
    	
    	
    	$merchant_data = [
    	    'tid' => rand(10000000, 99999999),
            'merchant_id' => '2680086',
            'order_id' => rand(10000000, 99999999),
            'amount' => $total_amount,
            'currency' => 'INR',
            'redirect_url' => $route,
            'cancel_url' => $route,
            'language' => 'EN',
            'billing_name' => @$address->name,
            'billing_address' => null,
            'billing_city' => null,
            'billing_state' => null,
            'billing_zip' => null,
            'billing_country' => null,
            'billing_tel' => @old('mobile'),
            'billing_email' => @$address->email,
            'delivery_name' => @$address->name,
            'delivery_address' => null,
            'delivery_city' => null,
            'delivery_state' => null,
            'delivery_zip' => null,
            'delivery_country' => null,
            'delivery_tel' => @old('mobile'),
            'merchant_param1' => 'additional Info.',
            'merchant_param2' => 'additional Info.',
            'merchant_param3' => 'additional Info.',
            'merchant_param4' => 'additional Info.',
            'merchant_param5' => 'additional Info.',
            'promo_code' => 'null',
            'customer_identifier' => 'null'
    	];
    	
    	$merchant_data_string = http_build_query($merchant_data);
    	
    	function hextobin($hexString)
        {
            $length = strlen($hexString);
            $binString = "";
            $count = 0;
            while ($count < $length) {
                $subString = substr($hexString, $count, 2);
                $packedString = pack("H*", $subString);
                if ($count == 0) {
                    $binString = $packedString;
                } 
                else {
                    $binString .= $packedString;
                }
                
                $count += 2;
            }
            return $binString;
        }
    	
    	function encryptforcc($plainText,$key)
    	{
    		$key = hextobin(md5($key));
    		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    		$openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    		$encryptedText = bin2hex($openMode);
    		return $encryptedText;
    	}

	    $encrypted_data = encryptforcc($merchant_data_string,$working_key); 


@endphp

<div class="col-lg-12">
    <form id="contactForm" enctype="multipart/form-data" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction" class="p-0" method="POST">
        @csrf
        <input type="hidden" name="method" value="Instamojo">
        <input type="hidden" name="amount" value="{{$total_amount - $coupon_am}}">
        <input type="hidden" name="encRequest" value="{{$encrypted_data}}">
        <input type="hidden" name="access_code" value="{{$access_code}}">
        <div class="row">
            <div class="col-lg-12">
                <label class="primary_label2 style3" for="">{{ __('common.name') }} <span>*</span></label>
                <input class="primary_input3 style4 radius_3px mb_20" type="text" required name="name" placeholder="{{ __('common.name') }}" value="{{@$address->name}}">
            </div>
            <div class="col-lg-12">
                <label class="primary_label2 style3" for="">{{ __('common.email') }} <span>*</span></label>
                <input class="primary_input3 style4 radius_3px mb_20" type="text" required name="email" placeholder="{{ __('common.email') }}" value="{{@$address->email}}">
            </div>
            <div class="col-lg-12">
                <label class="primary_label2 style3" for="">{{ __('common.mobile') }} <span>*</span></label>
                <input class="primary_input3 style4 radius_3px mb_20" type="text" required name="mobile" placeholder="{{ __('common.mobile') }}" value="{{@old('mobile')}}">
            </div>
        </div>
        <button class="btn_1 d-none" id="instamojo_btn" type="submit">{{ __('wallet.continue_to_pay') }}</button>
    </form>
</div>