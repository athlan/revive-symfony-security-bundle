<?php

namespace Revive\ReviveAuthenticationBundle\Repository\UserSession;

/**
 * Result of session creation. This is an authorized operation with result.
 *
 * @package Revive\ReviveAuthenticationBundle\Repository\UserSession
 */
class UserSessionCreationAuthorizationSessionCreationResult {

    const SUCCESS = 0;
    const FAILED_NOT_ADMIN = -10;

    /**
     * Indicates if result is success.
     *
     * @param $val
     * @return bool
     */
    public static function isSuccess($val) {
        return $val === self::SUCCESS;
    }
}
