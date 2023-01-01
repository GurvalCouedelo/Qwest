<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\TypeNote;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

class CaptchaService extends Controller
{
    public function captchaverify($recaptcha){
        $clefSecrete = array("localhost" => "6LdaFxcUAAAAAPujfUcFJsddlcF5k7-svl8kzc2B", "titann" => "6Lexpy4UAAAAAKSElksStd8RidkW_P2pF_KOqXjd");
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>$clefSecrete["titann"], "response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response);
//        return true;
        return $data->success;
    }

    public function creerCaptcha(){
        $clefPublique = array("localhost" => "6LdaFxcUAAAAAHnLSGjQv-5iLxzD6HvgtHEQXMvy", "titann" => "6Lexpy4UAAAAAMzUGfwPkOKpgwwJSwxbuIuJ1LkT");
         return "<div class=\"g-recaptcha\" data-sitekey=\"" . $clefPublique["titann"] . "\" data-theme=\"dark\"></div>";
    }
}