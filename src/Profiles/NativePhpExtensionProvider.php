<?php

namespace Amohamed\NativePhpCustomPhp\Profiles;

final class NativePhpExtensionProvider
{
    /**
     * @return array<int, string>
     */
    public function baseExtensions(): array
    {
        return [
            'bcmath',
            'bz2',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'filter',
            'gd',
            'iconv',
            'intl',
            'json',
            'libxml',
            'mbregex',
            'mbstring',
            'opcache',
            'openssl',
            'pdo',
            'pdo_sqlite',
            'phar',
            'session',
            'simplexml',
            'sockets',
            'sodium',
            'sqlite3',
            'tokenizer',
            'xml',
            'zip',
            'zlib',
        ];
    }
}
