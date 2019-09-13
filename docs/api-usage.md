# API

## Endpoints

The following routes are available, the most up-to-date list is located [here](../config/routes.php)

### Auth related

| url                | Method  | Params                    | Example response                          |
|--------------------|---------|---------------------------|-------------------------------------------|
| /api/auth/token    | POST    | {email: '', password: ''} | {access_token?: '', success: bool}        |

| url                | Method  | Protected       | Example response                 |
|--------------------|---------|-----------------|----------------------------------|
| /api/v1/profile    | GET     | JWTAuth bearer  | {"success":true,"data":{"user_id":"username","firstname":"Test","name":"Demo","email":"username@example.com"}} | 


> `/api/auth/token` is used to issue a new token. `/api/auth/validate` to validate if a token 
> is valid (signature, expiration...). `/api/v1/profile` returns user information if token is valid
> otherwise return a 401 error.


### Monitoring 

| url                       | Method   | Return                                |
|---------------------------|----------|---------------------------------------|
| /api/ping                 | GET      | {ack: time()}                         |
| /api/contredanse_status   | GET      | {up: bool, ack: time(), reason?: '' } |  

> Can be used to monitor if remote authentication with contredanse is up and running.

### Unused endpoints

| url                | Method  | Params                    | Example response                          |
| /api/auth/validate | POST    | {token: ''}               | {valid: bool, expires_at, remaining_time} |


## Usage example

### api/auth/token - request a token (login)

> You mst post a valid login/password

```bash
$ curl https://site.org/api/auth/token \
       --request POST  \
       -H "Origin: http://localhost" \
       -H "Content-Type: application/json" \
       --data '{"email":"user@example.org","password":"secret"}'
```

It will return the jwt token: 

```json
{"success": true, "access_token":"<header>.<payload>.<hash>","token_type":"api_auth"}
```

Or a 400 (bad request = missing parameter) / 401 (Unauthorized) status code and the following message:

```json
{"success": false, "reason": "a message"}
```

### api/v1/profile - return profile info 

```bash
$ curl https://site.org/api/v1/profile \
       -H 'authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1N...' 
       
```
In case of success will return profile data

```json
{"success":true,"data":{"user_id":"username","firstname":"Test","name":"Demo","email":"username@example.com"}}
```

In case of error, returns a 401:

```json
{"valid":false,"reason":"expired"}
```

## CORS

### Ensure CORS Middleware does the job

> replace https://site.org by the correct url

```bash 
$ curl https://site.org/api/auth/token \
   --request OPTIONS \
   --include -H "Access-Control-Request-Headers: content-type" \
   -H "Access-Control-Request-Method: POST" \
   -H "Origin: http://localhost" \
   -H "Accept: application/json"
```

Should return at least the following headers:

```
Access-Control-Allow-Origin: http://localhost
Access-Control-Allow-Credentials: true
Vary: Origin
Access-Control-Allow-Headers: authorization, if-match, if-unmodified-since, content-type
```

