# Oaklandca.gov

This is the repository and source code for https://beta.oaklandca.gov

## Local Installation (OS X)

1. Install MAMP - https://www.mamp.info/en/
2. Install Sequel Pro - http://www.sequelpro.com/
3. Install Node - https://nodejs.org/en/

## Making Stylistic Changes in SASS

Stylesheets generated using SCSS. Running `grunt watch` will look for changes in `dev/scss` and compile changes to `public/resources/css/main.css`.

## Making Changes that involve DB Schema Updates

There's probably a more efficient way to do this but this has been my current method to make sure that production and local schemas stay in sync

1. Make schema changes using the admin interface on the production server - https://beta.oaklandca.gov/admin/settings/
2. Download the production DB using the Backup DB setting - https://beta.oaklandca.gov/admin/settings/
3. Import the production DB to local using Sequel Pro
4. Create a new branch, make changes, and test locally
5. Push branch to this GitHub repo and submit pull request
6. Merging the pull request to master will deploy changes to the production Heroku server

## Deployment

Pushing to the master branch of https://github.com/codeforamerica/oakland-beta will automatically update oakland-beta.herokuapp.com
