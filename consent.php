<?php
    include_once("../../libs/GSSDK.php"); // Location of Gigya's PHP SDK

    $secret="YOUR-PARTNER-SECRET-HERE";
    $errors="<br />There may be errors processing your request due to missing or incorrect values: <br /><br />";
    $errorsExist = 'false';
    if (isset($_GET['context']) && $_GET['context'] !== "") {
        $context = $_GET['context'];
    } else {
        $context = 'undefined';
        $errors .='context was undefined.<br />';
    }
    if (isset($_GET['clientID']) && $_GET['clientID'] !== "") {
        $clientID = $_GET['clientID'];
    } else {
        $clientID = 'undefined';
        $errors .='clientID was undefined.<br />';
    }
    if (isset($_GET['scope']) && $_GET['scope'] !== "") {
        $scope = $_GET['scope'];
        $scope = preg_replace('/[+]/', ' ', $scope);
    } else {
        $scope = 'undefined';
        $errors .= 'scope was undefined.<br />';
    }
    if (isset($_GET['UID']) && $_GET['UID'] !== "") {
        $UID = $_GET['UID'];
    } else {
        $UID = 'undefined';
        $errors .= 'UID was undefined.<br />';
    }
    if (isset($_GET['UIDSignature']) && $_GET['UIDSignature'] !== "") {
        $UIDSignature = $_GET['UIDSignature'];
    } else {
        $UIDSignature = 'undefined';
        $errors .= 'UIDSignature was undefined.<br />';
    }
    if (isset($_GET['signatureTimestamp']) && $_GET['signatureTimestamp'] !== "") {
        $signatureTimestamp = $_GET['signatureTimestamp'];
    } else {
        $signatureTimestamp = 'undefined';
        $errors .= 'signatureTimestamp was undefined.<br />';
    }
    if ($errors !== "<br />There may be errors processing your request due to missing or incorrect values: <br /><br />") {
        $errors .= "<br /><br />";
        echo $errors;
    }
    if (($scope ==="undefined") || ($clientID === "undefined") || ($context === "undefined") || ($UID === "undefined")) {
        echo "<br /><br /><span style='font-weight: bold; color: red;'>Too many errors occurred; please try again.</span>";
        $errorsExist = 'true';
    } else {
      function signatureReplaceBase64($sig) {
          $correctedSignature=null;
          if ($sig) {
              $correctedSignature= preg_replace("/=$/", "", $sig);
              $correctedSignature= preg_replace("/=$/", "", $correctedSignature);
              $correctedSignature= preg_replace("/[+]/", "-", $correctedSignature); // -
              $correctedSignature= preg_replace("/\//", "_", $correctedSignature); // _
              return $correctedSignature;
          } else {
              return false;
          }
      }
      //construct signature
      $consentObj = json_encode(array(
          "scope" => $scope,
          "clientID" => $clientID,
          "context" => $context,
          "UID" => $UID,
          "consent" => true // You can use a custom Profile Update screen to collect user consent for sharing their data with the RP (OIDC Relying Party).
      ));
      $consentObjSig = signatureReplaceBase64(SigUtils::calcSignature($consentObj, $secret));

      echo '?mode=afterConsent&consent=' . $consentObj . '&sig=' . $consentObjSig;
    }
?>
