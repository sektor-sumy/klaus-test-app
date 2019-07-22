<?php
namespace App\Tests\Util;

use App\Command\CheckLanguageCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CheckLanguageCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    protected function setUp()
    {
        $application = new Application();
        $application->add(new CheckLanguageCommand());
        $command = $application->find('app:check-language');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown()
    {
        $this->commandTester = null;
    }

    public function testExecute()
    {
        $this->commandTester->execute([
            'countries' => ['Spain'],
        ]);

        $this->assertEquals('Country language code: es
Spain speaks same language with these countries: Argentina, Belize, Bolivia (Plurinational State of), Chile, Colombia, Costa Rica, Cuba, Dominican Republic, Ecuador, El Salvador, Equatorial Guinea, Guam, Guatemala, Honduras, Mexico, Nicaragua, Panama, Paraguay, Peru, Puerto Rico, Spain, Uruguay, Venezuela (Bolivarian Republic of), Western Sahara', trim($this->commandTester->getDisplay()));


        $this->commandTester->execute([
            'countries' => ['Spain', 'Argentina', 'Belize'],
        ]);

        $this->assertEquals('Spain and Argentina and Belize speak the same language.', trim($this->commandTester->getDisplay()));

        $this->commandTester->execute([
            'countries' => ['Spain', 'Argentina', 'Belize', 'Belarus'],
        ]);

        $this->assertEquals('Spain and Argentina and Belize and Belarus do not speak the same language.', trim($this->commandTester->getDisplay()));
    }
}
