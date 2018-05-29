<?php
error_reporting(E_ALL);
global $curlResult;
global $curlResult2;
global $json;
global $json0;
global $json1;
global $json2;
global $json3;
global $json4;
global $userInfoResult;
global $userInfojson;
global $reqResStatus;
global $fullUIInfo;
?>
<!DOCTYPE html>
<html>
<script>
//Convert hashed fragment to PHP GETable query string in return URI from Gigya
// id_token flow would normally be used with mobile devices, and would require the fragment
// for security reasons
if(window.location.hash !=="") {
        torip=window.location.href;
        torip=torip.replace('#', '?');
        window.location.href=torip;
    }
var curlResult = {};
</script>
    <head>
        <title>Gigya OIDC - Single Page Demo RP Site</title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
        <script src="../beautify.js"></script>
    </head>
    <?php
    $errors='';
        global $validUser;
        if (isset($_GET['id_token']) && $_GET['id_token'] !== "") {
            $id_token = $_GET['id_token'];
        } else {
            $id_token = 'undefined';
            $errors .='id_token was undefined.<br />';
        }
        if (isset($_GET['code']) && $_GET['code'] !== "") {
            $code = $_GET['code'];
        } else {
            $code = 'undefined';
            $errors .='code was undefined.<br />';
        }
        // Do a simple check to determine if the user arrived directly to the page (via copy/paste)
        // Does not actually determine if user is authorized
        // A production environment would require additonal logic after validating the code/token is authentic
        if (($id_token !== 'undefined') && ($code ==='undefined')) {
            $oidcResponseType = "id_token";
            $validUser="true";
        } else if (($id_token === 'undefined') && ($code !=='undefined')) {
            $oidcResponseType = "code";
            $validUser="true";
        } else {
            $oidcResponseType = "<span style=\"color: #e76468;\">No authorized user detected.</span>";
            $validUser="false";
        }
    ?>
    <body>
        <div class="" id="main_wrapper">

        <input type="hidden" id="validUser" value='<?php echo $validUser; ?>' />
        <h2>Gigya OIDC Demo RP Site</h2>
        <h3 id="loginSuccessful">Login Successful</h3><h3 id="invalidLogin">Error</h3>
        <script>
            // This section has the same caveat as the validUser param above
            validUser=document.getElementById('validUser').value;
            if ((validUser == "true")) {
                document.getElementById('loginSuccessful').style.display='block';
                document.getElementById('invalidLogin').style.display='none';
            } else {
                document.getElementById('loginSuccessful').style.display='none';
                document.getElementById('invalidLogin').style.display='block';
            }
        </script>
        <!-- When the flow is detected as being id_token -->
        <h4>Response Type: <?php echo $oidcResponseType ?></h4>
        <div class="" id="idTokenDiv">
            Complete JWT:<br />
            <textarea id="successJWT" rows="8" style="width: 100%" readonly></textarea><br />
            Part One:<br />
            <textarea id="qrystrJWT1" rows="2" style="width: 100%" readonly></textarea><br />
            Part One Decoded:<br />
            <textarea id="qrystrJWT1b" rows="2" style="width: 100%" readonly></textarea><br />
            Part Two:<br />
            <textarea id="qrystrJWT2" rows="5" style="width: 100%" readonly></textarea><br />
            Part Two Decoded:<br />
            <textarea id="qrystrJWT2b" rows="6" style="width: 100%" readonly></textarea><br />
            Part Three (signature):<br />
            <textarea id="qrystrJWT3" rows="3" style="width: 100%" readonly></textarea><br />
        </div>

        <!-- When the flow is detected as being code -->
        <div class="" id="codeFlowDiv">
            This flow would normally be handled entirely on the server. These values are only echoed here for reference.<br /><br />
            Code received from authorize endpoint (send this to the token endpoint):<br />
            <textarea id="codeFlowCode" rows="2" style="width: 100%" readonly></textarea><br />
            Access token (received from token endpoint; response[0]):<br />
            <textarea id="qrystrJWT4" rows="2" style="width: 100%" readonly></textarea><br />
            Refresh token (received from token endpoint):<br />
            <textarea id="qrystrJWT6" rows="2" style="width: 100%" readonly></textarea><br />
            Decoded id_token data (received from token endpoint; response[3]):<br />
            <textarea id="qrystrJWT5" rows="12" style="width: 100%" readonly></textarea><br />
            Decoded userinfo response (returned from userinfo endpoint in exchange for access_token (above)):<br />
            <textarea class="" id="userInfoResponse" rows="12" style="width: 100%" readonly></textarea><br />
        </div>

        <div class="" id="serverRequestResponseDiv">
        </div>
        <script>
        var oidcResponseType = '<?php echo $oidcResponseType ?>';
        var clientSecret="";
        var qrystrJWT;
        var splitJWT = {};
        var qrystrJWT1;
        var qrystrJWT2;
        var qrystrJWT3;
        var qrystrJWT1b;
        var qrystrJWT2b;
        var qrystrJWT5;

        // If id_token flow
        if (oidcResponseType === "id_token") {
            document.getElementById('idTokenDiv').style.display='block';
            document.getElementById('codeFlowDiv').style.display='none';
            $(document).ready(function() {
                qrystrJWT = '<?php echo $id_token ?>';
                splitJWT = qrystrJWT.split('.');
                document.getElementById('successJWT').value = qrystrJWT;
                if (splitJWT.length === 3) {
                    qrystrJWT1 = splitJWT[0];
                    qrystrJWT2 = splitJWT[1];
                    qrystrJWT3 = splitJWT[2];
                    document.getElementById('qrystrJWT1').value=qrystrJWT1;
                    document.getElementById('qrystrJWT2').value=qrystrJWT2;
                    document.getElementById('qrystrJWT3').value=qrystrJWT3;
                    document.getElementById('qrystrJWT1b').value=url_base64_decode(qrystrJWT1);
                    document.getElementById('qrystrJWT2b').value=url_base64_decode(qrystrJWT2);
                } else {
                    console.warn('The query string does not contain a valid JWT.');
                }


            });
        // Else if code flow
        } else if (oidcResponseType === "code") {
            document.getElementById('idTokenDiv').style.display='none';
            document.getElementById('codeFlowDiv').style.display='block';
            $(document).ready(function() {
                qrystrAuthCode = '<?php echo $code ?>';
                splitJWT = qrystrAuthCode.split('.');
            });
        }

        </script>
