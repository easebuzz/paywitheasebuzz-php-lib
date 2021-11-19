

\# paywitheasebuzz-php-lib

PHP integration kit for pay with easebuzz pay.easebuzz.in

\# Software Requirement

\*setup php kits on test/development/production environment install below software\*

\1. PHP 5.5 or above

\2. php-curl (note: php-curl install based on php version).

\3. apache or wamp or xampp server

\# easebuzz Documentation for kit integration

https://docs.easebuzz.in/

\# paywitheasebuzz-php-lib kit, try it yourself

\### for apache server

\1. clone paywitheasebuzz-php-lib on your system.

\2. unzip paywitheasebuzz-php-lib

\3. open the kit folder from the terminal or command prompt.

paywitheasebuzz-php-lib/

\4. run the command from the terminal or command prompt.

php -S localhost:3000

\### for wamp or xampp server

\1. clone paywitheasebuzz-php-lib on your system.

\2. unzip paywitheasebuzz-php-lib.

\3. copy unzip paywitheasebuzz-php-lib and paste below location.

\1. for xampp

xampp\htdocs\ (paste unzip paywitheasebuzz-php-lib)

\2. for wamp

wamp\www\ (paste unzip paywitheasebuzz-php-lib)

\4. open xampp or wamp server

\5. start the server

\# Process for integrating paywitheasebuzz-php-lib kit in "Project"

\1. copy and paste easebuzz-lib folder in your project directory.

\2. create or prepare two PHP files in your project. Which will be called Easebuzz class methods

or functions. for example below.

\1. easebuzz.php (use for calling all methods or function of paywitheasebuzz-php-lib kit)

\2. response.php (use for handle initiate payment API response)

\3. include easebuzz\_payment\_gateway.php file in easebuzz.php

\```

include\_once('easebuzz-lib/easebuzz\_payment\_gateway.php');





\```

\4. set $MERCHANT\_KEY, $SALT and $ENV.

\```

$MERCHANT\_KEY = "10PBP71ABZ2";

$SALT = "ABC55E8IBW";

$ENV = "test"; // set environment name

\```

\5. create an Easebuzz class object and pass $MERCHANT\_KEY, $SALT and $ENV.

\```

$easebuzzObj = new Easebuzz($MERCHANT\_KEY, $SALT, $ENV);

\```

\6. call Easebuzz class methods and functions based on your requirements.

\1. Initiate Payment API

\*POST Format and call initiatePaymentAPI\*

\```

$postData = array (

"txnid" => "T3SAT0B5OL",

"amount" => "100.0",

"firstname" => "jitendra",

"email" => "test@gmail.com",

"phone" => "1231231235",

"productinfo" => "Laptop",

"surl" => "http://localhost:3000/response.php",

"furl" => "http://localhost:3000/response.php",

"udf1" => "aaaa",

"udf2" => "aaaa",

"udf3" => "aaaa",

"udf4" => "aaaa",

"udf5" => "aaaa",

"address1" => "aaaa",

"address2" => "aaaa",

"city" => "aaaa",

"state" => "aaaa",

"country" => "aaaa",

"zipcode" => "123123"

);

$easebuzzObj->initiatePaymentAPI($postData);

\```

\## Advanced Parameter:

\* "sub\_merchant\_id" : -

Mandatory parameter if you are using sub-aggregator feature otherwise not

mandatory.Here Pass sub-aggregator id.You can create sub aggregator from Easebuzz

dashboard web portal."





\* "unique\_id" : -

Mandatory parameter if you are using customer save card feature otherwise not

mandatory. This is the customer's unique id. You need to enable the save card feature from the

Easebuzz dashboard web portal.

\* "split\_payments" : -

Mandatory parameter if you are using split payment feature otherwise not mandatory.You

need to pass here payment slots in JSON format like {"label\_HDFC": 100,"label\_icici":100},

Please use the label provided by Easebuzz team.

\```

e.g. "split\_payments" => { "axisaccount" : 100, "hdfcaccount" : 100}

\```

\2. Transaction API

\*POST Format and call transaction API\*

\```

$postData = array (

"txnid" => "TZIF0SS24C",

"amount" => "1.03",

"email" => "test@gmail.com",

"phone" => "1231231235"

);

$result = $easebuzzObj->transactionAPI($postData);

\```

\3. Transaction API (by date)

\*POST Format and call transactionDateAPI\*

\```

$postData = array(

"merchant\_email" => "jitendra@gmail.com",

"transaction\_date" => "06-06-2018"

);

$result = $easebuzzObj->transactionDateAPI($postData);

\```

\4. Refund API

\*POST Format and call refundAPI\*

\```

$postData = array(

"txnid" => "ASD20088",

"refund\_amount" => "1.03",





"phone" => "1231231235",

"email" => "test@gmail.com",

"amount" => "1.03"

);

$result = $easebuzzObj->refundAPI($postData);

\```

\5. Payout API

\*POST Format and call payoutAPI\*

\```

$postData = array(

"merchant\_email" => "jitendra@gmail.com",

"payout\_date" => "08-06-2018"

);

$result = $easebuzzObj->payoutAPI($postData);

\```

\6. Handle Initiate Payment API response

\* Note:- initiate payment API response will get for success URL or failure URL\*

\1. include easebuzz\_payment\_gateway.php file in response.php

\```

include\_once('easebuzz-lib/easebuzz\_payment\_gateway.php');

\```

\2. set $SALT

\```

$SALT = "ABC55E8IBW";

\```

\3. create an Easebuzz class object and pass $SALT.

\```

$easebuzzObj = new Easebuzz($MERCHANT\_KEY = null, $SALT, $ENV = null);

\```

\4. call Easebuzz class methods or functions

\```

$result = $easebuzzObj->easebuzzResponse( $\_POST );

\```

\7. # If the server is not supporting the header():-

\*Note:- Please follow the below steps.\*

\1. Go to the path easebuzz-lib/payment.php

\2. Open the payment.php file





\3. Goto the function \_paymentResponse($result) and un-comment the below code.

\```

function \_paymentResponse($result){

if ($result->status === 1){

// first way (comment below line)

// header( 'Location:' . $result->data );

// second way (un-comment below code)

echo "

<script type='text/javascript'>

window.location ='".$result->data."'

</script>

";

exit();

}else{

//echo '<h3>'.$result['data'].'</h3>';

print\_r(json\_encode($result));

}

}

\```

\8. # If you have to enable ease checkout (iframe) in php kit :-

\*Note:- Please follow the below steps.\*

\1. Go to the path easebuzz-lib/easebuzz\_payment\_gateway.php

\2. Open the easebuzz\_payment\_gateway.php file

\3. Go to the function initiatePaymentAPI($params, **$redirect=False**) change in below

code.

\```

public function initiatePaymentAPI($params, $redirect=False){

return initiate\_payment($params, $redirect, $this->MERCHANT\_KEY,

$this->SALT, $this->ENV);

}

\```

\4. $redirect parameter as **False** for ease checkout (iframe).

\5. $redirect parameter as **True** for Hosted checkout .

