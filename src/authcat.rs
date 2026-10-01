use core::fmt;
use std::error::Error;

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
    verified: u8
}

#[derive(Debug)]
pub struct AuthError {
    pub message: String
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
        let resp = client.get(&user_url)
            .bearer_auth(token)
            .send().await;

        if let Ok(response) = resp {
            if response.status() != StatusCode::OK {
                return Err(AuthError{ message: format!("Server reported error code {}", &response.status())});
            }
            
            let dec_res = response.json::<User>().await;
            if let Err(error) = dec_res {
                return Err(AuthError { message: error.to_string() });
            }
            else {
                return Ok(dec_res.unwrap());
            }
        } else {
            Err(AuthError { message: String::from("Failed to make authentication request") })
        }
    }
}
