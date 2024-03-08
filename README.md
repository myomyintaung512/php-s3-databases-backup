# SETUP GUIDE
- git clone git@github.com:myomyintaung512/php-s3-databases-backup.git backup
- cd backup
- composer install
- cp .env.example .env
- Update .env
- setup cronjob for `php backup.php`
