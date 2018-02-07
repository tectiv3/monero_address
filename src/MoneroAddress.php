<?php

namespace tectiv3;

use StephenHill\Base58;
/**
* Monero address encoder/decoder
*/
class MoneroAddress {

    private $coins = ["12" => "Standart", "11" => "Trimmed", "13" => "Integrated", "35" => "Testnet"];

    public $addr58;
    public $addrHex;
    public $pub_spend;
    public $pub_view;
    public $net_byte;
    public $pid;

    function __construct($addr58) {
        $this->addr58 = $addr58;
        $this->parse();
    }

    private function encode($input) {
        if (strlen($input) === 0) {
            return "";
        }
        $data = hex2bin($input);
        $base58 = new Base58();
        $full_block_size = 8;
        $full_encoded_block_size = 11;
        $encoded_block_sizes = [0, 2, 3, 5, 6, 7, 9, 10, 11];

        $full_block_count = floor( strlen($data) / $full_block_size );
        $last_block_size = strlen($data) % $full_block_size;
        $res_size = $full_block_count * $full_encoded_block_size + $encoded_block_sizes[$last_block_size];

        $result = "";
        for ($i=0; $i < $full_block_count; $i++) { 
            $block = $base58->encode(substr($data, $i * $full_block_size, $full_block_size));
            $block = strlen($block) < 11 ? str_repeat("1", 11-strlen($block)) . $block : $block;
            $result .= $block;
        }
        if ($last_block_size > 0) {
            $block = $base58->encode(substr($data, $full_block_count * $full_block_size, $last_block_size));
            $block = strlen($block) < 7 ? str_repeat("1", 7-strlen($block)) . $block : $block;
            $result .= $block;
        }
        return $result;
    }

    private function decode($input) {
        $base58 = new Base58();
        $full_block_size = 8;
        $full_encoded_block_size = 11;
        $encoded_block_sizes = [0, 2, 3, 5, 6, 7, 9, 10, 11];

        $full_block_count = floor( strlen($input) / $full_encoded_block_size );
        $last_block_size = strlen($input) % $full_encoded_block_size;
        $last_block_decoded_size = array_flip($encoded_block_sizes)[$last_block_size];

        if ($last_block_decoded_size < 0) {
            throw new Exception("Invalid encoded length");
        }

        $data_size = $full_block_count * $full_block_size + $last_block_decoded_size;
        $data = "";
        for ($i = 0; $i < $full_block_count; $i++) {
            $buf = substr($input, $i * $full_encoded_block_size, $full_encoded_block_size);
            if ($buf[0] == '1') $buf = substr($buf, 1);
            $data .= $base58->decode($buf);
        }
        if ($last_block_size > 0) {
            $buf = substr($input, $full_block_count * $full_encoded_block_size, $last_block_size);
            if ($buf[0] == '1') $buf = substr($buf, 1);
            $data .= $base58->decode($buf);
        }
        return bin2hex($data);
    }

    public function makeIntegrated($pid) {
        if ($pid == "") throw new Exception("empty payment id");
        $preAddr = "13" . $this->pub_spend . $this->pub_view . $pid;
        $hash = Sha3::hash(hex2bin($preAddr), 256);
        $addrHex = $preAddr . substr($hash, 0, 8);
        return $this->encode($addrHex);
    }
    
    private function parse() {
        if (strlen($this->addr58) !== 95 && strlen($this->addr58) !== 97 && strlen($this->addr58) !== 106) {
            throw new Exception("Invalid Address Length!");
        }
        $addrHex = $this->decode($this->addr58);
        $this->addrHex = $addrHex;
        if (strlen($addrHex) === 140) {
            $netbyte = substr($addrHex, 0, 4);
        } else {
            $netbyte = substr($addrHex, 0, 2);
        }
        $this->netbyte = $netbyte;
        if ($netbyte == "13") {
            if (strlen($addrHex) !== 154){
                throw new Exception("Invalid Address Length: " . strlen($addrHex) . " for " . $coins[$netbyte]);
            }
            $this->pid = substr($addrHex, 132, -8);
        } 
        $this->pub_spend = substr($addrHex, 2, 64);
        $this->pub_view = substr($addrHex, 66, 64);
    }
    
    public function print() {
        echo $this->addr58 . " is " . $this->coins[$this->netbyte] . " address.\n";
        echo "Netbyte: ". $this->netbyte . PHP_EOL;
        echo "Hex: ".$this->addrHex. " len: ". strlen($this->addrHex) . PHP_EOL;
        if ($this->pid) {
            echo "Payment ID: " . $this->pid . PHP_EOL;
        }
        echo "Public spend: " . $this->pub_spend;
        echo " Public view: " . $this->pub_view . PHP_EOL;
        $addrHash = Sha3::hash(hex2bin(substr($this->addrHex, 0, -8)), 256);
        $chk1 = substr($this->addrHex, -8);
        $chk2 = substr($addrHash, 0, 8);
        if ($chk1 == $chk2) {
            echo "Yes! This is a valid " . $this->coins[$this->netbyte] . " address.\n";
        } else {
            echo "No! This is not a valid " . $this->coins[$this->netbyte] . " address\n";
        }
    }

}
?>