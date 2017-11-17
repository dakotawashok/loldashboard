#League of Legends Dashboard
This is a small app that allows a user to compare games from two different league of legends profiles in one screen

##To get this project running locally
1. Clone the repo from https://github.com/dakotawashok/loldashboard.git
2. Make a new table in your local database for the app
3. Copy the .env.example into a .env file and enter your database credentials
4. Run `composer install` from the project root directory
5. Run `php artisan key:generate` from the project root directory
6. Run `php artisan migrate:install` from the project root directory *important* Make sure you run this from inside the virtual machine you're using
7. Run `npm install` from the project root directory
8. *Optional* if you feel like having a nice looking URL like I do, make a new record in your hosts file
9. *Optional* if you feel like having a nice looking URL like I do, change the APP_URL constant in your .env file to whatever you made the URL in step 8
10. *Optional* Set up your vhost/apache .conf file if you need to
11. Run `npm run watch` and go to your URL you set up and voila, you're good to go!