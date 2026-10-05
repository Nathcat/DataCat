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

#### /api/apps

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
