# Payloads

The payload is the contract between a domain and a responder. A domain returns one; a responder reads it. Nothing HTTP-shaped crosses the boundary.

A `Phalcon\ADR` payload carries four things:

* a **status** - a domain-level outcome (`SUCCESS`, `NOT_VALID`, `NOT_FOUND`, ...)
* a **result** - the data a successful outcome produced
* **messages** - errors or notices, keyed by field where they belong to one
* **extras** - view chrome the result should not carry (see below)

## Factories

Build a payload with a named factory rather than setting the status by hand. The factory names read as outcomes, and the responder maps each to an HTTP status code. The ones this application uses:

| Factory | Carries | Status | HTTP (via `StatusMapper`) |
| --- | --- | --- | --- |
| `Payload::success($result)` | result | `SUCCESS` | `200` |
| `Payload::created($result)` | result | `CREATED` | `201` |
| `Payload::updated($result)` | result | `UPDATED` | `200` |
| `Payload::found($result)` | result | `FOUND` | `302` (see redirects) |
| `Payload::authenticated($result)` | result | `AUTHENTICATED` | `200` |
| `Payload::invalid($messages)` | messages | `NOT_VALID` | `422` |
| `Payload::notFound($messages)` | messages | `NOT_FOUND` | `404` |
| `Payload::unauthenticated($messages)` | messages | `NOT_AUTHENTICATED` | `401` |
| `Payload::forbidden($messages)` | messages | `NOT_AUTHORIZED` | `403` |
| `Payload::error($messages)` | messages | `ERROR` | `500` |

More factories exist (`accepted`, `authorized`, `deleted`, `valid`, `processing`, `notCreated`, `notUpdated`, `notDeleted`, `notAccepted`). The full status-to-code map lives in `Phalcon\ADR\Responder\StatusMapper`; an unmapped status resolves to `500`, never a silent `200`.

The result-carrying factories take a result; the message-carrying factories take messages. `withMessages()` and `withResult()` add the other where a factory needs both - a `created` payload can still carry a notice:

```php
return Payload::created(['id' => $userId])
    ->withMessages(['A confirmation mail has been sent to ' . $email]);
```

## Result vs messages

* **result** is the data the template renders - a page of users, a profile, the select options. On a redirect it is a `Phalcon\ADR\Responder\Redirect`.
* **messages** are what the page shows the user - per-field validation errors, or a flash notice. Keying a message by field lets the form put it under the right input:

  ```php
  $messages['email'] = 'That e-mail is already registered';
  return Payload::invalid($messages);
  ```

## Extras and `Meta`

`extras` carries application state a template consumes but the result should not - whether anyone is signed in, and their name. It travels as a `Domain\Meta` value object:

```php
final class Meta
{
    public function __construct(
        public readonly bool $isLoggedIn = false,
        public readonly string $name = ''
    ) {
    }
}
```

An action that has its own `Meta` puts it on the payload's extras. When it does not, the `LayoutRenderer` derives a default from the session, so every page knows whether a visitor is signed in without each action remembering to set it.

## How a responder reads it

`ViewResponder` flattens the payload into the variables the template receives - `$result`, `$messages`, `$extras`, `$status` - renders the template inside the responder's layout, and sets the response status from the payload:

```php
$response
    ->setStatusCode($this->statusMapper->toHttpCode((string) $payload->getStatus()))
    ->setContentType('text/html')
    ->setContent($html);
```

## Redirects

A redirect is a payload too. The action returns `Payload::found()` with a `Redirect` result and asks the `RedirectResponder`, which emits the `302`:

```php
return ($this->redirect)(
    $request,
    new Response(),
    Payload::found(new Redirect('/users'))
);
```
