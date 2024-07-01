IMPORTANT

When first cloned, run:

composer u

Run these commands to start the database:

php artisan db:wipe;php artisan migrate --seed

To run the task scheduler locally:

php artisan schedule:work

To run the task queue locally:

php artisan queue:work