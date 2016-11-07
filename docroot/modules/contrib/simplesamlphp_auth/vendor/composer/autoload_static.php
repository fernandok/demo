<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit611b7db47af3289bd8284402513a44be
{
    public static $files = array (
        '5abda994d126976858eb25d2546ee3c9' => __DIR__ . '/..' . '/simplesamlphp/simplesamlphp/lib/_autoload_modules.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WhiteHat101\\Crypt\\' => 18,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WhiteHat101\\Crypt\\' => 
        array (
            0 => __DIR__ . '/..' . '/whitehat101/apr1-md5/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'SimpleSAML' => 
            array (
                0 => __DIR__ . '/..' . '/simplesamlphp/simplesamlphp/lib',
            ),
            'SAML2_' => 
            array (
                0 => __DIR__ . '/..' . '/simplesamlphp/saml2/src',
            ),
        ),
    );

    public static $classMap = array (
        'XMLSecEnc' => __DIR__ . '/..' . '/robrichards/xmlseclibs/src/XMLSecEnc.php',
        'XMLSecurityDSig' => __DIR__ . '/..' . '/robrichards/xmlseclibs/src/XMLSecurityDSig.php',
        'XMLSecurityKey' => __DIR__ . '/..' . '/robrichards/xmlseclibs/src/XMLSecurityKey.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit611b7db47af3289bd8284402513a44be::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit611b7db47af3289bd8284402513a44be::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit611b7db47af3289bd8284402513a44be::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit611b7db47af3289bd8284402513a44be::$classMap;

        }, null, ClassLoader::class);
    }
}