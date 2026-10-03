use core::fmt;
use std::error::Error;

use regex::regex;
use salvo::prelude::*;

use serde::Deserialize;
use serde::Serialize;

use crate::errors::ApiError;

#[derive(Deserialize, Default, Clone, Debug)]
pub struct AuthCat {
    auth_url: String,
}

#[allow(non_snake_case)]
#[derive(Serialize, Deserialize, Default, Clone, Debug)]
pub struct User {
    pub id: u32,
    pub username: String,
    pub fullName: String,
    pub email: String,
    pub pfpPath: String,
    pub verified: u8,
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
