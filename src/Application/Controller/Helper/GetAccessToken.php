<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 8/5/18
 * Time: 7:33 AM
 */

namespace Application\Controller\Helper;


class GetAccessToken
{

    public function accessToken($request) {
        $accessToken = str_replace('Bearer','',$request->headers->get('Authorization'));
        return $accessToken;
    }
}