<?php

namespace Revive\ReviveAuthenticationBundle\Repository\UserSession;

/**
 * The authentication result.
 *
 * @package Revive\ReviveAuthenticationBundle\Repository\UserSession
 */
class UserSessionCreationAuthenticationResult {

    const SUCCESS = 0;
    const FAILED_INVALID_CREDENTIALS = -1;

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
