<?php

declare(strict_types=1);

/**
 * Loads every class under one directory and fails if any of them needs a
 * package that is not installed. Reflection resolves parents, interfaces and
 * traits, which is what catches a branch reaching into the other Doctrine
 * stack through inheritance.
 *
 * It does NOT resolve types in method signatures or bodies, so it is not the
 * proof that Orm and Odm stay apart -- deptrac is. This proves loadability.
 *
 * Usage: php tools/check-autoload-isolation.php <src-dir> <namespace-prefix>
 */
require __DIR__ . '/../vendor/autoload.php';

$dir = (string) ($argv[1] ?? '');
$prefix = (string) ($argv[2] ?? '');

if ($dir === '' || $prefix === '') {
    \fwrite(\STDERR, "Usage: php tools/check-autoload-isolation.php <src-dir> <namespace-prefix>\n");

    exit(2);
}

$failures = [];
$loaded = 0;

/** @var SplFileInfo $file */
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $relative = \substr($file->getPathname(), \strlen($dir) + 1, -4);
    $fqcn = $prefix . \str_replace('/', '\\', $relative);
    $loaded++;

    try {
        new ReflectionClass($fqcn);
    } catch (Throwable $e) {
        $failures[] = $fqcn . ': ' . $e->getMessage();
    }
}

\printf("%s: loaded %d classes, %d failures\n", $dir, $loaded, \count($failures));

foreach ($failures as $failure) {
    \printf("  FAIL %s\n", $failure);
}

exit($failures === [] ? 0 : 1);