<?php

// The rest of the code below pertains almost entirely to the code flow

/* startCodeFlow CODE from the query string and sends it back to the token endpoint
   in order to receive an access_token
*********************************************************************************** */
function startCodeFlow() {

    $strApiKey = "YOUR-API-KEY-HERE";
    $url = "https://fidm.au1.gigya.com/oidc/op/v1.0/" . $strApiKey . "/token";
    $authHeader="Basic ";
    $clientID = "YOUR-CLIENT-ID-HERE";
    $clientSecret="YOUR-CLIENT-SECRET-HERE";
    $hashedAuthString=base64_encode($clientID . ":" . $clientSecret);
    $authHeader .= $hashedAuthString;

    $fields = array(
        'code'=>$_GET['code'],
        'grant_type'=>'authorization_code',
        'redirect_uri'=>'https://somedomain.com/loggedIn.php'
    );
    $postvars='';
    $sep='';
    foreach($fields as $key=>$value)
    {
        $postvars.= $sep.urlencode($key).'='.urlencode($value);
        $sep='&';
    }

    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . $authHeader));
    curl_setopt($ch,CURLOPT_POST,count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $result = curl_exec($ch);
    curl_close($ch);
    global $curlResult;
    $curlResult = json_encode($result);
    $result2 = $result;
    global $json;
    $json = json_decode($result2, true);
}

/* THIS NOW RETURNS THE ACCESS TOKEN TO THE USER_INFO ENDPOINT TO GET THE USER'S INFO
*********************************************************************************** */

