<?php

/**
 * This file is part of the Vökuró.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vokuro\Contracts;

/**
 * Sends a templated e-mail. The domains depend on this port, never on the
 * transport behind it.
 */
interface Mailer
{
    /**
     * @param array<string, string> $to     address => name
     * @param array<string, mixed>  $params
     *
     * @return int the number of addressees
     */
    public function send(array $to, string $subject, string $template, array $params = []): int;
}
