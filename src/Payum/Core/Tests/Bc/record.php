<?php

declare(strict_types=1);

/*
 * Records the golden transcripts the BC suite compares 2.x against. Run it against a 1.x install, never
 * against this repository:
 *
 *     composer require payum/payum:1.7.7 ... (see README.md)
 *     php src/Payum/Core/Tests/Bc/record.php /path/to/1.x/vendor/autoload.php [scenario-file ...]
 */
use Composer\InstalledVersions;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Tests\Bc\Recorder;
use Payum\Core\Tests\Bc\Scenario;

if (! isset($argv[1]) || ! is_file($argv[1])) {
    fwrite(STDERR, "Usage: php record.php /path/to/1.x/vendor/autoload.php [scenario-file ...]\n");
    exit(1);
}

require $argv[1];

if (interface_exists(CommandInterface::class)) {
    fwrite(STDERR, "That autoloader loads Payum 2.x. Golden transcripts must come from a 1.x run.\n");
    exit(1);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Payum\\Core\\Tests\\Bc\\';

    if (str_starts_with($class, $prefix) && is_file($file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php')) {
        require $file;
    }
});

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$version = InstalledVersions::getPrettyVersion('payum/payum') ?? InstalledVersions::getPrettyVersion('payum/core');
$files = array_slice($argv, 2) ?: glob(__DIR__ . '/Scenarios/*.php');

foreach ($files as $file) {
    $gateway = basename($file, '.php');
    $transcripts = [];

    /** @var Scenario $scenario */
    foreach (require $file as $scenario) {
        $transcripts[$scenario->name] = (new Recorder())->record($scenario);
    }

    $golden = [
        'recorded_with' => 'payum ' . $version . ', PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
        'scenarios' => $transcripts,
    ];

    file_put_contents(
        __DIR__ . '/golden/' . $gateway . '.json',
        json_encode($golden, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
    );

    echo $gateway, ': ', count($transcripts), " scenarios\n";
}
