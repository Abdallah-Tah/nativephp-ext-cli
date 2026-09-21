<?php

namespace Amohamed\NativePhpCustomPhp\Binary;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use Amohamed\NativePhpCustomPhp\Cache\BinaryCache;
use Amohamed\NativePhpCustomPhp\Registry\ManifestClient;

final class BinaryResolver
{
    public function __construct(
        private readonly BinaryCache $cache,
        private readonly ManifestClient $manifestClient,
        private readonly ?string $registryUrl = null,
    ) {
    }

    public function resolve(BuildSpec $spec): ResolutionResult
    {
        $this->cache->ensureStructure();

        if ($this->cache->has($spec)) {
            return new ResolutionResult('cache-hit', path: $this->cache->pathFor($spec));
        }

        if ($this->registryUrl === null || $this->registryUrl === '') {
            return new ResolutionResult('local-build-required');
        }

        $manifest = $this->manifestClient->fetch($this->registryUrl);

        if ($manifest === null) {
            return new ResolutionResult('local-build-required');
        }

        $targetPlatform = $spec->platform . '-' . $spec->architecture;
        $requestedExtensions = $spec->extensions;
        sort($requestedExtensions);

        foreach ($manifest->artifacts as $artifact) {
            if ($artifact->php !== $spec->phpVersion) {
                continue;
            }

            if ($artifact->platform !== $targetPlatform) {
                continue;
            }

            if ($artifact->profile !== $spec->profile && $spec->profile !== 'custom') {
                continue;
            }

            $artifactExtensions = $artifact->extensions;
            sort($artifactExtensions);

            if ($artifactExtensions !== $requestedExtensions) {
                continue;
            }

            return new ResolutionResult(
                'prebuilt',
                url: $artifact->url,
                sha256: $artifact->sha256,
                size: $artifact->size,
                meta: ['artifact' => $artifact->toArray()]
            );
        }

        return new ResolutionResult('local-build-required');
    }
}
