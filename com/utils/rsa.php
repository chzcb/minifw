<?php 

class RsaUtils
{
	public static $private_key = '-----BEGIN PRIVATE KEY-----
MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAKGRSmQrwYYwSIG+
QOxjCRNMBgwYbcGZRUp6wP5d/K3iOWZbuCUDIuIiZHt+Z02iP00cgG5B9qe6xdfB
T8W+uh+d5+gtrbeV2Meit4IGlAZ5FByH1+9t8USl+1Wqja/RxkJcz1FgO2YKAF/U
9Ajqb4j2Fs18ImgkfqLjo3Xgbnt3AgMBAAECgYByKFhpwsjwaXkxwx3YaHlnGxZC
wZf7bLKa13U5xWrd6bQo8LGB5V9mbGqXH08aeYNEltjgDtea18smkT4uOH8E7G31
nUi8N0ONLuyiAGSQXi+Is+U69AIaVEN9V4kvu/gln9hXhlBrMeOpc6gPhoLersrG
yWLFDxxN1QMWnvrgwQJBANCW85dP3OMooBev+NdmmhDoKI+gcqHCV7ODn0w1gq+p
wmRNjwJIyjd2DkNi62+a6n4CasSh64Q0idL4iT+YMRECQQDGSkyG5WdDn31A3zel
KYyiMuF5foMgV6sXH0gXsvfo+yeSLPTZXWu+0jbQkljUekFtYSq8Ix67U7kmgs/e
cuQHAkBd9+L9A4lq/F5CzY+42gwGGdBMA+ggX0DLKjyVRHX/VOax8Q6/5LLUkWaT
jPiraorBAa2/r4I+KLz+QeDyuUlBAkAm4dzdutAj93tFJEAyF9Km35lNDJzD080N
zKmDVCm+urkItd4RXXtKQMhU382hZJO90gbiO3TEQOeWgKIoOZkzAkBXiUbM/keF
KQnv0js6f/UJGldJ2gfjGXfNDOrTcst9icrWNAPhOkn+TJZYpMIeNR7Z2vb51XUU
BHN52gdqBGj2
-----END PRIVATE KEY-----';
	public static $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQChkUpkK8GGMEiBvkDsYwkTTAYM
GG3BmUVKesD+Xfyt4jlmW7glAyLiImR7fmdNoj9NHIBuQfanusXXwU/Fvrofnefo
La23ldjHoreCBpQGeRQch9fvbfFEpftVqo2v0cZCXM9RYDtmCgBf1PQI6m+I9hbN
fCJoJH6i46N14G57dwIDAQAB
-----END PUBLIC KEY-----';

	public static function encode($data){
		$encrypted = "";
		openssl_private_encrypt($data,$encrypted,self::$private_key);
		return urlsafe_b64encode($encrypted);
	}
	public static function decode($encrypted){
		$decrypted = "";
		openssl_public_decrypt(urlsafe_b64decode($encrypted),$decrypted,self::$public_key);
		return $decrypted;
	}

}

function urlsafe_b64encode($str){
	$ret = base64_encode($str);
	return str_replace(array('+','/'),array('-','_',''),$ret);
}

function urlsafe_b64decode($str){
	$res = str_replace(array('-','_'),array('+','/'),$str);
	$mod4 = strlen($res)%4;
	if($mod4){
		$res.=substr('===',$mod4);
	}
	return base64_decode($res);
}
