<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Highlight\TempestTerminalTheme;
use Tempest\Console\Testing\TestConsoleOutput;
use Tempest\Highlight\Highlighter;

/**
 * @internal
 * @small
 */
final class CommandAliasesWork extends TestCase
{
    public function test_aliases_work()
    {
        $handler = new ReflectionMethod(new ListFrameworks(), 'handle');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        $output = new TestConsoleOutput(new Highlighter(new TempestTerminalTheme()));

        (new RenderConsoleCommand($output))($consoleCommand);

        $this->assertSame(
            'frameworks:list [--sortByBest=false] - List all available frameworks.',
            trim($output->getLinesWithoutFormatting()[0]),
        );
    }
}
