# League of Legends Dashboard
This is a small app that allows a user to compare games from two different league of legends profiles in one screen

## To get this project running locally
1. Clone the repo from https://github.com/dakotawashok/loldashboard.git
2. Make a new table in your local database for the app
3. Copy the .env.example into a .env file and enter your database credentials
4. Run `composer install` from the project root directory
5. Run `php artisan key:generate` from the project root directory
6. Run `php artisan migrate:install` from the project root directory *important* Make sure you run this from inside the virtual machine you're using
7. Run `php artisan migrate` from the project root directory *important* Make sure you run this from inside the virutal machine you're using
7. Run `npm install` from the project root directory
8. *Optional* if you feel like having a nice looking URL like I do, make a new record in your hosts file
9. *Optional* if you feel like having a nice looking URL like I do, change the APP_URL constant in your .env file to whatever you made the URL in step 8
10. *Optional* Set up your vhost/apache .conf file if you need to. Make sure to restart apache after this step
11. Download the zip from https://github.com/kevinohashi/php-riot-api
12. Unzip the zip you just downloaded, and move it over to the ./Vendor/ folder
13. In the `./vender/riotapi/php-riot-api.php` file, replace __INSERT_API_KEY_HERE__ with your actual riot api key
11. Run `npm run watch` and go to your URL you set up and voila, you're good to go!

## Notes

Since Riot comes out with updates to their game fairly often, sometimes the json files stored in `./public/jsonfiles/` 
get outdated. To counter this, I've made a quick little script that goes to their datadragon service and updates the json 
files that we have. From the project root directory, run: 

`php artisan updatestaticfiles {--update}`

The --update option also goes into the javascript mixin file and changes the riot_api_version constant to the most up to 
date version from their website.

***

You might have to remove a line of code in the `./vendor/riotapi/php-riot-api.php` file. It's line 381 and it should 
read `echo("sleeping for".($interval - $timeSinceOldest + 1)." seconds\n");`