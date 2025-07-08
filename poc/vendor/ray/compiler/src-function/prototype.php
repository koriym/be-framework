<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Exception\ScriptFileNotFound;

use function file_exists;

use const DIRECTORY_SEPARATOR;

/**
 * Injection with prototype scope
 *
 * @param string     $scriptDir       The base directory of the script files.
 * @param string     $dependencyIndex The dependency identifier used in the script's context.
 * @param string     $filePath        The relative file path of the script to be included.
 * @param array|null $ip              An optional array for injection point to be accessible in the script.
 *
 * @return mixed The resolved dependency instance from the required script file.
 *
 * @throws ScriptFileNotFound Thrown if the specified script file could not be located.
 */
function prototype(string $scriptDir, array &$singletons, string $dependencyIndex, string $filePath, ?array $ip = null)
{
    $file = $scriptDir . DIRECTORY_SEPARATOR . $filePath;
    if (! file_exists($file)) {
        throw new ScriptFileNotFound($filePath);
    }

    // $scriptDir, $Singletons, $dependencyIndex and $ip can be used in the included file
    return require $file;
}
