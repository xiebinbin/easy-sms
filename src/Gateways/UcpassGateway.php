<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms\Gateways;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * Class UcpassGateway.
 *
 * @see https://www.yunpian.com/doc/zh_CN/intl/single_send.html
 */
class UcpassGateway extends Gateway
{
    use HasHttpRequest;
    const ENDPOINT_URL = 'https://open.ucpaas.com/ol/sms/sendsms_batch';

    /**
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param \Overtrue\EasySms\Support\Config                 $config
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException ;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $result = $this->request('post', self::ENDPOINT_URL, [
            'json' => [
                'sid' => $config->get('account_sid', ''),
                'token' => $config->get('token', ''),
                'appid'=>$config->get('appid',''),
                'templateid'=>$message->getTemplate($this),
                'param'=>implode(',',$message->getData($this)),
                'mobile'=>$to->getNumber(),
                'uid'=>0
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'exceptions' => false,
        ]);
        /**
         * array:6 [
  "code" => "000000"
  "count_sum" => "1"
  "create_date" => "2019-03-03 17:36:14"
  "msg" => "OK"
  "report" => array:1 [
    0 => array:5 [
      "code" => "000000"
      "count" => "1"
      "mobile" => "17608161524"
      "msg" => "OK"
      "smsid" => "f35918f986690c2f3784a3694d5ce54e"
    ]
  ]
  "uid" => "0"
]
         */
        if ($result['code'] != '000000') {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }

        return $result;
    }
}
