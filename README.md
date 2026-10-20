# DataCat

Welcome to the new DataCat back-end server!

This is an API server exposing DataCat API functionality to client applications.

## API

### Errors

The API will attempt to return descriptions of errors which occur during execution of an endpoint.
Such errors will follow standard HTTP error codes. Usually, if the server replies with an error code,
the body will contain a textual description of the error.

### Apps

Apps are the key object of DataCat, services are linked to apps. The endpoints in this section
provide options for managing your apps.

#### `/api/apps`

Requires user authentication via a `Bearer` token.

##### `PUT`

Create a new app

###### Body

```json
{
  "name": ...  // The name of the app to create
}
```

###### Response

```
200 OK
```

##### GET

Get the apps owned by the authenticated user.

###### Response

```json
[
  {
    "id": ...,  // App ID
    "owner": ...,   // Owner ID
    "name": ..., // App name
    "apiKey": ...  // App API key
  }, ...
]
```

##### DELETE

Delete an app. Note that the authenticated user must own the app in order to delete it.

###### Body

```json
{
  "id": ... // App ID
}
```

###### Response

```
200 OK
```

### Groups

Most of these endpoints require user authentication, unless otherwise specified.

#### `/api/groups`

##### `GET`

Get the groups the authenticated users owns / is a member of.

###### Response

```json
{
  "owned": [
    {
      "id": ...,
      "name": ...,
      "owner": ...,
    }, ...
  ],
  "member": [
    {
      "id": ...,
      "name": ...,
      "owner": ...,
    }, ...
  ]
}
```

##### `PUT`

Create a new group.

###### Body

```json
{
  "name": ...
}
```

#### `/api/groups/invite`

##### `POST`

###### Query

- `token`: The invite token

###### Body

```json
{
  "action": ..., // MUST be either "accept" or "decline"
}
```

#### `/api/groups/{id}`

##### `DELETE`

Delete the group, note that you must be authenticated as the group owner to succeed.

#### `/api/groups/{id}/members`

##### `GET`

Get a list of the members of the group. You do _NOT_ need to be authenticated for this request.

##### `DELETE`

Leave the group.

#### `/api/groups/{id}/invite`

##### `PUT`

Invite a user to the group. You must be authenticated as the group owner.

###### Body

```json
{
  "username": ..., // The username of the user you want to invite.
}
```

### Leaderboards

These endpoints require application authentication via `Basic` authentication.

One must specify:

```
Authorization: Basic <app_id>:<api_key>
```

In their request headers.

#### `/api/leaderboards`

##### `PUT`

Create a new leaderboard.

###### Body

```json
{
  "name": ...
}
```

#### `/api/leaderboards/{id}`

##### `GET`

Get the records on the leaderboard.

###### Query

- `ascending`: Whether or not the records should be in ascending order. Defaults to true.
- `limit`: The maximum number of records which should be returned. Defaults to 10.

##### `DELETE`

Delete the leaderboard.

##### `POST`

Create or edit the leaderboard record.

###### Body

```json
{
  "user": ..., // The ID of the user the record refers to
  "increment": ..., // OPTIONAL: true / false. If not specified, the record will be set to <value>, if true, the record will be incremented by <value>, if false, it will be decremented by <value>. If the record does not already exist, it is initialised with <value>.
  "value": ..., // OPTIONAL: The value to edit the record with. If not set, defaults to 1
}
```
