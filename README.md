# paywitheasebuzz-php-lib
PHP integration kit for pay with easebuzz pay.easebuzz.in

# Requirement Software
*setup php kits on test/devlopement/production enviroment install below software*

1. PHP 5.5 or above
2. php-curl
3. apache or wamp or xampp server

# easebuzz Documentation for kit integration
https://docs.easebuzz.in/

# Process for test paywitheasebuzz-php-lib kit

### for apache server
1. clone paywitheasebuzz-php-lib on your's system.
2. unzip paywitheasebuzz-php-lib
3. open kit folder from the terminal or command prompt.
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
5. start server

# Process for integrate paywitheasebuzz-php-lib kit in <Your Project>

1. Copy and Paste easebuzz-lib folder in your's project directory.
2. Create or Prepare two PHP file in your's project. Which will be call Easebuzz class methods or functions. for example below.
	1. easebuzz.php
	2. response.php
3. include easebuzz_payment_gateway.php file in easebuzz.php
	'''php
		<?php
			include_once('easebuzz-lib/easebuzz_payment_gateway.php');
		?>
	'''
4. set $MERCHANT_KEY, $SALT and $ENV.
	'''php
		<?php
			$MERCHANT_KEY = "10PBP71ABZ2";
		    $SALT = "ABC55E8IBW";         
		    $ENV = "test";   // set enviroment name
		?>
	'''
5. create Easebuzz class object and pass $MERCHANT_KEY, $SALT and $ENV.
	'''php
		<?php
			$easebuzzObj = new Easebuzz($MERCHANT_KEY, $SALT, $ENV);
		?>
	'''
6. call Easebuzz class methods and function based on your's requirements.
	1. Inititate Payment API
		*POST Format
			Array ( [txnid] => T3SAT0B5OL [amount] => 100.0 [firstname] => jitendra [email] => test@gmail.com [phone] => 1231231235 [productinfo] => Laptop [surl] => http://localhost:3000/response.php [furl] => http://localhost:3000/response.php [udf1] => aaaa [udf2] => aa [udf3] => aaaa [udf4] => aaaa [udf5] => aaaa [address1] => aaaa [address2] => aaaa [city] => aaaa [state] => aaaa [country] => aaaa [zipcode] => 123123 )
		.*
		'''php
			<?php
				$easebuzzObj->initiatePaymentAPI($_POST);	
			?>
		'''
	2. Transaction API
		*POST Format
			Array ( [txnid] => TZIF0SS24C [amount] => 1.03 [email] => test@gmail.com [phone] => 1231231235 )
		.*
		'''php
			<?php
				$result = $easebuzzObj->transactionAPI($_POST);	
			?>
		'''
	3. Transaction API (by date)
		*POST Format
			Array ( [merchant_email] => jitendra@gmail.com [transaction_date] => 06-06-2018 )
		.*
		'''php
			<?php
				$result = $easebuzzObj->transactionDateAPI($_POST);
			?>	
		'''
	4. Refund API
		*POST Format
			Array ( [txnid] => ASD20088 [refund_amount] => 1.03 [phone] => 1231231235 [email] => test@gmail.com [amount] => 1.03 )
		.*
		'''php
			<?php
				$result = $easebuzzObj->refundAPI($_POST);	
			?>
		'''
	5. Payout API
		*POST Format
			Array ( [merchant_email] => jitendra@gmail.com [payout_date] => 08-06-2018 )
		.*
		'''php
			<?php
				$result = $easebuzzObj->payoutAPI($_POST);
			?>
		'''
	6. Response of Inititate Payment API
		*Note :- initiate payment api response will get for success url or failure url*
		1. include easebuzz_payment_gateway.php file in response.php
			'''php
				<?php
					include_once('easebuzz-lib/easebuzz_payment_gateway.php');
				?>
			'''
		2. set $SALT
			'''php
				<?php
					$SALT = "ABC55E8IBW";
				?>
			'''
		3. create Easebuzz class object and pass $SALT.
			'''php
				<?php
					$easebuzzObj = new Easebuzz($MERCHANT_KEY = null, $SALT, $ENV = null);
				?>
			'''
		4. call Easebuzz class methods or functions
			'''php
				<?php
					result = $easebuzzObj->easebuzzResponse( $_POST );
				?>
			'''