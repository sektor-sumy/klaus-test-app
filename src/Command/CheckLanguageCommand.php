<?php

namespace App\Command;

use App\Service\Language;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLanguageCommand extends Command
{
    protected $language;

    public function __construct(Language $language)
    {
        parent::__construct();
        $this->language = $language;
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
                $this->showMessageForMultipleCountries($countries, $output);
            } else {
                $this->showMessageForOneCountry($countries[0], $output);
            }
        } catch (\Exception $e) {
            $output->writeln('Something went wrong! '.$e->getMessage());
        }
    }

    /**
     * @param array           $countries
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function showMessageForMultipleCountries(array $countries, OutputInterface $output)
    {
        $languages = [];
        foreach ($countries as $country) {
            $languages[] = $this->language->getLanguagesCodeByNameCountry($country);
        }

        $matches = call_user_func_array('array_intersect', $languages);

        if (!empty($matches)) {
            $output->writeln(implode(' and ', $countries).' speak the same language.');
        } else {
            $output->writeln(implode(' and ', $countries).' do not speak the same language.');
        }
    }

    /**
     * @param string          $country
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function showMessageForOneCountry(string $country, OutputInterface $output)
    {
        $lang = $this->language->getLangCodeByNameCountry($country);
        $output->writeln('Country language code: '.$lang);
        $countriesWithCurrentLang = $this->language->getCountriesByLang($lang);
        if (!empty($countriesWithCurrentLang)) {
            $output->writeln($country.' speaks same language with these countries: '.implode(', ', $countriesWithCurrentLang));
        }
    }
}
