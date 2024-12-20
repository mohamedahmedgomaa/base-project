<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit770867f8af9b0e26501e57b1463645e0
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Gomaa\\Test\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Gomaa\\Test\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit770867f8af9b0e26501e57b1463645e0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit770867f8af9b0e26501e57b1463645e0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit770867f8af9b0e26501e57b1463645e0::$classMap;

        }, null, ClassLoader::class);
    }
}
