<?php

namespace App\Utils;

use App\Entity\Location;

class APICallsService
{

    public function getCoordsFromPlace(Location $place): array
    {
        $key = $_ENV['OPENCAGE_KEY'];
        $street = $place->getStreet();
        $city = $place->getCity()->getName();
        //TODO - Remove the declaration. It is for test purpose only
        $city = "Rennes";

        $info_string = str_replace(' ', '+', $street . ', ' . $city);
        $response = file_get_contents("https://api.opencagedata.com/geocode/v1/json?q=$info_string&key=$key&language=en&pretty=1");
        $response = json_decode($response);
        return ['latitude'=>$response->results[0]->geometry->lat,'longitude'=> $response->results[0]->geometry->lng];
    }

}