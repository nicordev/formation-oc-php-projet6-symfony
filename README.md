# OpenClassrooms PHP/Symfony course - Project 6 - SnowTricks

A community website on snowboard tricks created with Symfony.

## Setup

1. Clone the project
2. Run `composer install`
3. Create a database and configure the `.env` file with your database credentials
4. Run `php bin/console doctrine:migrations:migrate` to create the right tables

## Load data fixtures

Once the setup is complete, run the following command to load the data in your database: `php bin/console doctrine:fixtures:load`

Then use the following credentials to login with the password **pwdSucks!0** on the website:
* testuser@snow.com
* moderator@snow.com
* editor@snow.com
* manager@snow.com
* admin@snow.com

*Enjoy!*