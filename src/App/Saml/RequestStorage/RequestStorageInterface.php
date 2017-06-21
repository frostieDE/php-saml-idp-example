<?php

namespace App\Saml\RequestStorage;

/**
 * The request storage stores an incoming SAML request in order to process it somewhen afterwards. This is useful
 * in case the user is not authenticated and must be sign in first. Symfony redirects the user but after the login
 * the SAML request is gone and cannot be processed anymore. Thus, the request must be stored in the meantime.
 */
interface RequestStorageInterface {
    public function save();

    public function load();

    public function clear();
}