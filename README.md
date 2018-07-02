# paywitheasebuzz-php-lib
PHP integration kit for pay with easebuzz pay.easebuzz.in

# Requirement Software
*setup php kits on test/development/production environment install below software*

1. PHP 5.5 or above
2. php-curl
3. apache or wamp or xampp server

# easebuzz Documentation for kit integration
https://docs.easebuzz.in/

# Process for test paywitheasebuzz-php-lib kit

### for apache server
1. clone paywitheasebuzz-php-lib on your's system.
2. unzip paywitheasebuzz-php-lib
3. open the kit folder from the terminal or command prompt.
    paywitheasebuzz-php-lib/
4. run the command from the terminal or command prompt.
    php -S localhost:3000

### for wamp or xampp server
1. clone paywitheasebuzz-php-lib on your's system.
2. unzip paywitheasebuzz-php-lib.
3. copy unzip paywitheasebuzz-php-lib and paste below location.
    1. for xampp
        xampp\htdocs\ (paste unzip paywitheasebuzz-php-lib)
    2. for wamp
        wamp\www\ (paste unzip paywitheasebuzz-php-lib)
4. open xampp or wamp server
5. start the server

# Process for integrating paywitheasebuzz-php-lib kit in "Project"

1. Copy and Paste easebuzz-lib folder in your's project directory.
2. Create or Prepare two PHP file in your's project. Which will be called Easebuzz class methods or functions. for example below.
    1. easebuzz.php
    2. response.php
3. include easebuzz_payment_gateway.php file in easebuzz.php
    ```
        include_once('easebuzz-lib/easebuzz_payment_gateway.php');
    ```
4. set $MERCHANT_KEY, $SALT and $ENV.
    ```
        $MERCHANT_KEY = "10PBP71ABZ2";
        $SALT = "ABC55E8IBW";         
        $ENV = "test";   // set enviroment name
    ```
5. create Easebuzz class object and pass $MERCHANT_KEY, $SALT and $ENV.
    ```
        $easebuzzObj = new Easebuzz($MERCHANT_KEY, $SALT, $ENV);
    ```
6. call Easebuzz class methods and function based on your's requirements.
    1. Initiate Payment API
        *POST Format*
        ```
            Array ( [txnid] => T3SAT0B5OL [amount] => 100.0 [firstname] => jitendra [email] => test@gmail.com [phone] => 1231231235 [productinfo] => Laptop [surl] => http://localhost:3000/response.php [furl] => http://localhost:3000/response.php [udf1] => aaaa [udf2] => aa [udf3] => aaaa [udf4] => aaaa [udf5] => aaaa [address1] => aaaa [address2] => aaaa [city] => aaaa [state] => aaaa [country] => aaaa [zipcode] => 123123 )
        ```
        *call initiatePaymentAPI*
        ```
            $easebuzzObj->initiatePaymentAPI($_POST);    
        ```
    2. Transaction API
        *POST Format*
        ```
            Array ( [txnid] => TZIF0SS24C [amount] => 1.03 [email] => test@gmail.com [phone] => 1231231235 )
        ```
        *call transactionAPI*
        ```
            $result = $easebuzzObj->transactionAPI($_POST);    
        ```
    3. Transaction API (by date)
        *POST Format*
        ```
            Array ( [merchant_email] => jitendra@gmail.com [transaction_date] => 06-06-2018 )
        ```
        *call transactionDateAPI*
        ```
            $result = $easebuzzObj->transactionDateAPI($_POST);
        ```
    4. Refund API
        *POST Format*
        ```
            Array ( [txnid] => ASD20088 [refund_amount] => 1.03 [phone] => 1231231235 [email] => test@gmail.com [amount] => 1.03 )
        ```
        *call refundAPI*
        ```
            $result = $easebuzzObj->refundAPI($_POST);    
        ```
    5. Payout API
        *POST Format*
        ```
            Array ( [merchant_email] => jitendra@gmail.com [payout_date] => 08-06-2018 )
        ```
        *call payoutAPI*
        ```
            $result = $easebuzzObj->payoutAPI($_POST);
        ```
    6. Response of Inititate Payment API
        * Note:- initiate payment API response will get for success URL or failure URL*
        1. include easebuzz_payment_gateway.php file in response.php
            ```
                include_once('easebuzz-lib/easebuzz_payment_gateway.php');
            ```
        2. set $SALT
            ```
                $SALT = "ABC55E8IBW";
            ```
        3. create Easebuzz class object and pass $SALT.
            ```
                $easebuzzObj = new Easebuzz($MERCHANT_KEY = null, $SALT, $ENV = null);
            ```
        4. call Easebuzz class methods or functions
            ```
                result = $easebuzzObj->easebuzzResponse( $_POST );
            ```