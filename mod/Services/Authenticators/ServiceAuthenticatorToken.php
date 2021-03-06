<?php
namespace Module\OAuth2Client\Services\Authenticators;

use Module\Authorization\Services\ContainerAuthenticatorsCapped;
use Module\OAuth2Client\Authenticate\IdentifierHttpToken;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap;
use Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy;
use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Ioc\Container\Service\aServiceContainer;


/*
 * Usage:
 *
 * $auth = \Module\Authorization\Actions::Authenticator( Module\OAuth2Client\Module::AUTHENTICATOR );
 * if ( $auth->hasAuthenticated() ) {
      $identity = $auth->identifier()->withIdentity();
      $user = $identity->user;
      echo 'Hello '.(isset($user['full_name']) ? $user['full_name'] : '@'.$user['username']);
   }
 *
 */

class ServiceAuthenticatorToken
    extends aServiceContainer
{
    /** @var iProviderIdentityData */
    protected $identityProvider;


    /**
     * Create Service
     *
     * @return Authenticator
     */
    function newService()
    {
        ### Attain Login Continue If Has
        /** @var iHttpRequest $request */
        $request  = \IOC::GetIoC()->get('/HttpRequest');
        $tokenAuthIdentifier = new IdentifierHttpToken;
        $tokenAuthIdentifier
            ->setRequest($request)
            ->setTokenAssertion( \Module\OAuth2Client\Actions::AssertToken()->assertion() )
        ;

        // In Cases That we request and token has no owner_identifier
        // client_credentials token in example
        //
        /*
        $authenticator = new Authenticator(
            $tokenAuthIdentifier
        );
        */


        // So I Disable This Here; this cause problem
        $authenticator = new Authenticator(
            new IdentifierWrapIdentityMap(
                $tokenAuthIdentifier
                , new IdentityFulfillmentLazy( $this->getIdentityProvider() , 'owner_identifier' )
            )
        );

        return $authenticator;
    }


    // Options:

    /**
     * Set Identity Provider That Fetch Identity Profile
     *
     * @param iProviderIdentityData $provider
     *
     * @return $this
     */
    function setIdentityProvider(iProviderIdentityData $provider)
    {
        $this->identityProvider = $provider;
        return $this;
    }

    /**
     * Get Identity Provider
     *
     * @return iProviderIdentityData
     */
    function getIdentityProvider()
    {
        return $this->identityProvider;
    }


    // ..

    /**
     * @override
     * !! Access Only In Capped Collection; No Nested Containers Here
     *
     * Get Service Container
     *
     * @return ContainerAuthenticatorsCapped
     */
    function services()
    {
        return parent::services();
    }
}
