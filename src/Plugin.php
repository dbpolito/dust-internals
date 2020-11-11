<?php

declare(strict_types=1);

namespace Dust;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Pest\Contracts\Plugins\HandlesArguments;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class Plugin implements HandlesArguments
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Creates a new Plugin instance.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output    = $output;
    }

    public function handleArguments(array $arguments): array
    {
        if (!array_key_exists(1, $arguments) || $arguments[1] !== 'dust-update') {
            return $arguments;
        }

        $this->init($arguments);

        exit(0);
    }

    /**
     * @param array<int, string> $arguments
     */
    private function init(array $arguments): void
    {
        $app = (new TestCase())->createApplication();

        $app->register(FilesystemServiceProvider::class);
        $app->register(DuskServiceProvider::class);

        $kernel = $app->make(Kernel::class);

        // @todo To be improved..
        unset($arguments[0]);
        unset($arguments[1]);
        $arguments = array_values($arguments);
        if (array_key_exists(0, $arguments)) {
            $version = $arguments[0];
            unset($arguments[0]);
            $arguments['version'] = $version;
        }

        $status = $kernel->call('dusk:chrome-driver', $arguments, $this->output);

        $kernel->terminate(new ArrayInput([]), $status);

        exit($status);
    }
}
