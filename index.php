<?php

    include_once 'easepay-lib.php';

    $MERCHANT_KEY="XXXXXXXXX";
    $SALT='XXXXXXXXXX';
    $ENV='test';  // uncomment it for test env.(testpay.easebuzz.in)
//    $ENV='prod'; // uncomment it for production env.(pay.easebuzz.in)

    $posted = array();
    if(!empty($_POST)) {
      foreach($_POST as $key => $value) {
        $posted[$key] = htmlentities($value, ENT_QUOTES);
        $posted[$key] = trim($value);
      }
    }
    $formError = 0;
    if(sizeof($posted) > 0) {
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

        if(
              empty($posted['amount'])
              || empty($posted['firstname'])
              || empty($posted['email'])
              || empty($posted['phone'])
              || empty($posted['productinfo'])
              || empty($posted['surl'])
              || empty($posted['furl'])
        ) {
            $formError = 1;
        }



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
    }

?>
<html>
    <head>
    </head>
    <body>
    <?php if($formError) { ?>
      <span style="color:red">Please fill all mandatory fields.</span>
      <br/>
      <br/>
    <?php } ?>
      <form action="index.php" method="post" name="Form">
      <table>
        <tr>
          <td><b>Mandatory Parameters</b></td>
        </tr>
        <tr>
          <td>Amount: </td>
          <td><input name="amount" value="<?php echo (empty($posted['amount'])) ? '' : $posted['amount'] ?>" /></td>
          <td>First Name: </td>
          <td><input name="firstname" id="firstname" value="<?php echo (empty($posted['firstname'])) ? '' : $posted['firstname']; ?>" /></td>
        </tr>
        <tr>
          <td>Email: </td>
          <td><input name="email" id="email" value="<?php echo (empty($posted['email'])) ? '' : $posted['email']; ?>" /></td>
          <td>Phone: </td>
          <td><input name="phone" value="<?php echo (empty($posted['phone'])) ? '' : $posted['phone']; ?>" /></td>
        </tr>
        <tr>
          <td>Product Info: </td>
          <td colspan="3"><input name="productinfo" value="<?php echo (empty($posted['productinfo'])) ? '' : $posted['productinfo'] ?>" size="64" /></td>
        </tr>
        <tr>
          <td>Success URI: </td>
          <td colspan="3"><input name="surl" value="<?php echo (empty($posted['surl'])) ? 'http://localhost/payeasebuzz-phplib/response.php' : $posted['surl'] ?>" size="64" /></td>
        </tr>
        <tr>
          <td>Failure URI: </td>
          <td colspan="3"><input name="furl" value="<?php echo (empty($posted['furl'])) ? 'http://localhost/payeasebuzz-phplib/response.php' : $posted['furl'] ?>" size="64" /></td>
        </tr>
        <tr>
          <td><b>Optional Parameters</b></td>
        </tr>
        <tr>
          <td>UDF1: </td>
          <td><input name="udf1" value="<?php echo (empty($posted['udf1'])) ? '' : $posted['udf1']; ?>" /></td>
          <td>UDF2: </td>
          <td><input name="udf2" value="<?php echo (empty($posted['udf2'])) ? '' : $posted['udf2']; ?>" /></td>
        </tr>
        <tr>
          <td>UDF3: </td>
          <td><input name="udf3" value="<?php echo (empty($posted['udf3'])) ? '' : $posted['udf3']; ?>" /></td>
          <td>UDF4: </td>
          <td><input name="udf4" value="<?php echo (empty($posted['udf4'])) ? '' : $posted['udf4']; ?>" /></td>
        </tr>
        <tr>
          <td>UDF5: </td>
          <td><input name="udf5" value="<?php echo (empty($posted['udf5'])) ? '' : $posted['udf5']; ?>" /></td>
        </tr>
        <tr>
            <td colspan="4"><input type="submit" value="Submit" /></td>
        </tr>
      </table>
    </form>
    </body>
</html>
