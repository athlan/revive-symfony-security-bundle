# Revive Symfony Security integration bundle

This bundle allows you to enable Symfony Security authentication by providing Revive Aderver credentials.

It allow you to build custom tools around Revive in Symfony Framework.

# Installation

1. Add bundle to `AppKernel.php`

```
    public function registerBundles()
    {
        $bundles = [
        
            // ...
            
            new Revive\ReviveAuthenticationBundle\ReviveAuthenticationBundle(),
            
            // ...

```

2. Define several services in your container:

```
services:
    # create user session repository service
    revive_user_sessions:
        parent: revive_authentication.repository.user_session.xml_rpc
        arguments:
            # you can provide here custom configured xml_rpc client or get the existing pre-configured one
            - "@revive_authentication.xml_rpc.client"

            # address to your Revive xml-rpc
            # Example: http://domain.com/www/api/v2/xmlrpc/
            # you can use parameter mechanism here
            - "%revive_service_url%"

    # create form-autehnticacor that exchanges UsernamePasswordToken from user provided form
    # to ReviveAuthenticationToken
    revive_authenticator:
        parent: revive_authentication.authenticator.login_form_authenticator
        arguments: ["@revive_user_sessions"]

    # create logout listener to destroy remote sessions (optional, but recommended)
    revive_authenticator_logout_handler:
        parent: revive_authentication.authenticator.logout_handler
        arguments: ["@revive_user_sessions"]

```

3. Configure security.yml

```
security:
    # regiter Revive user provider
    providers:
        revive:
            id: revive_authentication.user_provider.revive_user_prototype

    firewalls:
        main:
            anonymous: ~
            simple_form:
                # regiter Revive form authenticator
                authenticator: revive_authenticator
                check_path: /login
                login_path: /login
                csrf_token_generator: security.csrf.token_manager
            logout:
                path: /logout
                target: /
                handlers: [ revive_authenticator_logout_handler ]

    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, roles: IS_AUTHENTICATED_FULLY }

```
