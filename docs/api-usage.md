# API usage

## Endpoints

The following routes are available, the most up-to-date list is located [here]()

### Auth related

| url                | Method   | Params                    | Return                                    |
|--------------------|----------|---------------------------|-------------------------------------------|
| /api/auth/token    | POST     | {email: '', password: ''} | {access_token?: '', success: bool}        |
| /api/auth/validate | POST     | {token: ''}               | {valid: bool, expires_at, remaining_time} |

> `/api/auth/token` can be used to issue a new token. `/api/auth/validate` to validate if a token 
> is valid (signature, expiration...)

### Services

| url                | Method   | Auth             | Purpose           |
|--------------------|----------|------------------|-------------------|
| /api/v1/profile    | GET      | JWT auth bearer  | Login information |

### Monitoring 

| url                       | Method   | Return                                |
|---------------------------|----------|---------------------------------------|
| /api/ping                 | GET      | {ack: time()}                         |
| /api/contredanse_status   | GET      | {up: bool, ack: time(), reason?: '' } |  

> Can be used to monitor if remote authentication with contredanse is up and running.


## Usage example

### Request a token (login)

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

Or a 400 (bad request = missing parameter) / 401 (Unauthorized) status code and the folowing

```json
{"success": false, "reason": "a message"}
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

