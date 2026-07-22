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
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * The user search results. The toolbar on the list submits its filters here as
 * query values, and the same paged read narrows on them.
 */
final class GetUsersSearch implements Action
{
    private const PER_PAGE = 10;

    public function __construct(
        private UserRepository $users,
        private PrivateResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $query   = $request->getQuery();
        $filters = [
            'id'         => $query['id'] ?? '',
            'name'       => $query['name'] ?? '',
            'email'      => $query['email'] ?? '',
            'profilesId' => $query['profilesId'] ?? '',
        ];

        $page = $this->users->page(
            (int) ($query['page'] ?? 1),
            self::PER_PAGE,
            $filters
        );

        return ($this->responder->withTemplate('users/search'))(
            $request,
            new Response(),
            Payload::success([
                'page'  => $page,
                'query' => http_build_query(array_filter($filters)),
            ])
        );
    }
}
