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

namespace Vokuro\Action\Users;

use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * The user list. The `?page=` query walks the pages; the profiles feed the
 * filter dropdown on the toolbar.
 */
final class GetUsers implements Action
{
    private const PER_PAGE = 10;

    public function __construct(
        private UserRepository $users,
        private ProfileRepository $profiles,
        private PrivateResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $page = $this->users->page(
            (int) $request->getQuery('page', null, 1),
            self::PER_PAGE
        );

        return ($this->responder->withTemplate('users/index'))(
            $request,
            new Response(),
            Payload::success([
                'page'     => $page,
                'profiles' => $this->profiles->listForSelect(),
            ])
        );
    }
}
