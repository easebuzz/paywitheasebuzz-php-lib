# paywitheasebuzz-php-lib
PHP integration kit for pay with easebuzz pay.easebuzz.in

## Steps: 

1. Open index.php 
2. provide key and salt
  $MERCHANT_KEY="XXXXXXXXX";
  $SALT='XXXXXXXXXX';
3. Open response.php
4. provide salt
   $SALT='XXXXXXXXX';
5. Run index.php

To integrate with your website just change this parameters dynamically in easepay_page function :

<code>
easepay_page(array('key' => $MERCHANT_KEY,
'txnid' => $txnid,
'amount' => $posted['amount'],
'firstname' => $posted['firstname'],
'email' => $posted['email'],
'phone' => $posted['phone'],
'udf1' => $posted['udf1'],
'udf2' => $posted['udf2'],
'udf3' => $posted['udf3'],
'udf4' => $posted['udf4'],
'udf5' => $posted['udf5'],
'productinfo' =>$posted['productinfo'],
'surl' => $posted['surl'],
'furl' => $posted['furl']), $SALT, $ENV);
</code>

