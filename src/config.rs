pub fn init_config<T: serde::de::DeserializeOwned>() -> Option<T> {
    match envy::from_env::<T>() {
        Ok(config) => Some(config),
        Err(config) => {
            eprintln!("{:#?}", config);
            None
        }
    }
}
