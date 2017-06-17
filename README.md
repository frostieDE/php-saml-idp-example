# LightSAML Identity Provider Sample

Note: this project is only a demonstration on how to implement the IdP part of SAML using LightSAML. Also note, that this
project is still WIP ;-)

## Installation

1. Copy `app/config.yml` to `app/config.dev.yml` and edit the config to fit your needs
2. Create the database specified in your `config.dev.yml` and run `php bin/console orm:schema-tool:create`
3. Create a self-signed certificate and put it into `apps/certs` (remember to edit `config.dev.yml` if you use another filename)
4. Create an admin user: `php bin/console app:create-admin`
5. Configure your Webserver to serve the `web/` directory
6. Goto `<YOUR_HOST>/dev.php/dashboard` (the default URL `/dev.php/` is not working currently...)
7. Add users and service providers