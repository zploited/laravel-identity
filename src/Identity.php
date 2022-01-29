<?php

namespace Zploited\Laravel\Identity;

use Illuminate\Support\Facades\Http;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Strobotti\JWK\KeyConverter;
use Strobotti\JWK\KeyFactory;
use Zploited\Laravel\Identity\Exceptions\AuthenticationException;

class Identity
{
    static $protocol = 'http://';

    public static function validateAccessToken($token): ?Plain
    {
        try {
            //TODO: Check JWT is well formed
            //- Verify that the JWT contains three segments, separated by two period ('.') characters.
            if (count(explode('.', $token)) !== 3) {
                throw new AuthenticationException("Malformed access token");
            }

            //- Parse the JWT to extract its three components. The first segment is the Header, the second is the Payload, and the third is the Signature. Each segment is base64url encoded.
            try {
                $unsafeJwtConfig = Configuration::forUnsecuredSigner();
                $unsafeToken = $unsafeJwtConfig->parser()->parse($token);
            } catch (\Exception $ex) {
                throw new AuthenticationException('Unable to parse provided token.');
            }

            //TODO: Check signature
            //- Check the signing algorithm.
            //--    Retrieve the alg property from the decoded Header.
            $alg = $unsafeToken->headers()->get('alg');

            //--    Ensure that it is an allowed algorithm. Specifically, to avoid certain attacks, make sure you disallow none.
            $allowed = ['rs256', 'hs256'];
            if (!in_array(strtolower($alg), $allowed)) {
                throw new AuthenticationException("The algorithm used in token is not allowed.");
            }

            //--    Check that it matches the algorithm you selected when you registered your Application.
            if (config('identity.client.algorithm') !== strtolower($alg)) {
                throw new AuthenticationException("The algorithm used in token does not match the client.");
            }

            //- Confirm that the token is correctly signed using the proper key.
            $publicKey = self::publicKeyFromJwksEndpoint($unsafeToken->headers()->get('kid'));

            $secureConfig = Configuration::forAsymmetricSigner(new Sha256, InMemory::empty(), InMemory::plainText($publicKey));
            $secureToken = $secureConfig->parser()->parse($token);

            if (!$secureConfig->validator()->validate($secureToken, new SignedWith($secureConfig->signer(), $secureConfig->verificationKey()))) {
                throw new AuthenticationException('Unable to validate token signature!');
            }

            //TODO: Check standard claims
            //- Before using the token, you should retrieve the following standard claims from the decoded payload and perform the following checks:
            //--    Token expiration (exp, Unix timestamp): The expiration date/time must be after the current date/time.
            if (!$secureConfig->validator()->validate($secureToken, new LooseValidAt(SystemClock::fromUTC()))) {
                throw new AuthenticationException('Token is either expired or not valid yet.');
            }

            //--    Token issuer (iss, string): The issuing authority inside the token must match the issuing authority (issuer) identified in your Auth0 tenant's discovery document, which exists at https://YOUR_DOMAIN/.well-known/openid-configuration.
            //if (!$secureConfig->validator()->validate($secureToken, new IssuedBy(config('identity.tenant.identifier')))) {
            //    throw new AuthenticationException('Incorrect issuing service.');
            //}

            if (!$secureConfig->validator()->validate($secureToken, new PermittedFor(config('identity.client.id')))) {
                throw new AuthenticationException('Incorrect issuing service.');
            }
        } catch (AuthenticationException $authenticationException) {
            return null;
        }

        return $secureToken;
    }

    public static function publicKeyFromJwksEndpoint(string $kid)
    {
        $endpoint = self::$protocol . config('identity.tenant.identifier') . '/jwks.json';
        $keys = Http::get($endpoint)->json('keys');

        foreach ($keys as $key) {
            if($key['use'] === 'sig' && $key['kid'] === $kid) {
                $jwk = (new KeyFactory())->createFromJson(json_encode($key));
                return (new KeyConverter())->keyToPem($jwk);
            }
        }

        return null;
    }
}