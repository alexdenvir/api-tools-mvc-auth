<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-mvc-auth for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-mvc-auth/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-mvc-auth/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\ApiTools\MvcAuth\Factory;

use Laminas\Authentication\Adapter\Http\ApacheResolver;
use Laminas\Authentication\Adapter\Http as HttpAuth;
use Laminas\Authentication\Adapter\Http\FileResolver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * Create and return a Laminas\Authentication\Adapter\Http instance based on the
 * configuration provided.
 */
final class HttpAdapterFactory
{
    /**
     * Only defined in order to prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Create an HttpAuth instance based on the configuration passed.
     * 
     * @param array $config 
     * @return HttpAuth
     * @throws ServiceNotCreatedException if any required elements are missing
     */
    public static function factory(array $config)
    {
        if (! isset($config['accept_schemes']) || ! is_array($config['accept_schemes'])) {
            throw new ServiceNotCreatedException(
                '"accept_schemes" is required when configuring an HTTP authentication adapter'
            );
        }

        if (! isset($config['realm'])) {
            throw new ServiceNotCreatedException(
                '"realm" is required when configuring an HTTP authentication adapter'
            );
        }

        if (in_array('digest', $config['accept_schemes'])) {
            if (! isset($config['digest_domains'])
                || ! isset($config['nonce_timeout'])
            ) {
                throw new ServiceNotCreatedException(
                    'Both "digest_domains" and "nonce_timeout" are required '
                    . 'when configuring an HTTP digest authentication adapter'
                );
            }
        }

        $httpAdapter = new HttpAuth(array_merge(
            $config,
            array(
                'accept_schemes' => implode(' ', $config['accept_schemes'])
            )
        ));

        if (in_array('basic', $config['accept_schemes'])
            && isset($config['htpasswd'])
        ) {
            $httpAdapter->setBasicResolver(new ApacheResolver($config['htpasswd']));
        }

        if (in_array('digest', $config['accept_schemes'])
            && isset($config['htdigest'])
        ) {
            $httpAdapter->setDigestResolver(new FileResolver($config['htdigest']));
        }

        return $httpAdapter;
    }
}
