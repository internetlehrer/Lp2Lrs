<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbf94da7b3f80f0c1ee21e8aaf468fe07
{
    public static $classMap = array (
        'ilLp2LrsChangesQueue' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsChangesQueue.php',
        'ilLp2LrsChangesQueueEntry' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsChangesQueueEntry.php',
        'ilLp2LrsConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsConfigGUI.php',
        'ilLp2LrsCron' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsCron.php',
        'ilLp2LrsPlugin' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsPlugin.php',
        'ilLp2LrsXapiRequest' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsXapiRequest.php',
        'ilLp2LrsXapiStatement' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsXapiStatement.php',
        'ilLp2LrsXapiStatementList' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsXapiStatementList.php',
        'ilLp2LrsXapiStatementListBuilder' => __DIR__ . '/../..' . '/classes/class.ilLp2LrsXapiStatementListBuilder.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitbf94da7b3f80f0c1ee21e8aaf468fe07::$classMap;

        }, null, ClassLoader::class);
    }
}
