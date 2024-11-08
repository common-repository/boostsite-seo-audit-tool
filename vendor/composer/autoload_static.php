<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit46fc9a135f6b478a86bf34e05161f5d3
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Boostsite\\AI_Plugin\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Boostsite\\AI_Plugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/plugin',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit46fc9a135f6b478a86bf34e05161f5d3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit46fc9a135f6b478a86bf34e05161f5d3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit46fc9a135f6b478a86bf34e05161f5d3::$classMap;

        }, null, ClassLoader::class);
    }
}
