<?php

namespace App\Services\Cms;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Throwaway RS256 keypair used ONLY to simulate the BNU CMS's SSO handoff
 * during local development, since no real CMS exists to sign tokens yet
 * (see CLAUDE.md — "generate a local test keypair for now"). Never used
 * outside app()->environment('local'); production must set a real
 * CMS_SSO_PUBLIC_KEY. Keys live under storage/app/private, which is
 * git-ignored.
 */
class DevSsoKeyPair
{
    private static function directory(): string
    {
        return storage_path('app/private/cms-dev');
    }

    private static function privateKeyPath(): string
    {
        return self::directory().'/sso-private-key.pem';
    }

    private static function publicKeyPath(): string
    {
        return self::directory().'/sso-public-key.pem';
    }

    public static function publicKeyIfExists(): ?string
    {
        return File::exists(self::publicKeyPath())
            ? File::get(self::publicKeyPath())
            : null;
    }

    /**
     * Generates the keypair on first use and reuses it afterwards, so
     * tokens minted across requests stay verifiable against the same key.
     */
    public static function ensure(): void
    {
        if (File::exists(self::privateKeyPath()) && File::exists(self::publicKeyPath())) {
            return;
        }

        File::ensureDirectoryExists(self::directory());

        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($keyPair, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($keyPair)['key'];

        File::put(self::privateKeyPath(), $privateKeyPem);
        File::put(self::publicKeyPath(), $publicKeyPem);
    }

    /**
     * Mints a token shaped exactly like a real CMS handoff would, for the
     * given role and CMS identifier. Local-dev-only convenience.
     */
    public static function mintToken(string $role, string $cmsId): string
    {
        self::ensure();

        $payload = [
            'sub' => $cmsId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + 60,
            'aud' => config('services.cms.sso_audience'),
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, File::get(self::privateKeyPath()), 'RS256');
    }
}
