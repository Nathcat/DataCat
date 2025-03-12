# AuthCat

AuthCat provides credential authentication and basic user data for the [nathcat.net](https://nathcat.net) network.

## Implementation

For JS applications, use the following JS file which contains functions which will access the API for you and return the JSON result.
```html
<script src="https://data.nathcat.net/sso/js/sso.js"></script>
```
Otherwise, you can make a `POST` request to `https://data.nathcat.net/sso/try-login.php` to attempt to login or `https://data.nathcat.net/sso/create-user.php` to create a new user.

### Login Request

To create a login request, send a POST request to `https://data.nathcat.net/sso/try-login.php` with a JSON body containing the following information:
```json
{
    "username": <username>,
    "password": <password>
    // Following is optional, use with Quick Auth
    "quick-auth-token": <quick-auth-token>
}
```
The API will then return either a failure message in the format:
```json
{
    "status": "fail",
    "message": <error-message>
}
```
Or a success message in the format:
```json
{
    "status": "success",
    "user": {
        "id": <id>,
        "username": <username>,
        "email": <email>,
        "password": <hashed-password>,
        "fullName": <fullName>
    }
}
```

### Create New User Request

To create a new user request, send a POST request to `https://data.nathcat.net/sso/create-user.php` with a JSON body containing the following information:
```json
{
    "username": <username>,
    "email": <email>,
    "password": <password>,
    "password2": <password2>,
    "fullName": <fullName>
}
```
The API will then return either a failure message in the format:
```json
{
    "status": "fail",
    "message": <error-message>
}
```
Or a success message in the format:
```json
{
    "status": "success"
}
```

### User search request

AuthCat also provides a way to search for users. To use this feature, send a POST request to `https://data.nathcat.net/sso/user-search.php`,
providing the following data in a JSON body:

#### Search by ID
```json
{
    "id": Integer
}
```
Note that if `id` is specified in the request body, then _all other provided data in the body will be ignored_, and AuthCat will only search
for a user by the provided `id`.

#### Search by username
```json
{
    "username": String
}
```
AuthCat appends a wildcard character to the end of the provided username, so will return any user which has a username starting with the provided
username.

#### Search by full name
```json
{
    "fullName": String
}
```
AuthCat appends a wildcard character to the end of the provided name, so will return any user which has a name starting with the provided
name.

#### Combining search fields
If the `id` field is present in the body, then AuthCat will ignore any other provided data and only search for a user with the provided `id`.

You may however, specify _both_ the `username` and `fullName` fields, and AuthCat will perform both searches separately and return the results
for both.

### User search response
If successful, AuthCat will respond with JSON in the following format:
```json
{
    "status": "success",
    "results": {
        // "results" contains all returned users, with their ID as the key to their data, like the following:
        <ID>: {
            "id": Integer,
            "username": String,
            "fullName": String,
            "pfpPath": String
        }
    }
}
```

If searching by `id`, and AuthCat cannot find any user with the given `id`, it will respond with:
```json
{
    "status": "fail", 
    "message": "User not found"
}
```

In other search methods, if no user is found, the `results` field is simply empty.

Other errors will be reported under the response format:
```json
{
    "status": "fail",
    "message": String
}
```

### Quick Auth

The Quick Auth system makes use of a token system not based on cookies to make logins last longer.

On the AuthCat page there are buttons which allow users to manage the state of their authentication tokens.

There are two php endpoints which applications can use to manage authentication tokens.

1. `/sso/create-quick-auth.php`
2. `/sso/revoke-quick-auth.php`

#### Creating a token

Use endpoint `1.` for this task. You should supply the following information as _either_ a form data (`Content-Type: multipart/form-data`), or a JSON body (`Content-Type: application/json`).

```json
{
    "username": String,
    "password": String
}
```

AuthCat will reply with either a failiure packet with the same format as above, or a success packet:

```json
{
    "status": "success",
    "token": String
}
```

The `token` field containing the your quick auth token.

Or alternatively, you may add a `GET` parameter `?by-session` to specify that rather than passing user data through the body, you wish to pass the user data through the session cookie.

#### Revoking a token

In order to revoke a login token you should direct your request to endpoint `2.`, with a JSON body in either of the following formats:

```json
{
    "id": Integer,
    "token": String
}
```

```json
{
    "id": Integer
}
```

The first format will delete all tokens for user id `id` where the token matches `token`, which allows you to revoke a single session for a user.

The second format will delete all tokens for a single user with id `id`, which allows you to potentially logout all sessions for a single user.