class Aes {

    private $init_key;

    private $aes_bytes;

    private $ivlen;

    private $str;

    public function __construct($str,$key='',$aes_bytes='')
    {
        $this->str = $str;
        $this->init_key = $key?:"medo";
        $this->aes_bytes = $aes_bytes?:'AES-256-CBC';
        $this->ivlen = openssl_cipher_iv_length($this->aes_bytes);
    }

    public function encrypt()
    {
        $iv = openssl_random_pseudo_bytes($this->ivlen);
        $ciphertext_raw = openssl_encrypt($this->str, $this->aes_bytes, $this->init_key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->init_key, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
        return urlencode($ciphertext);
    }

    public function decrypt()
    {
        $result = '';
        $ciphertext = urldecode($this->str);
        $c = base64_decode($ciphertext);
        $iv = substr($c, 0, $this->ivlen);
        $hmac = substr($c, $this->ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $this->ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $this->aes_bytes, $this->init_key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->init_key, $as_binary=true);
        if (hash_equals($hmac, $calcmac)) {
            $result = $original_plaintext;
        }
        return $result;
    }
}

$str = 'I am Kung';
$aes = new Aes($str);
$en_str = $aes->encrypt();
echo $en_str.PHP_EOL;
$aes = new Aes($en_str);
$de_str = $aes->decrypt();
echo $de_str.PHP_EOL;
