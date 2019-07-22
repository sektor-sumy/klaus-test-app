<?php

namespace App\Service;

use GuzzleHttp\Client;

class Language
{
    const API_URL = 'https://restcountries.eu/rest/v2/';

    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => self::API_URL]);
    }

    /**
     * @param string $country
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getLangCodeByNameCountry(string $country)
    {
        $response = $this->client->request('GET', 'name/'.$country);
        if ($response->getStatusCode() === 200) {
            $arrContent = json_decode($response->getBody()->getContents(), true);
            $langCode = $arrContent[0]['languages'][0]['iso639_1'];

            return $langCode;
        }

        throw new \Exception('Country not found');
    }

    /**
     * @param string $langCode
     *
     * @return array
     */
    public function getCountriesByLang(string $langCode)
    {
        $countriesWithCurrentLang = [];
        $response = $this->client->request('GET', 'lang/'.$langCode);
        $arrayContent = json_decode($response->getBody()->getContents(), true);

        foreach ($arrayContent as $item) {
            $countriesWithCurrentLang[] = $item['name'];
        }

        return $countriesWithCurrentLang;
    }

    /**
     * @param string $country
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getLanguagesCodeByNameCountry(string $country)
    {
        $languageCodes = [];
        $response = $this->client->request('GET', 'name/' . $country.'?fullText=true');
        if ($response->getStatusCode() === 200) {
            $arrayContent = json_decode($response->getBody()->getContents(), true);
            foreach ($arrayContent[0]['languages'] as $lang) {
                $languageCodes[] = $lang['iso639_1'];
            }

            return $languageCodes;
        }

        throw new \Exception('Country not found');
    }
}
