# Oaklandca.gov

This is the repository and source code for `https://pilot.oaklandca.gov`.

## Understanding the Web Architecture

1. The website is built on Craft CMS (https://craftcms.com/)
2. Sourcecode is hosted on GitHub (https://github.com/CityofOakland/oaklandca.gov)
3. The production app is hosted on Heroku (http://oakland-beta.herokuapp.com/)
4. A Varnish Cache and Reverse Proxy is hosted on an AWS EC2 instance. Cached pages are served directly from Varnish server and non-cachced pages are passed through to the Heroku app.
5. https://beta.oaklandca.gov is pointed at the Heroku app and https://pilot.oaklandca.gov is pointed at the Varnish server.

## Accessing the Admin Interface

The CMS admin interface is accessible through https://beta.oaklandca.gov/admin

## Local Installation (OS X)

There are many ways to setup your Nginx server and mySql database. Here's one method: 

1. Clone this repo into `~/Sites`
1. Install MAMP - https://www.mamp.info/en/
    - In the MAMP settings, Point the MAMP server to `~/Sites`
    - Start the MAMP server
1. Install Sequel Pro - http://www.sequelpro.com/
    - Login using the socket tab with default username `root` and password `root`
    - Add a new database with the name `oaklanddb`
    - Import the production database using the Backup DB setting -  - https://beta.oaklandca.gov/admin/settings/

## Making Stylistic Changes in SASS

Stylesheets in this repo generated using Scss. Scss files can be compiled into css in many ways. Here's one method:

1. Install Node - https://nodejs.org/en/
1. Run `npm install`
1. Install the grunt-cli globally using `npm install -g grunt-cli`
1. Run `grunt` to compile change in  `dev/scss` to `public/resources/css/main.css`
1. Run `grunt watch` to watch for live changes and auto-compile


## Making Changes that involve DB Schema Updates

There's probably a more efficient way to do this but this has been my current method to make sure that production and local schemas stay in sync.

1. Make schema changes using the admin interface on the production server - https://beta.oaklandca.gov/admin/settings/
2. Download the production DB using the Backup DB setting
3. Import the production DB to local using Sequel Pro
4. Create a new branch, make changes, and test locally
5. Push branch to this GitHub repo and submit pull request
6. Merging the pull request to master will deploy changes to the production Heroku server

## Deployment

Pushing to the master branch of https://github.com/codeforamerica/oakland-beta will automatically update oakland-beta.herokuapp.com

## Additional Resources

1. Visual Design Guidelines and a Pattern Portfoliio for this website is located at `https://design.oaklandca.gov/`.
