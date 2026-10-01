use core::fmt;
use std::error::Error;

use regex::regex;
use salvo::prelude::*;

use serde::Deserialize;
use serde::Serialize;

#[derive(Deserialize, Default, Clone, Debug)]
pub struct AuthCat {
    auth_url: String,
}

#[allow(non_snake_case)]
#[derive(Serialize, Deserialize, Default, Clone, Debug)]
pub struct User {
    id: u32,
    username: String,
    fullName: String,
    email: String,
    pfpPath: String,
    verified: u8,
}

#[async_trait]
impl Writer for User {
    async fn write(self, _req: &mut Request, _depot: &mut Depot, res: &mut Response) {
        res.status_code(StatusCode::OK);
        res.render(serde_json::to_string(&self).unwrap());
    }
}

#[derive(Debug)]
pub struct AuthError {
    pub message: String,
}

impl fmt::Display for AuthError {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        write!(f, "{}", &self.message)
    }
}
impl Error for AuthError {}
#[async_trait]
impl Writer for AuthError {
    async fn write(self, _req: &mut Request, _depot: &mut Depot, res: &mut Response) {
        res.status_code(StatusCode::UNAUTHORIZED);
        res.render(&self.message);
    }
}

pub type AccessToken = String;

impl AuthCat {
    pub async fn authenticate_token(&self, token: &AccessToken) -> Result<User, AuthError> {
        let mut user_url = String::clone(&self.auth_url);
        user_url.push_str("/user");

        let client = reqwest::Client::new();
        let resp = client.get(&user_url).bearer_auth(token).send().await;

        if let Ok(response) = resp {
            if response.status() != StatusCode::OK {
                return Err(AuthError {
                    message: format!("Server reported error code {}", &response.status()),
                });
            }

            let dec_res = response.json::<User>().await;
            if let Err(error) = dec_res {
                return Err(AuthError {
                    message: error.to_string(),
                });
            } else {
                return Ok(dec_res.unwrap());
            }
        } else {
            Err(AuthError {
                message: String::from("Failed to make authentication request"),
            })
        }
    }
}

/// Get an access token from a request object by parsing the authentication header
///
/// * `req`: The request
pub fn get_access_token_from_request(req: &mut Request) -> Option<AccessToken> {
    let header = String::from(req.header("Authorization").unwrap_or(""));
    if let Some(caps) = regex!(r"Bearer (?<token>.*)").captures(&header) {
        Some(caps["token"].to_owned())
    } else {
        None
    }
}

/// Set the parameters of a response to an unauthenticated request
///
/// * `res`: The response to modify
fn response_set_unauthenticated(res: &mut Response) {
    match res.add_header("WWW-Authenticate", "Bearer", true) {
        Ok(res) => {
            res.status_code(StatusCode::UNAUTHORIZED);
        }
        Err(error) => eprintln!("{}", error.to_string()),
    }
}

/// Requires that the request be authenticated with a bearer token. If not possible, the AuthError
/// is returned, and appropriate response is set. Otherwise, the user is returned and the calling
/// handler may continue.
///
/// * `req`: The request
/// * `res`: The response, only modified if the request is unauthenticated
/// * `authcat`: The active authcat instance
pub async fn require_authenticated(
    req: &mut Request,
    res: &mut Response,
    authcat: &AuthCat,
) -> Result<User, AuthError> {
    let token: AccessToken;
    if let Some(t) = get_access_token_from_request(req) {
        token = t;
    } else {
        response_set_unauthenticated(res);
        return Err(AuthError {
            message: String::from("Did not supply bearer token."),
        });
    }

    authcat.authenticate_token(&token).await
}
