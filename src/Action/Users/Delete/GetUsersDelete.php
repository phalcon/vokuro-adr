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

namespace Vokuro\Action\Users\Delete;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Repository\UserRepository;

/**
 * Removes a user and returns to the list. The id is the only path attribute.
 */
final class GetUsersDelete implements Action
{
    public function __construct(
        private UserRepository $users,
        private RedirectResponder $redirect
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $id = $request->getAttributes()->get('id', 0);

        if (null !== $this->users->findById($id)) {
            $this->users->delete($id);
        }

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect('/users'))
        );
    }

    /**
     * The trailing segment of `/users/delete/3`. The router matches it, casts
     * it and names it, so a non-numeric id is a 404 and never a lookup.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function params(): array
    {
        return [
            'id' => ['type' => 'int', 'match' => '\d+'],
        ];
    }
}
