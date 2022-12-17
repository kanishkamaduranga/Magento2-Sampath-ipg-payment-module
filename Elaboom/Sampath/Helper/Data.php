<?php


namespace Elaboom\Sampath\Helper;


use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public $auth_Token = '38d928f3-f2c2-4bb9-a1b3-8efeede856ed';
    public $hmac = 'P2L1T2Q2RaeQD1QC';

    public $client_id = [
        0 => 14000237,

        6 => 14004580,
        12 => 14004581,
        24 => 14004582,
        40 => 14005049,
    ];


    function generateHashReference($order_id, $length = 75) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString.$order_id;
    }
}
