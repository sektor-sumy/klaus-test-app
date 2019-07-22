<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLanguageCommand extends Command
{
    private $client;

    const API_URL = 'https://restcountries.eu/rest/v2/';

    public function __construct()
    {
        $this->client = new Client(['base_uri' => self::API_URL]);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:check-language')
            ->setDescription('Check language command')
            ->addArgument('countries', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Countries.')

        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $countries = $input->getArgument('countries');
        try {
            if (count($countries) > 1) {
                $result = $this->showMessageForMultipleCountries($countries);
                if (!empty($result)) {
                    $output->writeln(implode(' and ', $countries).' speak the same language.');
                } else {
                    $output->writeln(implode(' and ', $countries).' do not speak the same language.');
                }
            } else {
                $lang = $this->getLangByNameCountry($countries[0]);
                $output->writeln('Country language code: '.$lang);
                $countriesWithCurrentLang = $this->getCountriesByLang($lang);
                if (!empty($countriesWithCurrentLang)) {
                    $output->writeln($countries[0].' speaks same language with these countries: '.implode(', ', $countriesWithCurrentLang));
                }
            }
        } catch (\Exception $e) {
            $output->writeln('Something went wrong! '.$e->getMessage());
        }
    }

    /**
     * @param array $countries
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function showMessageForMultipleCountries(array $countries)
    {
        $languages = [];
        foreach ($countries as $country) {
            $languages[] = $this->getLanguagesByNameCountry($country);
        }

        return call_user_func_array('array_intersect', $languages);
    }

    /**
     * @param string $country
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function getLangByNameCountry(string $country)
    {
        $res = $this->client->request('GET', 'name/'.$country);
        if ($res->getStatusCode() === 200) {
            $arrayResult = json_decode($res->getBody()->getContents(), true);
            $lang = $arrayResult[0]['languages'][0]['iso639_1'];

            return $lang;
        }

        throw new \Exception('Country not found');
    }

    /**
     * @param string $lang
     *
     * @return array
     */
    private function getCountriesByLang(string $lang)
    {
        $countriesWithCurrentLang = [];
        $res2 = $this->client->request('GET', 'lang/'.$lang);
        $arrayResult2 = json_decode($res2->getBody()->getContents(), true);

        foreach ($arrayResult2 as $res2) {
            $countriesWithCurrentLang[] = $res2['name'];
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
    private function getLanguagesByNameCountry(string $country)
    {
        $langs = [];
        $res = $this->client->request('GET', 'name/' . $country.'?fullText=true');
        if ($res->getStatusCode() === 200) {
            $arrayResult = json_decode($res->getBody()->getContents(), true);
            foreach ($arrayResult[0]['languages'] as $lang) {
                $langs[] = $lang['iso639_1'];
            }

            return $langs;
        }

        throw new \Exception('Country not found');
    }
}