function getUserInfoEndpointData($accessToken) {
    $strApiKey = "YOUR-API-KEY-HERE";
    $url = "https://fidm.au1.gigya.com/oidc/op/v1.0/" . $strApiKey . "/userinfo";
    $bearAccessToken="Bearer " . $accessToken;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . $bearAccessToken));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

    $resultUI = curl_exec($ch);

    // CHECK STATUS TEST
    $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    global $reqResStatus;
    $reqResStatus = $resultStatus;

    // END CHECK STATUS
    curl_close($ch);
    global $userInfoResult;
    $userInfoResult = json_encode($resultUI);
    $result3 = $resultUI;
    global $userInfojson;
    $userInfojson = json_decode($result3, true); // returns array()
    global $fullUIInfo;
    $fullUIInfo = $userInfojson;
}

/* END OF USER_INFO ENDPOINT CODE
*********************************************************************************** */

/* Now we echo everything out to the client for demo purposes, in production
   the RP would now add/update their database with the user's info and proceed to
   deliver their content.
*********************************************************************************** */
if($code !=='undefined') {
    startCodeFlow();
}
$jsonSize=count($json);
if ($jsonSize===5) {
    global $json0;
    global $json1;
    global $json2;
    global $json3;
    global $json4;

    $json0 = $json['access_token'];
    $json1 = $json['token_type'];
    $json2 = $json['expires_in'];
    $json3 = $json['id_token'];
    $json4 = $json['refresh_token'];
    // If you want to test the access_token:
    // getUserInfoEndpointData($json0);
} else {
  echo '<br /><br />Response:<br />' . $curlResult;
}

?>
<script>

// Necessary to decode the URL encoded base64 data received from Gigya
function url_base64_decode(str) {
  var output = str.replace(/-/g, '+').replace(/_/g, '/');
  switch (output.length % 4) {
    case 0:
      break;
    case 2:
      output += '==';
      break;
    case 3:
      output += '=';
      break;
    default:
      throw 'Invalid base64!';
  }
  var result = window.atob(output);
  try{
    return decodeURIComponent(escape(result));
  } catch (err) {
    return result;
  }
}

var jsonSize = '<?php echo $jsonSize ?>';
console.log('JSON Size: ' + jsonSize);

var json0;
var json1;
var json2;
var json3;
var json4;
var jsonFull;
var jsonIdTokenData;
var codeFlowCodeStr;
if (oidcResponseType === "code") {
    if (jsonSize==5) {
        codeFlowCodeStr = '<?php echo $code ?>';
        json0 = '<?php echo $json0 ?>';
        json1 = '<?php echo $json1 ?>';
        json2 = '<?php echo $json2 ?>';
        json3 = '<?php echo $json3 ?>';
        json4 = '<?php echo $json4 ?>';
        jsonFull = json0 + json1 + json2 + json3 + json4;
        jsonIdTokenData = json3.split('.');

    } else {
        jsonFull = 'Your request timed out and the token is no longer valid. Please start again.';
    }
    document.getElementById('codeFlowCode').value=codeFlowCodeStr;
    document.getElementById('qrystrJWT4').value=json0;
    document.getElementById('qrystrJWT5').value=js_beautify(url_base64_decode(jsonIdTokenData[1]));
    document.getElementById('qrystrJWT6').value=json4;
}
var reqResStatus = '<?php echo $reqResStatus ?>';
document.getElementById('serverRequestResponseDiv').innerHTML = reqResStatus;
if ((reqResStatus !== "") && (reqResStatus !== "200")) {
    document.getElementById('serverRequestResponseDiv').style.display='block';
} else {
    document.getElementById('serverRequestResponseDiv').style.display='none';
}
var fullUIInfo;
if (reqResStatus === "200") {
    fullUIInfo = '<?php echo json_encode($fullUIInfo); ?>';
    UIjson= JSON.stringify(JSON.parse(fullUIInfo));
    document.getElementById('userInfoResponse').value = js_beautify(UIjson);
}
$(document).ready(function() {
});

</script>
    </div><!-- /main_wrapper -->
</body>
</html>
