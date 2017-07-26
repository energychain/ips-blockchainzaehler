<?
/*****************************************************************************
  Mit diesem Script können IP Symcon Variablen (=Zählerstände) in die
  STROMDAO Energy Blockchain geschrieben werden.
  
  Verwendung:
  - Script via IP-Symcon Management Console anlegen.
  - Bei der Variable mit dem Zählerstand dieses Script bei Wertänderung
    ausführen lassen
  
  Das Script wird beim ersten Ereignis eine öffentliche Kennung und einen
  Zugriffsschlüssel erzeugen. Anschließend wird der Wert in die Energy
  Blockchain als Transaktion geschrieben.
  
  Ab diesem Zeitpunkt können die Werte dann verwendet werden in Smart Contracts
  oder anderen Blockchain Anwendungen (zum Beispiel Abrechnung der Nebenkosten.)
  
  Bei Fragen bitte Mail an thorsten.zoerner@stromdao.de
  
  Beispiel Verwendung:
  https://demo.stromdao.de/showcase/sc_infra_frm.html
******************************************************************************/
function createRandomString($l) {
	 $chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-";
	 $randstring = '';
    for ($i = 0; $i < $l; $i++) {
        $randstring .= $chars[rand(0, strlen($chars))];
    }
    return $randstring;
}

$thing=$_IPS['SELF'];
$value=0;

if($_IPS["SENDER"]=="Variable")  {
	$thing=$_IPS['VARIABLE'];
	$value=round($_IPS['VALUE']*1000); // Die Energy Blockchain arbeitet nur mit Ganzzahlen
}


if(!IPS_GetObjectIDByName("public_name",$thing)) {
   $public_name = IPS_CreateVariable(3);
   IPS_SetName($public_name, "public_name");
   IPS_SetParent($public_name, $thing);
	SetValue($public_name,createRandomString(10));
}
if(!IPS_GetObjectIDByName("private_key",$thing)) {
   $private_key = IPS_CreateVariable(3);
   IPS_SetName($private_key, "private_key");
   IPS_SetParent($private_key, $thing);
	SetValue($private_key,createRandomString(10));
}

$pub=GetValue(IPS_GetObjectIDByName("public_name",$thing));
$priv=GetValue(IPS_GetObjectIDByName("private_key",$thing));

if(!IPS_GetObjectIDByName("token",$thing)) {
   $tokenID = IPS_CreateVariable(3);
   IPS_SetName($tokenID, "token");
   IPS_SetParent($tokenID, $thing);
   $json=json_decode(file_get_contents("https://demo.stromdao.de/api/auth/".$pub."/".$priv));
	SetValue($tokenID,$json->token);
}
$token=GetValue(IPS_GetObjectIDByName("token",$thing));

if(!IPS_GetObjectIDByName("address",$thing)) {
   $addressID = IPS_CreateVariable(3);
   IPS_SetName($addressID, "address");
   IPS_SetParent($addressID, $thing);
   $json=file_get_contents("https://demo.stromdao.de/api/info/".$pub."?token=".$token);
	SetValue($addressID,json_decode($json));
}
$address=GetValue(IPS_GetObjectIDByName("address",$thing));

if($_IPS["SENDER"]=="Variable") {
	$tx_hash=file_get_contents("https://demo.stromdao.de/api/mpr/0x0/storeReading/".$value."/?token=".$token);
	if(!IPS_GetObjectIDByName("tx_hash",$thing)) {
			 $txID = IPS_CreateVariable(3);
			 IPS_SetName($txID, "tx_hash");
			 IPS_SetParent($txID, $thing);
	}
	SetValue(IPS_GetObjectIDByName("tx_hash",$thing),$tx_hash);
}
?>
