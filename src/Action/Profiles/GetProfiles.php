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

namespace Vokuro\Action\Profiles;

use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * The profile list.
 */
final class GetProfiles implements Action
{
    private const PER_PAGE = 10;

    public function __construct(
        private ProfileRepository $profiles,
        private PrivateResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $page = $this->profiles->page(
            (int) $request->getQuery('page', null, 1),
            self::PER_PAGE
        );

        return ($this->responder->withTemplate('profiles/index'))(
            $request,
            new Response(),
            Payload::success(['page' => $page])
        );
    }
}
