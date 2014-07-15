<?php

class sms extends api
{
  public function SendToManager( $message )
  {
    return $this->SendSMS("+79218567808,+79213243303", $message);
  }

  public function SendSMS( $to, $message, $from = "enelar" )
  {
    $post =
    [
      "action" => "send",
      "number" => $to,
      "senderid" => $from,
      "message" => $message,
      "username" => "enelar",
      "type" => "json",
    ];
    $post['signature'] = $this->BuildSignature($post);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"http://api.comtube.ru/scripts/api/sms.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    curl_close ($ch);
    $obj = json_decode($server_output, true);
    return $obj;
  }

  private function BuildSignature($params)
  {
    ksort($params);
    $url = '';
    if (!is_array($params))
      return $url;
    foreach($params as $key => $value)
      $url .= $key . "=" . urlencode($value) . "&";
    $signature = md5($url . "&password=". urlencode("interteimat"));
    return $signature;
  }  
}