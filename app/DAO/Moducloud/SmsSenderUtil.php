<?php

// Works well with php5.3 and php5.6.

namespace App\DAO\Moducloud;

class SmsSenderUtil {
    function getRandom() {
        return rand(100000, 999999);
    }

    function calculateSig($secretkey, $random, $curTime, $phoneNumbers) {
        $phoneNumbersString = $phoneNumbers[0];
        for ($i = 1; $i < count($phoneNumbers); $i++) {
            $phoneNumbersString .= ("," . $phoneNumbers[$i]);
        }
        return hash("sha256", "secretkey=".$secretkey."&random=".$random
            ."&time=".$curTime."&mobile=".$phoneNumbersString);
    }

    function calculateSigForTemplAndPhoneNumbers($secretkey, $random, $curTime, $phoneNumbers) {
        $phoneNumbersString = $phoneNumbers[0];
        for ($i = 1; $i < count($phoneNumbers); $i++) {
            $phoneNumbersString .= ("," . $phoneNumbers[$i]);
        }
        return hash("sha256", "secretkey=".$secretkey."&random=".$random
            ."&time=".$curTime."&mobile=".$phoneNumbersString);
    }

    function phoneNumbersToArray($nationCode, $phoneNumbers) {
        $i = 0;
        $tel = array();
        do {
            $telElement = new \stdClass();
            $telElement->nationcode = $nationCode;
            $telElement->mobile = $phoneNumbers[$i];
            array_push($tel, $telElement);
        } while (++$i < count($phoneNumbers));
        return $tel;
    }

    function calculateSigForTempl($secretkey, $random, $curTime, $phoneNumber) {
        $phoneNumbers = array($phoneNumber);
        return $this->calculateSigForTemplAndPhoneNumbers($secretkey, $random, $curTime, $phoneNumbers);
    }

    function sendCurlPost($url, $dataObj) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($dataObj))));
        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }
        curl_close($curl);
        return $result;
    }
}
?>
