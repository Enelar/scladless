<?php

class watch extends api
{
  protected function Reserve()
  {
    $id = 10495457;

    return $true;
  }

  private function Grab( $id )
  {
    $g = LoadModule('api/grab', 'grabber');
    $obj = json_decode($g->Grab($id), true);
    if ($obj['success'])
      $this->PageCache($obj['url'], $obj['body'], $obj['shot']);
    return $obj;
  }

  private function PageCache( $url, $body, $img )
  {
    $res = db::Query("INSERT INTO market.pagecache(url, body, img) VALUES ($1, $2, decode($3, 'base64')) RETURNING snap",
      [$url, $body, $img], true);
    var_dump($res);
  }

  private function NextCard()
  {
    $res = db::Query("SELECT * FROM market.cards WHERE snap IS NULL OR now() - snap > '6 hours'::interval ORDER BY snap ASC LIMIT 1", [], true);
    //phoxy_protected_assert($res, ["error" => "Noting to update"]);
    if (!$res)
      return null;

    return $res['ymid'];
  }

  private function UpdateSubscribed( )
  {
    $id = $this->NextCard();
    if ($id === null)
      return false;

    $obj = $this->Grab($id);
    if (!$obj['success'])
      return false;
    var_dump($obj);
    db::Query("UPDATE market.cards SET snap=now() WHERE ymid=$1", [$id], true);

    $res = db::Query("INSERT INTO market.slices(ymid, price, shop) VALUES ($1, $2::character varying[]::integer[], $3) RETURNING snap",
      [$id, $obj['prices'], $obj['shops']], true);
    return !!$res;
  }

  protected function RobotUpdate()
  {
    echo "<script language='javascript'>setTimeout(function() { document.location.search='?'}, 000)</script>";
    return $this->UpdateSubscribed();
  }
}