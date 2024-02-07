# coturn-credential-api

A ready to deploy REST API written in plain PHP to generate credentials for [CoTURN](https://github.com/coturn/coturn) TURN/STUN server.<br />

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

# Table of contents

- [coturn-credential-api](#coturn-credential-api)
- [Table of contents](#table-of-contents)
- [Use case](#use-case)
- [Basic information](#basic-information)
- [Setup](#setup)
  - [Configure CoTURN to accept REST API authentication](#configure-coturn-to-accept-rest-api-authentication)
  - [Basic installation of coturn-credential-api](#basic-installation-of-coturn-credential-api)
- [Usage](#usage)
  - [Requesting credentials](#requesting-credentials)
      - [JavaScript example](#javascript-example)
  - [API response](#api-response)
      - [Attributes](#attributes)
      - [Credential data attributes](#credential-data-attributes)
      - [Example response](#example-response)
  - [API errors](#api-errors)
      - [Example error](#example-error)

# Use case

Because TURN traffic can be bandwith intensive it is common practice to secure your TURN server with credentials. The problem being, most cases in which a TURN server is implemented are client side, meaning the credentials are exposed. To limit risk of exposure the most common workaround is to implement a REST API that generates a time sensitive username and password. (More details can be found in [Justin Uberti's Internet-Draft](https://datatracker.ietf.org/doc/html/draft-uberti-behave-turn-rest-00)).

This API is inspired by [Justin Uberti's Internet-Draft](https://datatracker.ietf.org/doc/html/draft-uberti-behave-turn-rest-00) and aims to provide a quick and easy solution to generate time sensitive credentials in a format understood by [CoTURN](https://github.com/coturn/coturn).

# Basic information

The API implements key based authentication to make sure only authorized requests can generate credentials.

You may want to implement some sort of rate limiting to stop abuse.

# Setup

## Configure CoTURN to accept REST API authentication

By default [CoTURN](https://github.com/coturn/coturn) uses the 'classic' long-term credentials mechanism.
To enable REST API authentication you will need to add the `use-auth-secret` flag to your `/etc/turnserver.conf` config.

This API uses a static secret to hash the time sensitive password. THe secret will need to be defined in your config using the `static-auth-secret` variable.

Excerpt from `/etc/turnserver.conf`:

```
use-auth-secret
static-auth-secret=__SECRET__
```

## Basic installation of coturn-credential-api

You will need to have a web server such as NGINX configured and running with PHP in advance.

1. Clone this repo or download it as a zip file [here](https://github.com/ezrarieben/coturn-credential-api/archive/main.zip).
2. Move all files and folders in the `public` folder to your web root and make sure permissions are set correctly.
3. Edit `config.inc.php` and set your auth secret to the same secret [configured in CoTURN's server config](#configure-coturn-to-accept-rest-api-authentication).

```php
define('TURN_AUTH_SECRET', "__SECRET__");
```

1. Set API keys in `config.inc.php`.
    > **NOTE:** The keys below are just for illustrative purposes. You will want to generate your own, possibly using a [random generator](https://www.random.org/strings/).

```php
define('ALLOWED_API_KEYS', array(
    'BMaV8tJgrHxDUMHZe4813h2K7QK7IAPg',
    'CQNr3EPDOjnn6JKfkFuaPl3sPy5zpbh0',
    'imDVt70geDpwbl1r1egpx8IShRg64DO3'
));
```

# Usage

## Requesting credentials

Credentials can be generated/requested via GET or POST. Make sure to pass the following variables as POST or GET parameters:

| Name       | Description                                                                                                          | Required |
| ---------- | -------------------------------------------------------------------------------------------------------------------- | -------- |
| `username` | Username used to generate credentials                                                                                | **YES**  |
| `key`      | A valid API key defined during [installation of coturn-credential-api](#basic-installation-of-coturn-credential-api) | **YES**  |


#### JavaScript example

```javascript
const params = {
    username: "testuser",
    key: "BMaV8tJgrHxDUMHZe4813h2K7QK7IAPg",
};

const options = {
    method: "POST",
    body: new URLSearchParams(params),
};

fetch("https://example.com/", options)
    .then((response) => response.json())
    .then((data) => {
        console.log(data);
    });
```

## API response

The API response is formatted in JSON and contains the following attributes by default:

#### Attributes

| Name      | Description                                                                    | Type                                      |
| --------- | ------------------------------------------------------------------------------ | ----------------------------------------- |
| `success` | Boolean value to signify if generating of credentials was successfull          | Boolean                                   |
| `message` | Message describing either the error that occurred or the credentials generated | String                                    |
| `data`    | The credential data                                                            | [JSONObject](#credential-data-attributes) |

#### Credential data attributes

| Name       | Description                                                                                                       | Type    |
| ---------- | ----------------------------------------------------------------------------------------------------------------- | ------- |
| `username` | Generated username according to TTL defenition in config and username param, in the format that CoTURN expects it | String  |
| `password` | Base64 encoded password hash as CoTURN expects it.                                                                | String  |
| `ttl`      | Time to live of the credentials.                                                                                  | Integer |

#### Example response

```json
{
    "success": true,
    "message": "Credentials are valid until: 2024-02-06 03:30:00 (UTC)",
    "data": {
        "username": "1707233400:testuser",
        "password": "+gJJaa/6oHM3DOURkrLYrkPeH9g=",
        "ttl": 1707233400
    }
}
```

## API errors

If an error occurrs during credential generation the [`success` attribute](#attributes) in the API response will be set to `false`.
<br/>The error that occurred is described in the [`message` attribute](#attributes).

#### Example error

```json
{
    "success": false,
    "message": "API key provided is invalid.",
    "data": []
}
```
